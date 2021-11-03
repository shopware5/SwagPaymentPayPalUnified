<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RedirectUrls;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount\Details;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList\Item;

class PaymentBuilderService implements PaymentBuilderInterface
{
    /**
     * @var SettingsServiceInterface
     */
    protected $settings;

    /**
     * @var PaymentBuilderParameters
     */
    protected $requestParams;

    /**
     * @var DependencyProvider
     */
    protected $dependencyProvider;

    /**
     * @var ReturnUrlHelper
     */
    protected $returnUrlHelper;

    /**
     * @var array
     */
    private $basketData;

    /**
     * @var array
     */
    private $userData;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    public function __construct(
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        DependencyProvider $dependencyProvider,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper,
        CartHelper $cartHelper,
        ReturnUrlHelper $returnUrlHelper
    ) {
        $this->settings = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->snippetManager = $snippetManager;
        $this->priceFormatter = $priceFormatter;
        $this->customerHelper = $customerHelper;
        $this->cartHelper = $cartHelper;
        $this->returnUrlHelper = $returnUrlHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment(PaymentBuilderParameters $params)
    {
        $this->requestParams = $params;
        $this->basketData = $params->getBasketData();
        $this->userData = $params->getUserData();

        $requestParameters = new Payment();
        $paymentType = $params->getPaymentType();

        $applicationContext = $this->getApplicationContext($paymentType);

        if ($paymentType === PaymentType::PAYPAL_EXPRESS || $paymentType === PaymentType::PAYPAL_CLASSIC) {
            $requestParameters->setIntent($this->getIntentAsString((int) $this->settings->get('intent', SettingsTable::EXPRESS_CHECKOUT)));
        } else {
            $requestParameters->setIntent(PaymentIntent::SALE);
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setCancelUrl($this->returnUrlHelper->getCancelUrl($params->getBasketUniqueId(), $params->getPaymentToken()));
        $redirectUrls->setReturnUrl($this->returnUrlHelper->getReturnUrl($params->getBasketUniqueId(), $params->getPaymentToken()));

        $amount = new Amount();
        $amount->setDetails($this->getAmountDetails());
        $amount->setCurrency($this->basketData['sCurrencyName']);
        $amount->setTotal($this->cartHelper->getTotalAmount($this->basketData, $params->getUserData()));

        $transactions = new Transactions();
        $transactions->setAmount($amount);

        $submitCartGeneral = (bool) $this->settings->get('submit_cart');
        $submitCartEcs = (bool) $this->settings->get('submit_cart', SettingsTable::EXPRESS_CHECKOUT);
        if ($paymentType !== PaymentType::PAYPAL_EXPRESS && $submitCartGeneral) {
            $this->setItemList($transactions);
        } elseif ($paymentType === PaymentType::PAYPAL_EXPRESS && $submitCartEcs) {
            $this->setItemList($transactions);
        }

        $requestParameters->setPayer($payer);
        $requestParameters->setRedirectUrls($redirectUrls);
        $requestParameters->setTransactions($transactions);
        $requestParameters->setApplicationContext($applicationContext);

        return $requestParameters;
    }

    /**
     * @param int $intent
     *
     * @return string
     */
    private function getIntentAsString($intent)
    {
        switch ($intent) {
            case 0:
                return PaymentIntent::SALE;
            case 1:
                return PaymentIntent::AUTHORIZE;
            case 2:
                return PaymentIntent::ORDER;
            default:
                throw new \RuntimeException(sprintf('The intent-type %d is not supported!', $intent));
        }
    }

    private function setItemList(Transactions $transactions)
    {
        $itemList = new ItemList();
        $itemList->setItems($this->getItemList());

        $transactions->setItemList($itemList);
    }

    /**
     * @return Item[]
     */
    private function getItemList()
    {
        $list = [];
        /** @var array $basketContent */
        $basketContent = $this->basketData['content'];
        $customProductMainLineItemKey = 0;
        $customProductsHint = $this->snippetManager->getNamespace('frontend/paypal_unified/checkout/item_list')
            ->get('paymentBuilder/customProductsHint', ' incl. surcharges for Custom Products configuration');

        foreach ($basketContent as $key => $basketItem) {
            $sku = $basketItem['ordernumber'];
            $name = $basketItem['articlename'];
            $quantity = (int) $basketItem['quantity'];

            $price = $this->customerHelper->hasGrossPrices($this->userData) === true
                ? $this->priceFormatter->roundPrice($basketItem['price'])
                : $this->priceFormatter->roundPrice($basketItem['netprice']);

            // In the following part, we modify the CustomProducts positions.
            // All position prices of the Custom Products configuration are added up, so that no items with 0€ are committed to PayPal
            if (!empty($basketItem['customProductMode'])) {
                //A value indicating if the surcharge of this position is only being added once
                $isSingleSurcharge = $basketItem['customProductIsOncePrice'];

                switch ($basketItem['customProductMode']) {
                    case 1:
                        $customProductMainLineItemKey = $key;
                        $name .= $customProductsHint;

                        if ($quantity !== 1) {
                            $price *= $quantity;
                            $name = $quantity . 'x ' . $name;
                            $quantity = 1;
                        }

                        break;
                    case 2: //Option
                    case 3: //Value
                        //Calculate the total price
                        if (!$isSingleSurcharge) {
                            $price *= $quantity;
                        }

                        /** @var Item $mainProduct */
                        $mainProduct = $list[$customProductMainLineItemKey];
                        $mainProduct->setPrice((float) $mainProduct->getPrice() + $price);
                        continue 2;
                }
            }

            $item = new Item();
            $item->setCurrency($this->basketData['sCurrencyName']);
            $item->setName($name);
            $item->setPrice($price);
            $item->setQuantity($quantity);

            if ($sku !== null && $sku !== '') {
                $item->setSku($sku);
            }

            $list[$key] = $item;
        }

        return $list;
    }

    /**
     * @return Details
     */
    private function getAmountDetails()
    {
        $amountDetails = new Details();

        if ($this->customerHelper->hasGrossPrices($this->userData) && !$this->customerHelper->useNetPriceCalculation($this->userData)) {
            $amountDetails->setShipping($this->priceFormatter->formatPrice($this->basketData['sShippingcostsWithTax']));
            $amountDetails->setSubTotal($this->priceFormatter->formatPrice($this->basketData['Amount']));
            $amountDetails->setTax(\number_format(0, 2));

            return $amountDetails;
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->customerHelper->hasGrossPrices($this->userData) && !$this->customerHelper->useNetPriceCalculation($this->userData)) {
            $amountDetails->setShipping($this->priceFormatter->formatPrice($this->basketData['sShippingcostsNet']));
            $amountDetails->setSubTotal($this->priceFormatter->formatPrice($this->basketData['AmountNet']));
            $amountDetails->setTax($this->basketData['sAmountTax']);

            return $amountDetails;
        }

        //Case 3: No tax handling at all, just use the net amounts.
        $amountDetails->setShipping($this->priceFormatter->formatPrice($this->basketData['sShippingcostsNet']));
        $amountDetails->setSubTotal($this->priceFormatter->formatPrice($this->basketData['AmountNet']));

        return $amountDetails;
    }

    /**
     * @param string $paymentType
     *
     * @return ApplicationContext
     */
    private function getApplicationContext($paymentType)
    {
        $applicationContext = new ApplicationContext();

        $applicationContext->setBrandName($this->getBrandName());
        $applicationContext->setLocale($this->dependencyProvider->getShop()->getLocale()->getLocale());
        $applicationContext->setLandingPage($this->getLandingPage());

        if ($paymentType === PaymentType::PAYPAL_EXPRESS || $paymentType === PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS) {
            $applicationContext->setUserAction('continue');
        }

        return $applicationContext;
    }

    /**
     * @return string
     */
    private function getBrandName()
    {
        $brandName = (string) $this->settings->get('brand_name');

        if (\strlen($brandName) > 127) {
            $brandName = \substr($brandName, 0, 127);
        }

        return $brandName;
    }

    /**
     * @return string
     */
    private function getLandingPage()
    {
        return (string) $this->settings->get('landing_page_type');
    }
}
