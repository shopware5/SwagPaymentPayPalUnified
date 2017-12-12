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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
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
        $paymentType = $params->getPaymentType();

        if ($paymentType === PaymentType::PAYPAL_EXPRESS || $paymentType === PaymentType::PAYPAL_CLASSIC) {
            $requestParameters->setIntent($this->getIntentAsString((int) $this->settings->get('intent', SettingsTable::EXPRESS_CHECKOUT)));
        } elseif ($paymentType === PaymentType::PAYPAL_INSTALLMENTS) {
            $requestParameters->setIntent($this->getIntentAsString((int) $this->settings->get('intent', SettingsTable::INSTALLMENTS)));
        } else {
            $requestParameters->setIntent('sale');
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
        if ($paymentType !== PaymentType::PAYPAL_EXPRESS || $this->settings->get('submit_cart', SettingsTable::EXPRESS_CHECKOUT)) {
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
        /** @var array $basketContent */
        $basketContent = $this->basketData['content'];
        $index = 0;

        foreach ($basketContent as $basketItem) {
            $sku = $basketItem['ordernumber'];
            $name = $basketItem['articlename'];
            $quantity = (int) $basketItem['quantity'];

            $price = $this->showGrossPrices() === true
                ? str_replace(',', '.', $basketItem['price'])
                : $basketItem['netprice'];

            //In the following part, we modify the CustomProducts positions.
            //By default, custom products may add a lot of different positions to the basket, which would probably reach
            //the items limit of PayPal. Therefore, we group the values with the options.
            //Actually, that causes a loss of quantity precision but there is no other way around this issue but this.
            if (!empty($basketItem['customProductMode'])) {
                //A value indicating if the surcharge of this position is only being added once
                $isSingleSurcharge = $basketItem['customProductIsOncePrice'];

                switch ($basketItem['customProductMode']) {
                    /*
                     * The current basket item is of type Option (a group of values)
                     * This will be our first starting point.
                     * In this procedure we fake the amount by simply adding a %value%x to the actual name of the group.
                     * Further more, we add a : to the end of the name (if a value follows this option) to indicate that more values follow.
                     * At the end, we set the quantity to 1, so PayPal doesn't calculate the total amount. That would cause calculation errors, since we calculate the
                     * whole position already.
                     */
                    case 2: //Option
                        $nextProduct = $basketContent[$index + 1];

                        $name = $quantity . 'x ' . $name;

                        //Another value is following?
                        if ($nextProduct && $nextProduct['customProductMode'] === '3') {
                            $name .= ': ';
                        }

                        //Calculate the total price of this option
                        if (!$isSingleSurcharge) {
                            $price *= $quantity;
                        }

                        $quantity = 1;
                        break;

                    /*
                     * This basket item is of type Value.
                     * In this procedure we calculate the actual price of the value and add it to the option price.
                     * Further more, we add a comma to the end of the value (if another value is following) to improve the readability on the PayPal page.
                     * Afterwards, we set the quantity to 0, so that the basket item is not being added to the list. We don't have to add it again,
                     * since it's already grouped to the option.
                     */
                    case 3: //Value
                        //The last option that has been added to the final list.
                        //This value will be grouped to it.
                        $nextProduct = $basketContent[$index + 1];
                        /** @var Item $lastGroup */
                        $lastGroup = &$list[count($list) - 1];
                        $lastGroupName = $lastGroup->getName();
                        $lastGroupPrice = $lastGroup->getPrice();

                        if ($lastGroup) {
                            //Check if another value is following, if so, add a comma to the end of the name.
                            if ($nextProduct && $nextProduct['customProductMode'] === '3') {
                                //Another value is following
                                $lastGroup->setName($lastGroupName . $name . ', ');
                            } else {
                                //This is the last value in this option
                                $lastGroup->setName($lastGroupName . $name);
                            }

                            //Calculate the total price.
                            if ($isSingleSurcharge) {
                                $lastGroup->setPrice($lastGroupPrice + $price);
                            } else {
                                $lastGroup->setPrice($lastGroupPrice + $price * $quantity);
                            }

                            //Don't add it to the final list
                            $quantity = 0;
                        }
                        break;
                }
            }

            if ($quantity !== 0) {
                $item = new Item();
                $item->setCurrency($this->basketData['sCurrencyName']);
                $item->setName($name);
                $item->setPrice($price);
                $item->setQuantity($quantity);

                if ($sku !== null && $sku !== '') {
                    $item->setSku($sku);
                }

                $list[] = $item;
            }

            ++$index;
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
