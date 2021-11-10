<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\Components\PayPalOrderBuilderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address as PayerAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name as PayerName;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address as ShippingAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Name as ShippingName;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class PayPalOrderBuilderService
{
    /**
     * @var SettingsServiceInterface
     */
    private $settings;

    /**
     * @var ItemListProvider
     */
    private $itemListProvider;

    /**
     * @var AmountProvider
     */
    private $amountProvider;

    /**
     * @var ReturnUrlHelper
     */
    private $returnUrlHelper;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ItemListProvider $itemListProvider,
        AmountProvider $amountProvider,
        ReturnUrlHelper $returnUrlHelper
    ) {
        $this->settings = $settingsService;
        $this->itemListProvider = $itemListProvider;
        $this->amountProvider = $amountProvider;
        $this->returnUrlHelper = $returnUrlHelper;
    }

    /**
     * @return Order
     */
    public function getOrder(PayPalOrderBuilderParameter $orderParameter)
    {
        $intent = $this->getIntent();
        $payer = $this->createPayer($orderParameter->getCustomer());

        $purchaseUnit = $this->createPurchaseUnit($orderParameter->getCart(), $orderParameter->getCustomer());

        if ($orderParameter->getPaymentType() !== PaymentType::PAYPAL_EXPRESS_V2) {
            $billingAddress = $this->createBillingAddress($orderParameter->getCustomer());
            $payer->setAddress($billingAddress);

            $shippingAddress = $this->createShipping($orderParameter->getCustomer());
            $purchaseUnit->setShipping($shippingAddress);
        }

        $applicationContext = $this->createApplicationContext();

        $applicationContext->setReturnUrl($this->returnUrlHelper->getReturnUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken()));
        $applicationContext->setCancelUrl($this->returnUrlHelper->getCancelUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken()));

        $order = new Order();
        $order->setIntent($intent);
        $order->setPayer($payer);
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setApplicationContext($applicationContext);

        if ($orderParameter->getPaymentType() === PaymentType::PAYPAL_EXPRESS_V2) {
            $order->getApplicationContext()->setShippingPreference(ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
            $order->getApplicationContext()->setUserAction(ApplicationContext::USER_ACTION_CONTINUE);
        }

        return $order;
    }

    /**
     * @return string
     */
    private function getIntent()
    {
        // TODO: Get intent from settings PT-12488
        $intent = PaymentIntentV2::CAPTURE;

        if (!\in_array($intent, [PaymentIntentV2::CAPTURE, PaymentIntentV2::AUTHORIZE], true)) {
            throw new \RuntimeException(sprintf('The intent %d is not supported!', $intent));
        }

        return $intent;
    }

    /**
     * @return Payer
     */
    private function createPayer(array $customer)
    {
        $customerData = $customer['additional']['user'];

        $name = new PayerName();
        $name->setGivenName($customerData['firstname']);
        $name->setSurname($customerData['lastname']);

        $payer = new Payer();
        $payer->setEmailAddress($customerData['email']);
        $payer->setName($name);

        return $payer;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return PayerAddress
     */
    private function createBillingAddress(array $customer)
    {
        $address = new PayerAddress();

        $billingAddress = $customer['billingaddress'];

        $address->setAddressLine1($billingAddress['street']);

        $additionalAddressLine1 = $billingAddress['additionalAddressLine1'];
        if ($additionalAddressLine1 !== null) {
            $address->setAddressLine2($additionalAddressLine1);
        }

        if (isset($customer['additional']['state']['shortcode'])) {
            $address->setAdminArea1($customer['additional']['state']['shortcode']);
        }

        $address->setAdminArea2($billingAddress['city']);
        $address->setPostalCode($billingAddress['zipcode']);
        $address->setCountryCode($customer['additional']['country']['countryiso']);

        return $address;
    }

    /**
     * @return PurchaseUnit
     */
    private function createPurchaseUnit(array $cart, array $customer)
    {
        $purchaseUnit = new PurchaseUnit();

        $submitCart = (bool) $this->settings->get('submit_cart');

        $items = $submitCart ? $this->itemListProvider->getItemList($cart, $customer) : null;
        $purchaseUnit->setItems($items);

        $amount = $this->amountProvider->createAmount($cart, $purchaseUnit, $customer);
        $purchaseUnit->setAmount($amount);

        return $purchaseUnit;
    }

    /**
     * @return Shipping
     */
    private function createShipping(array $customer)
    {
        if (!\array_key_exists('shippingaddress', $customer)) {
            throw new \RuntimeException(sprintf('Customer with ID "%s" has no shipping address', $customer['additional']['user']['id']));
        }

        $shippingAddress = $customer['shippingaddress'];
        $shipping = new Shipping();

        $address = $this->createShippingAddress($customer, new ShippingAddress());
        $shipping->setAddress($address);
        $shipping->setName($this->createShippingName($shippingAddress));

        return $shipping;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return ShippingAddress
     */
    private function createShippingAddress(array $customer, ShippingAddress $address)
    {
        $shippingAddress = $customer['shippingaddress'];

        $address->setAddressLine1($shippingAddress['street']);

        $additionalAddressLine1 = $shippingAddress['additionalAddressLine1'];
        if ($additionalAddressLine1 !== null) {
            $address->setAddressLine2($additionalAddressLine1);
        }

        if (isset($customer['additional']['stateShipping']['shortcode'])) {
            $address->setAdminArea1($customer['additional']['stateShipping']['shortcode']);
        }

        $address->setAdminArea2($shippingAddress['city']);
        $address->setPostalCode($shippingAddress['zipcode']);
        $address->setCountryCode($customer['additional']['countryShipping']['countryiso']);

        return $address;
    }

    /**
     * @return ShippingName
     */
    private function createShippingName(array $shippingAddress)
    {
        $shippingName = new ShippingName();
        $shippingName->setFullName(\sprintf('%s %s', $shippingAddress['firstname'], $shippingAddress['lastname']));

        return $shippingName;
    }

    /**
     * @return ApplicationContext
     */
    private function createApplicationContext()
    {
        $applicationContext = new ApplicationContext();
        $applicationContext->setBrandName((string) $this->settings->get('brand_name'));
        $applicationContext->setLandingPage($this->getLandingPageType());

        return $applicationContext;
    }

    /**
     * @return string
     */
    private function getLandingPageType()
    {
        // TODO implement setting for this PT-12488
        return ApplicationContext::LANDING_PAGE_TYPE_NO_PREFERENCE;
    }
}
