<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Routing\Router;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
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
     * @var Router
     */
    protected $router;

    /**
     * @var SettingsServiceInterface
     */
    protected $settings;

    /**
     * @var PaymentBuilderParameters
     */
    protected $requestParams;

    /**
     * @var array
     */
    private $basketData;

    /*
     * @var array
     */
    private $userData;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @param Router                   $router
     * @param SettingsServiceInterface $settingsService
     * @param SnippetManager           $snippetManager
     * @param DependencyProvider       $dependencyProvider
     */
    public function __construct(
        Router $router,
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        DependencyProvider $dependencyProvider
    ) {
        $this->router = $router;
        $this->settings = $settingsService;
        $this->snippetManager = $snippetManager;
        $this->dependencyProvider = $dependencyProvider;
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
        } elseif ($paymentType === PaymentType::PAYPAL_INSTALLMENTS) {
            $requestParameters->setIntent($this->getIntentAsString((int) $this->settings->get('intent', SettingsTable::INSTALLMENTS)));
        } else {
            $requestParameters->setIntent('sale');
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setCancelUrl($this->getRedirectUrl('cancel'));
        $redirectUrls->setReturnUrl($this->getRedirectUrl('return'));

        $amount = new Amount();
        $amount->setDetails($this->getAmountDetails());
        $amount->setCurrency($this->basketData['sCurrencyName']);
        $amount->setTotal(number_format($this->getTotalAmount(), 2));

        $transactions = new Transactions();
        $transactions->setAmount($amount);

        //don't submit the cart if the option is false and the selected payment method is express checkout
        if ($paymentType !== PaymentType::PAYPAL_EXPRESS || $this->settings->get('submit_cart', SettingsTable::EXPRESS_CHECKOUT)) {
            $itemList = new ItemList();
            $itemList->setItems($this->getItemList());

            $transactions->setItemList($itemList);
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
                return 'sale';
            case 1:
                return 'authorize';
            case 2:
                return 'order';
            default:
                throw new \RuntimeException('The intent-type ' . $intent . ' is not supported!');
        }
    }

    /**
     * @return float
     */
    private function getTotalAmount()
    {
        //Case 1: Show gross prices in shopware and don't exclude country tax
        if ($this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            return $this->formatPrice($this->basketData['AmountNumeric']);
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            return $this->formatPrice($this->basketData['AmountWithTaxNumeric']);
        }

        //Case 3: No tax handling at all, just use the net amounts.
        return $this->formatPrice($this->basketData['AmountNetNumeric']);
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

            $price = $this->showGrossPrices() === true
                ? $this->formatPrice($basketItem['price'])
                : $this->formatPrice($basketItem['netprice']);

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
                        $mainProduct->setPrice($mainProduct->getPrice() + $price);
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
     * @param string $action
     *
     * @return false|string
     */
    private function getRedirectUrl($action)
    {
        //Shopware 5.3 + supports cart validation.
        //In order to use it, we have to slightly modify the return URL.
        if ($this->requestParams->getBasketUniqueId()) {
            return $this->router->assemble(
                [
                    'controller' => 'PaypalUnified',
                    'action' => $action,
                    'forceSecure' => true,
                    'basketId' => $this->requestParams->getBasketUniqueId(),
                ]
            );
        }

        return $this->router->assemble(
            [
                'controller' => 'PaypalUnified',
                'action' => $action,
                'forceSecure' => true,
            ]
        );
    }

    /**
     * @return Details
     */
    private function getAmountDetails()
    {
        $amountDetails = new Details();

        if ($this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            $amountDetails->setShipping($this->formatPrice($this->basketData['sShippingcostsWithTax']));
            $amountDetails->setSubTotal($this->formatPrice($this->basketData['Amount']));
            $amountDetails->setTax(number_format(0, 2));

            return $amountDetails;
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            $amountDetails->setShipping($this->formatPrice($this->basketData['sShippingcostsNet']));
            $amountDetails->setSubTotal($this->formatPrice($this->basketData['AmountNet']));
            $amountDetails->setTax($this->basketData['sAmountTax']);

            return $amountDetails;
        }

        //Case 3: No tax handling at all, just use the net amounts.
        $amountDetails->setShipping($this->formatPrice($this->basketData['sShippingcostsNet']));
        $amountDetails->setSubTotal($this->formatPrice($this->basketData['AmountNet']));

        return $amountDetails;
    }

    /**
     * Returns a value indicating whether or not the current customer
     * uses the net price instead of the gross price.
     *
     * @return bool
     */
    private function showGrossPrices()
    {
        return (bool) $this->userData['additional']['show_net'];
    }

    /**
     * Returns a value indicating whether or not only the net prices without
     * any tax should be used in the total amount object.
     *
     * @return bool
     */
    private function useNetPriceCalculation()
    {
        if (!empty($this->userData['additional']['countryShipping']['taxfree'])) {
            return true;
        }

        if (empty($this->userData['additional']['countryShipping']['taxfree_ustid'])) {
            return false;
        }

        if (empty($this->userData['shippingaddress']['ustid']) &&
            !empty($this->userData['billingaddress']['ustid']) &&
            !empty($this->userData['additional']['country']['taxfree_ustid'])) {
            return true;
        }

        return !empty($this->userData['shippingaddress']['ustid']);
    }

    /**
     * @param float|string $price
     *
     * @return float
     */
    private function formatPrice($price)
    {
        return round((float) str_replace(',', '.', $price), 2);
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
        $applicationContext->setLocale($this->getLocale());

        if ($paymentType === PaymentType::PAYPAL_EXPRESS) {
            $applicationContext->setUserAction('continue');
        }

        return $applicationContext;
    }

    /**
     * Returns the locale as a 5 digit ISO code
     *
     * @return string
     */
    private function getLocale()
    {
        $locale = $this->dependencyProvider->getShop()->getLocale()->getLocale();

        if (strpos($locale, 'de_') === 0) {
            $locale = 'de_DE';
        }

        return $locale;
    }

    /**
     * @return string
     */
    private function getBrandName()
    {
        $brandName = (string) $this->settings->get('brand_name', SettingsTable::GENERAL);

        if (strlen($brandName) > 127) {
            $brandName = substr($brandName, 0, 127);
        }

        return $brandName;
    }
}
