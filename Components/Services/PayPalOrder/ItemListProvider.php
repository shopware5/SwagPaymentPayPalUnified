<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\PayPalOrder;

use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;

class ItemListProvider
{
    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

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

    public function __construct(
        LoggerServiceInterface $loggerService,
        SnippetManager $snippetManager,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper
    ) {
        $this->loggerService = $loggerService;
        $this->snippetManager = $snippetManager;
        $this->priceFormatter = $priceFormatter;
        $this->customerHelper = $customerHelper;
    }

    /**
     * @param array <string,mixed> $cart
     * @param array <string,mixed> $customer
     * @param PaymentType::*       $paymentType
     *
     * @return Item[]|null
     */
    public function getItemList(array $cart, array $customer, $paymentType)
    {
        $lineItems = $cart['content'];
        if ($lineItems === []) {
            return null;
        }

        $currency = $cart['sCurrencyName'];
        /** @var Item[] $items */
        $items = [];

        $customProductMainLineItemKey = 0;
        $customProductsHint = $this->snippetManager->getNamespace('frontend/paypal_unified/checkout/item_list')
            ->get('paymentBuilder/customProductsHint', ' incl. surcharges for Custom Products configuration');

        foreach ($lineItems as $key => $lineItem) {
            $label = $lineItem['articlename'];
            $number = (string) $lineItem['ordernumber'];
            $quantity = $lineItem['quantity'];
            $value = $this->priceFormatter->roundPrice($lineItem['price']);

            if (!$this->customerHelper->usesGrossPrice($customer) || $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2) {
                $value = $this->priceFormatter->roundPrice($lineItem['netprice']);
            }

            // In the following part, we modify the CustomProducts positions.
            // All position prices of the Custom Products configuration are added up, so that no items with 0€ are committed to PayPal
            if (!empty($lineItem['customProductMode'])) {
                //A value indicating if the surcharge of this position is only being added once
                $isSingleSurcharge = $lineItem['customProductIsOncePrice'];

                switch ($lineItem['customProductMode']) {
                    case 1:
                        $customProductMainLineItemKey = $key;
                        $label .= $customProductsHint;

                        if ($quantity !== 1) {
                            $value *= $quantity;
                            $label = $quantity . 'x ' . $label;
                            $quantity = 1;
                        }

                        break;
                    case 2: //Option
                    case 3: //Value
                        //Calculate the total price
                        if (!$isSingleSurcharge) {
                            $value *= $quantity;
                        }

                        $mainProduct = $items[$customProductMainLineItemKey];
                        $mainProduct->getUnitAmount()->setValue((string) ((float) $mainProduct->getUnitAmount()->getValue() + $value));
                        continue 2;
                }
            }

            $item = new Item();
            $this->setName($label, $lineItem, $item);
            $this->setSku($number, $lineItem, $item);

            $unitAmount = new UnitAmount();
            $unitAmount->setCurrencyCode($currency);
            $unitAmount->setValue((string) $value);

            $item->setUnitAmount($unitAmount);
            $item->setQuantity($quantity);

            if ((int) $lineItem['esdarticle'] > 0) {
                $item->setCategory('DIGITAL_GOODS');
            } else {
                $item->setCategory('PHYSICAL_GOODS');
            }

            if ($paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2) {
                $this->setTaxInformation($currency, $lineItem, $customer, $item);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param string $label
     *
     * @return void
     */
    private function setName($label, array $lineItem, Item $item)
    {
        $label = (string) $label;
        try {
            $item->setName($label);
        } catch (\LengthException $e) {
            $this->loggerService->warning($e->getMessage(), ['lineItem' => $lineItem]);
            $item->setName(\mb_substr($label, 0, Item::MAX_LENGTH_NAME));
        }
    }

    /**
     * @param string $number
     *
     * @return void
     */
    private function setSku($number, array $lineItem, Item $item)
    {
        if ($number === '') {
            return;
        }

        try {
            $item->setSku($number);
        } catch (\LengthException $e) {
            $this->loggerService->warning($e->getMessage(), ['lineItem' => $lineItem]);
            $item->setSku(\mb_substr($number, 0, Item::MAX_LENGTH_SKU));
        }
    }

    /**
     * @param string              $currency
     * @param array<string,mixed> $lineItem
     * @param array<string,mixed> $customer
     *
     * @return void
     */
    private function setTaxInformation($currency, array $lineItem, array $customer, Item $item)
    {
        $tax = new Tax();

        $tax->setCurrencyCode($currency);
        $tax->setValue(sprintf('%.2f', $this->getSingleItemTaxAmount($lineItem, $customer)));

        $item->setTax($tax);
        $item->setTaxRate($lineItem['tax_rate']);
    }

    /**
     * @param array<string,mixed> $lineItem
     * @param array<string,mixed> $customer
     *
     * @return float
     */
    private function getSingleItemTaxAmount(array $lineItem, array $customer)
    {
        if (!$this->customerHelper->chargeVat($customer)) {
            /*
             * If we don't need to charge any VAT, the tax sum is 0 accordingly.
             * This is the case, if the country of residence is excluded from
             * tax calculation for example.
             */
            return 0.0;
        }

        return $this->priceFormatter->roundPrice($lineItem['priceNumeric']) - $this->priceFormatter->roundPrice($lineItem['netprice']);
    }
}
