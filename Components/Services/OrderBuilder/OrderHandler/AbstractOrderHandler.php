<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler;

use RuntimeException;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberBuilder;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\ProcessingInstruction;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address as PayerAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name as PayerName;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address as ShippingAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Name as ShippingName;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

abstract class AbstractOrderHandler implements OrderBuilderHandlerInterface
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

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var PhoneNumberBuilder
     */
    private $phoneNumberBuilder;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ItemListProvider $itemListProvider,
        AmountProvider $amountProvider,
        ReturnUrlHelper $returnUrlHelper,
        ContextServiceInterface $contextService,
        PhoneNumberBuilder $phoneNumberBuilder
    ) {
        $this->settings = $settingsService;
        $this->itemListProvider = $itemListProvider;
        $this->amountProvider = $amountProvider;
        $this->returnUrlHelper = $returnUrlHelper;
        $this->contextService = $contextService;
        $this->phoneNumberBuilder = $phoneNumberBuilder;
    }

    /**
     * @return string
     */
    protected function getIntent()
    {
        $intent = $this->settings->get(SettingsServiceInterface::SETTING_INTENT);

        if (!\in_array($intent, [PaymentIntentV2::CAPTURE, PaymentIntentV2::AUTHORIZE], true)) {
            throw new RuntimeException(sprintf('The intent %s is not supported!', $intent));
        }

        return $intent;
    }

    /**
     * @return Payer
     */
    protected function createPayer(PayPalOrderParameter $orderParameter)
    {
        $customerData = $orderParameter->getCustomer()['additional']['user'];

        $name = new PayerName();
        $name->setGivenName($customerData['firstname']);
        $name->setSurname($customerData['lastname']);

        $payer = new Payer();
        $payer->setEmailAddress($customerData['email']);
        $payer->setName($name);

        if ($orderParameter->getPaymentType() === PaymentType::PAYPAL_EXPRESS_V2) {
            return $payer;
        }

        $payer->setAddress($this->createBillingAddress($orderParameter->getCustomer()));

        return $payer;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return PayerAddress
     */
    protected function createBillingAddress(array $customer)
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
     * @return array<PurchaseUnit>
     */
    protected function createPurchaseUnits(PayPalOrderParameter $orderParameter)
    {
        $purchaseUnit = new PurchaseUnit();
        $submitCart = $this->settings->get(SettingsServiceInterface::SETTING_SUBMIT_CART) || $orderParameter->getPaymentType() === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;

        if ($submitCart) {
            $purchaseUnit->setItems($this->itemListProvider->getItemList(
                $orderParameter->getCart(),
                $orderParameter->getCustomer(),
                $orderParameter->getPaymentType() === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2
            ));
        }

        $purchaseUnit->setAmount(
            $this->amountProvider->createAmount(
                $orderParameter->getCart(),
                $purchaseUnit,
                $orderParameter->getCustomer()
            )
        );

        if ($orderParameter->getPaymentType() === PaymentType::PAYPAL_EXPRESS_V2) {
            return [$purchaseUnit];
        }

        $purchaseUnit->setShipping($this->createShipping($orderParameter->getCustomer()));

        return [$purchaseUnit];
    }

    /**
     * @param array<string,mixed> $customer
     *
     * @return Shipping
     */
    protected function createShipping(array $customer)
    {
        if (!\array_key_exists('shippingaddress', $customer)) {
            throw new RuntimeException(sprintf('Customer with ID "%s" has no shipping address', $customer['additional']['user']['id']));
        }

        $shippingAddress = $customer['shippingaddress'];
        $shipping = new Shipping();

        $address = $this->createShippingAddress($customer);
        $shipping->setAddress($address);
        $shipping->setName($this->createShippingName($shippingAddress));

        return $shipping;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return ShippingAddress
     */
    protected function createShippingAddress(array $customer)
    {
        $address = new ShippingAddress();
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
     * @param array<string, mixed> $shippingAddress
     *
     * @return ShippingName
     */
    protected function createShippingName(array $shippingAddress)
    {
        $shippingName = new ShippingName();
        $shippingName->setFullName(sprintf('%s %s', $shippingAddress['firstname'], $shippingAddress['lastname']));

        return $shippingName;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return PhoneNumber
     */
    protected function createPaymentSourcePhoneNumber(array $customer)
    {
        if (!isset($customer['billingaddress']['phone'])) {
            return new PhoneNumber();
        }

        if (!isset($customer['additional']['country']['countryiso'])) {
            return $this->phoneNumberBuilder->build($customer['billingaddress']['phone']);
        }

        return $this->phoneNumberBuilder->build(
            $customer['billingaddress']['phone'],
            $customer['additional']['country']['countryiso']
        );
    }

    /**
     * @return ApplicationContext
     */
    protected function createApplicationContext(PayPalOrderParameter $orderParameter)
    {
        $applicationContext = new ApplicationContext();
        $applicationContext->setBrandName((string) $this->settings->get(SettingsServiceInterface::SETTING_BRAND_NAME));
        $applicationContext->setLandingPage($this->getLandingPageType());

        $applicationContext->setReturnUrl($this->returnUrlHelper->getReturnUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken()));
        $applicationContext->setCancelUrl($this->returnUrlHelper->getCancelUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken()));

        if ($orderParameter->getPaymentType() === PaymentType::PAYPAL_EXPRESS_V2) {
            $applicationContext->setShippingPreference(ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
            $applicationContext->setUserAction(ApplicationContext::USER_ACTION_CONTINUE);
        }

        return $applicationContext;
    }

    /**
     * All information for the experience context will likely be provided by the
     * merchant during onboarding (ISU).
     */
    protected function createExperienceContext()
    {
        $experienceContext = new ExperienceContext();
        $shop = $this->contextService->getShopContext()->getShop();

        $experienceContext->setLocale(
            str_replace('_', '-', $shop->getLocale()->getLocale())
        );

        if ($brandName = $this->settings->get(SettingsServiceInterface::SETTING_BRAND_NAME)) {
            $experienceContext->setBrandName($brandName);
        }

        $experienceContext->setLogoUrl('https://example.com/logo.svg'); // TODO: (PT-12488) actually implement setting
        $experienceContext->setReturnUrl('https://example.com/return'); // TODO: (PT-12488) actually implement or remove, since this is probably only necessary due to a broken API endpoint on PayPals side
        $experienceContext->setCancelUrl('https://example.com/cancel'); // TODO: (PT-12488) actually implement or remove, since this is probably only necessary due to a broken API endpoint on PayPals side
        $experienceContext->setCustomerServiceInstructions(['Lorem ipsum']); // TODO: (PT-12488) actually implement setting

        return $experienceContext;
    }

    /**
     * @return string
     */
    protected function getLandingPageType()
    {
        // TODO: (PT-12488) implement setting for this
        return ApplicationContext::LANDING_PAGE_TYPE_NO_PREFERENCE;
    }

    protected function createPaymentSource(PayPalOrderParameter $orderParameter, Order $order)
    {
        if ($orderParameter->getPaymentType() !== PaymentType::PAYPAL_PAY_UPON_INVOICE_V2) {
            return null;
        }

        $order->setProcessingInstruction(ProcessingInstruction::ORDER_COMPLETE_ON_PAYMENT_APPROVAL);

        $paymentSource = new PaymentSource();
        $payUponInvoice = new PayUponInvoice();
        $experienceContext = $this->createExperienceContext();

        $payUponInvoice->setName($order->getPayer()->getName());
        $payUponInvoice->setEmail($order->getPayer()->getEmailAddress());
        $payUponInvoice->setBirthDate($orderParameter->getCustomer()['additional']['user']['birthday']);
        $payUponInvoice->setPhone($this->createPaymentSourcePhoneNumber($orderParameter->getCustomer()));
        $payUponInvoice->setBillingAddress($order->getPayer()->getAddress());
        $payUponInvoice->setExperienceContext($experienceContext);

        $paymentSource->setPayUponInvoice($payUponInvoice);

        return $paymentSource;
    }
}
