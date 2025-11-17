<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\PayPalOrder;

use LengthException;
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
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    public function __construct(
        LoggerServiceInterface $loggerService,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper
    ) {
        $this->loggerService = $loggerService;
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

        /** @var Item[] $items */
        $items = [];

        $currency = $cart['sCurrencyName'];
        $useGrossPrices = $this->customerHelper->usesGrossPrice($customer);
        $isPayUponInvoice = $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;

        foreach ($lineItems as $lineItem) {
            $isEsdProduct = (int) $lineItem['esdarticle'] > 0;
            $label = $lineItem['articlename'];
            $number = (string) $lineItem['ordernumber'];
            $quantity = $lineItem['quantity'];
            $value = $this->priceFormatter->roundPrice($lineItem['priceNumeric']);

            if (!$useGrossPrices || $isPayUponInvoice) {
                $value = $this->priceFormatter->roundPrice($lineItem['netprice']);
            }

            $item = new Item();
            $this->setName($label, $lineItem, $item);
            $this->setSku($number, $lineItem, $item);

            $unitAmount = new UnitAmount();
            $unitAmount->setCurrencyCode($currency);
            $unitAmount->setValue((string) $value);

            $item->setUnitAmount($unitAmount);
            $item->setQuantity($quantity);
            $item->setCategory($isEsdProduct ? Item::DIGITAL_GOODS : Item::PHYSICAL_GOODS);

            if ($isPayUponInvoice || !$useGrossPrices) {
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
        } catch (LengthException $e) {
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
        } catch (LengthException $e) {
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
        $tax->setValue(\sprintf('%.2f', $this->getSingleItemTaxAmount($lineItem, $customer)));

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

        if ($this->customerHelper->usesGrossPrice($customer)) {
            return $this->priceFormatter->roundPrice($lineItem['priceNumeric']) - $this->priceFormatter->roundPrice($lineItem['netprice']);
        }

        return $this->priceFormatter->roundPrice($this->priceFormatter->roundPrice($lineItem['tax']) / $lineItem['quantity']);
    }
}
