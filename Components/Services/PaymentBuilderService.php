<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Routing\Router;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
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
     * @param Router                   $router
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct(Router $router, SettingsServiceInterface $settingsService)
    {
        $this->router = $router;
        $this->settings = $settingsService;
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

        if ($this->settings->get('plus_active')) {
            $requestParameters->setIntent('sale');
        } else {
            //For the "classic" integration it's possible to use further intents.
            $intent = (int) $this->settings->get('paypal_payment_intent');

            switch ($intent) {
                case 0:
                    $requestParameters->setIntent(PaymentIntent::SALE);
                    break;
                case 1:
                    $requestParameters->setIntent(PaymentIntent::AUTHORIZE);
                    break;
                case 2:
                    $requestParameters->setIntent(PaymentIntent::ORDER);
                    break;
            }
        }

        $requestParameters->setProfile($params->getWebProfileId());

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
        if ($params->getPaymentType() !== PaymentType::PAYPAL_EXPRESS || $this->settings->get('ec_submit_cart')) {
            $itemList = new ItemList();
            $itemList->setItems($this->getItemList());

            $transactions->setItemList($itemList);
        }

        $requestParameters->setPayer($payer);
        $requestParameters->setRedirectUrls($redirectUrls);
        $requestParameters->setTransactions($transactions);

        return $requestParameters;
    }

    /**
     * @return float
     */
    private function getTotalAmount()
    {
        //Case 1: Show gross prices in shopware and don't exclude country tax
        if ($this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            return $this->basketData['AmountNumeric'];
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            return $this->basketData['AmountWithTaxNumeric'];
        }

        //Case 3: No tax handling at all, just use the net amounts.
        return $this->basketData['AmountNetNumeric'];
    }

    /**
     * @return Item[]
     */
    private function getItemList()
    {
        $list = [];
        $lastCustomProduct = null;

        foreach ($this->basketData['content'] as $basketItem) {
            $sku = $basketItem['ordernumber'];
            $name = $basketItem['articlename'];
            $quantity = (int) $basketItem['quantity'];

            $price = $this->showGrossPrices() === true
                ? str_replace(',', '.', $basketItem['price'])
                : $basketItem['netprice'];

            // Add support for custom products
            if (!empty($basketItem['customProductMode'])) {
                switch ($basketItem['customProductMode']) {
                    case 1: // Product
                        $lastCustomProduct = count($list);
                        break;
                    case 2: // Option
                        if (empty($sku) && isset($list[$lastCustomProduct])) {
                            /** @var Item $lastItem */
                            $lastItem = $list[$lastCustomProduct];
                            $sku = $lastItem->getSku();
                        }
                        break;
                    case 3: // Value
                        $last = count($list) - 1;
                        if (isset($list[$last])) {
                            /** @var Item $lastItem */
                            $lastItem = $list[$last];

                            $lastItemName = $lastItem->getName();
                            $lastItemPrice = (float) $lastItem->getPrice();

                            if (strpos($lastItemName, ': ') === false) {
                                $lastItem->setName($lastItemName . ': ' . $name);
                            } else {
                                $lastItem->setName($lastItemName . ', ' . $name);
                            }

                            $lastItem->setPrice($lastItemPrice + $price);
                        }
                        continue 2;
                    default:
                        break;
                }
            }

            $result = new Item();
            $result->setCurrency($this->basketData['sCurrencyName']);
            $result->setName($name);
            $result->setSku($sku);
            $result->setPrice($price);
            $result->setQuantity($quantity);

            $list[] = $result;
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
            $amountDetails->setShipping($this->basketData['sShippingcostsWithTax']);
            $amountDetails->setSubTotal(str_replace(',', '.', $this->basketData['Amount']));
            $amountDetails->setTax(number_format(0, 2));

            return $amountDetails;
        }

        //Case 2: Show net prices in shopware and don't exclude country tax
        if (!$this->showGrossPrices() && !$this->useNetPriceCalculation()) {
            $amountDetails->setShipping($this->basketData['sShippingcostsNet']);
            $amountDetails->setSubTotal(str_replace(',', '.', $this->basketData['AmountNet']));
            $amountDetails->setTax($this->basketData['sAmountTax']);

            return $amountDetails;
        }

        //Case 3: No tax handling at all, just use the net amounts.
        $amountDetails->setShipping($this->basketData['sShippingcostsNet']);
        $amountDetails->setSubTotal(str_replace(',', '.', $this->basketData['AmountNet']));

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
        return (bool) $this->userData['additional']['country']['taxfree'];
    }
}
