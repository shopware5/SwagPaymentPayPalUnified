<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\PayPalOrder;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;

class Fixture
{
    const CUSTOMER = [
        'billingaddress' => [
            'id' => 1,
            'company' => null,
            'department' => null,
            'salutation' => 'mr',
            'firstname' => 'Test',
            'title' => null,
            'lastname' => 'Test',
            'street' => 'Ebbinghoff 10',
            'zipcode' => '48624',
            'city' => 'Schöppingen',
            'phone' => '02555928850',
            'vatId' => null,
            'additionalAddressLine1' => null,
            'additionalAddressLine2' => null,
            'countryId' => 2,
            'stateId' => null,
            'customer' => [
                'id' => 1,
            ],
            'country' => [
                'id' => 2,
            ],
            'state' => null,
            'userID' => 1,
            'countryID' => 2,
            'stateID' => null,
            'ustid' => null,
            'additional_address_line1' => null,
            'additional_address_line2' => null,
            'attributes' => [
                'id' => '1',
                'address_id' => '1',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
        ],
        'additional' => [
            'country' => [
                'id' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'areaID' => '1',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '1',
                'iso3' => 'DEU',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
                'allow_shipping' => '1',
                'countryarea' => 'deutschland',
            ],
            'state' => [
            ],
            'user' => [
                'id' => '1',
                'userID' => '1',
                'password' => '',
                'encoder' => 'bcrypt',
                'email' => 'test@example.com',
                'active' => '1',
                'accountmode' => '0',
                'confirmationkey' => '',
                'paymentID' => '8',
                'doubleOptinRegister' => '0',
                'doubleOptinEmailSentDate' => null,
                'doubleOptinConfirmDate' => null,
                'firstlogin' => '2022-03-16',
                'lastlogin' => '2022-03-16 08:58:40',
                'sessionID' => '13d7f93e3cda7b56acffd169111bcab6',
                'newsletter' => 0,
                'validation' => '',
                'affiliate' => '0',
                'customergroup' => 'EK',
                'paymentpreset' => '0',
                'language' => '1',
                'subshopID' => '1',
                'referer' => '',
                'pricegroupID' => null,
                'internalcomment' => '',
                'failedlogins' => '0',
                'lockeduntil' => null,
                'default_billing_address_id' => '1',
                'default_shipping_address_id' => '1',
                'title' => null,
                'salutation' => 'mr',
                'firstname' => 'Test',
                'lastname' => 'Test',
                'birthday' => '1970-01-01',
                'customernumber' => '20003',
                'login_token' => '68f2eb2b-d2f7-4476-bc41-be1301700239.1',
                'changed' => '2022-03-16 08:50:53',
                'password_change_date' => '2022-03-16 08:50:52',
                'register_opt_in_id' => null,
            ],
            'countryShipping' => [
                'id' => '2',
                'countryname' => 'Deutschland',
                'countryiso' => 'DE',
                'areaID' => '1',
                'countryen' => 'GERMANY',
                'position' => '1',
                'notice' => '',
                'taxfree' => '0',
                'taxfree_ustid' => '0',
                'taxfree_ustid_checked' => '0',
                'active' => '1',
                'iso3' => 'DEU',
                'display_state_in_registration' => '0',
                'force_state_in_registration' => '0',
                'allow_shipping' => '1',
                'countryarea' => 'deutschland',
            ],
            'stateShipping' => [
            ],
            'payment' => [
                'id' => '8',
                'name' => 'SwagPaymentPayPalUnifiedPayUponInvoice',
                'description' => 'Rechnungskauf',
                'template' => '',
                'class' => '',
                'table' => '',
                'hide' => '0',
                'additionaldescription' => '',
                'debit_percent' => '0',
                'surcharge' => '0',
                'surchargestring' => '',
                'position' => '-99',
                'active' => '1',
                'esdactive' => '0',
                'embediframe' => '',
                'hideprospect' => '0',
                'action' => 'PaypalUnifiedV2PayUponInvoice',
                'pluginID' => '57',
                'source' => null,
                'mobile_inactive' => '0',
                'attributes' => [
                ],
                'validation' => [
                ],
            ],
            'charge_vat' => true,
            'show_net' => true,
        ],
        'shippingaddress' => [
            'id' => 1,
            'company' => null,
            'department' => null,
            'salutation' => 'mr',
            'firstname' => 'Test',
            'title' => null,
            'lastname' => 'Test',
            'street' => 'Ebbinghoff 10',
            'zipcode' => '48624',
            'city' => 'Schöppingen',
            'phone' => '02555928850',
            'vatId' => null,
            'additionalAddressLine1' => null,
            'additionalAddressLine2' => null,
            'countryId' => 2,
            'stateId' => null,
            'customer' => [
                'id' => 1,
            ],
            'country' => [
                'id' => 2,
            ],
            'state' => null,
            'userID' => 1,
            'countryID' => 2,
            'stateID' => null,
            'ustid' => null,
            'additional_address_line1' => null,
            'additional_address_line2' => null,
            'attributes' => [
                'id' => '1',
                'address_id' => '1',
                'text1' => null,
                'text2' => null,
                'text3' => null,
                'text4' => null,
                'text5' => null,
                'text6' => null,
            ],
        ],
        'customerGroupUseGrossPrices' => true,
    ];

    const CART = [
        'content' => [
            [
                'id' => '1',
                'sessionID' => '13d7f93e3cda7b56acffd169111bcab6',
                'userID' => '1',
                'articlename' => 'Test product',
                'articleID' => '32',
                'ordernumber' => '2df7f76b-3e74-4062-a788-ab260aed5c78',
                'shippingfree' => '0',
                'quantity' => '10',
                'price' => '9,99',
                'netprice' => '8.3949579831933',
                'tax_rate' => '19',
                'datum' => '2022-03-16 08:52:45',
                'modus' => '0',
                'esdarticle' => '0',
                'partnerID' => '',
                'lastviewport' => 'checkout',
                'useragent' => '',
                'config' => '',
                'currencyFactor' => '1',
                'packunit' => '',
                'mainDetailId' => '32',
                'articleDetailId' => '32',
                'minpurchase' => '1',
                'taxID' => '1',
                'instock' => '0',
                'suppliernumber' => '',
                'maxpurchase' => '100',
                'purchasesteps' => 1,
                'purchaseunit' => null,
                'laststock' => '0',
                'shippingtime' => null,
                'releasedate' => null,
                'sReleaseDate' => null,
                'ean' => '',
                'stockmin' => '0',
                'ob_attr1' => '',
                'ob_attr2' => null,
                'ob_attr3' => null,
                'ob_attr4' => null,
                'ob_attr5' => null,
                'ob_attr6' => null,
                '__s_order_basket_attributes_id' => '1',
                '__s_order_basket_attributes_basketID' => '1',
                '__s_order_basket_attributes_attribute1' => '',
                '__s_order_basket_attributes_attribute2' => null,
                '__s_order_basket_attributes_attribute3' => null,
                '__s_order_basket_attributes_attribute4' => null,
                '__s_order_basket_attributes_attribute5' => null,
                '__s_order_basket_attributes_attribute6' => null,
                'shippinginfo' => true,
                'esd' => '0',
                'additional_details' => [
                    'articleID' => 32,
                    'articleDetailsID' => 32,
                    'ordernumber' => '2df7f76b-3e74-4062-a788-ab260aed5c78',
                    'highlight' => false,
                    'description' => '',
                    'description_long' => '',
                    'esd' => false,
                    'articleName' => 'Test product',
                    'taxID' => 1,
                    'tax' => 19.0,
                    'instock' => 0,
                    'isAvailable' => true,
                    'hasAvailableVariant' => true,
                    'weight' => 0.0,
                    'shippingtime' => null,
                    'pricegroupActive' => false,
                    'pricegroupID' => null,
                    'length' => 0.0,
                    'height' => 0.0,
                    'width' => 0.0,
                    'laststock' => false,
                    'additionaltext' => '',
                    'datum' => '2022-03-16',
                    'update' => '2022-03-16',
                    'sales' => 0,
                    'filtergroupID' => null,
                    'priceStartingFrom' => null,
                    'pseudopricePercent' => null,
                    'sVariantArticle' => null,
                    'sConfigurator' => false,
                    'metaTitle' => '',
                    'shippingfree' => false,
                    'suppliernumber' => '',
                    'notification' => false,
                    'ean' => '',
                    'keywords' => '',
                    'sReleasedate' => '',
                    'template' => '',
                    'attributes' => [],
                    'allowBuyInListing' => true,
                    'attr1' => '',
                    'attr2' => '',
                    'attr3' => '',
                    'attr4' => null,
                    'attr5' => null,
                    'attr6' => null,
                    'attr7' => null,
                    'attr8' => null,
                    'attr9' => null,
                    'attr10' => null,
                    'attr11' => null,
                    'attr12' => null,
                    'attr13' => null,
                    'attr14' => null,
                    'attr15' => null,
                    'attr16' => null,
                    'attr17' => null,
                    'attr18' => null,
                    'attr19' => null,
                    'attr20' => null,
                    'supplierName' => 'shpware AG',
                    'supplierImg' => null,
                    'supplierID' => 1,
                    'supplierDescription' => null,
                    'supplierMedia' => null,
                    'supplier_attributes' => [],
                    'newArticle' => true,
                    'sUpcoming' => false,
                    'topseller' => false,
                    'valFrom' => 1,
                    'valTo' => null,
                    'from' => 1,
                    'to' => null,
                    'price' => '9,99',
                    'pseudoprice' => '0',
                    'referenceprice' => '0',
                    'has_pseudoprice' => false,
                    'price_numeric' => 9.99,
                    'pseudoprice_numeric' => 0.0,
                    'price_attributes' => [],
                    'pricegroup' => 'EK',
                    'minpurchase' => 1,
                    'maxpurchase' => '100',
                    'purchasesteps' => 1,
                    'purchaseunit' => null,
                    'referenceunit' => null,
                    'packunit' => '',
                    'unitID' => null,
                    'sUnit' => [
                        'unit' => null,
                        'description' => null,
                    ],
                    'unit_attributes' => [],
                    'prices' => [
                        [
                            'valFrom' => 1,
                            'valTo' => null,
                            'from' => 1,
                            'to' => null,
                            'price' => '9,99',
                            'pseudoprice' => '0',
                            'referenceprice' => '0',
                            'pseudopricePercent' => null,
                            'has_pseudoprice' => false,
                            'price_numeric' => 9.99,
                            'pseudoprice_numeric' => 0.0,
                            'price_attributes' => [
                            ],
                            'pricegroup' => 'EK',
                            'minpurchase' => 1,
                            'maxpurchase' => '100',
                            'purchasesteps' => 1,
                            'purchaseunit' => null,
                            'referenceunit' => null,
                            'packunit' => '',
                            'unitID' => null,
                            'sUnit' => [
                                'unit' => null,
                                'description' => null,
                            ],
                            'unit_attributes' => [
                            ],
                        ],
                    ],
                    'linkBasket' => 'shopware.php?sViewport=basket&sAdd=2df7f76b-3e74-4062-a788-ab260aed5c78',
                    'linkDetails' => 'shopware.php?sViewport=detail&sArticle=32',
                    'linkVariant' => 'shopware.php?sViewport=detail&sArticle=32&number=2df7f76b-3e74-4062-a788-ab260aed5c78',
                ],
                'amount' => '99,90',
                'amountnet' => '83,95',
                'priceNumeric' => '9.99',
                'amountNumeric' => 99.9,
                'amountnetNumeric' => 83.94957983193301,
                'linkDetails' => 'shopware.php?sViewport=detail&sArticle=32',
                'linkDelete' => 'shopware.php?sViewport=basket&sDelete=1',
                'linkNote' => 'shopware.php?sViewport=note&sAdd=2df7f76b-3e74-4062-a788-ab260aed5c78',
                'tax' => '15,95',
            ],
        ],
        'Amount' => '99,90',
        'AmountNet' => '83,95',
        'Quantity' => 1,
        'AmountNumeric' => 99.9,
        'AmountNetNumeric' => 83.95,
        'AmountWithTax' => '0',
        'AmountWithTaxNumeric' => 0.0,
        'sCurrencyId' => 1,
        'sCurrencyName' => 'EUR',
        'sCurrencyFactor' => 1.0,
        'sShippingcostsWithTax' => 0.0,
        'sShippingcostsNet' => 0.0,
        'sShippingcostsTax' => 0.0,
        'sTaxRates' => [
            '19.00' => 15.95,
        ],
        'sShippingcosts' => 0.0,
        'sAmount' => 99.9,
        'sAmountTax' => 15.95,
    ];

    const PRODUCT_PRICE = self::CART['content'][0]['priceNumeric'];
    const QUANTITY = self::CART['content'][0]['quantity'];
    const TAX_RATE_PERCENT = self::CART['content'][0]['tax_rate'];

    /**
     * @return Item
     */
    public static function getItemWithRoundedAmounts()
    {
        return (new Item())->assign([
            'name' => 'Test product',
            'unitAmount' => (new UnitAmount())->assign([
                'currencyCode' => 'EUR',
                'value' => sprintf('%.2f', self::getPrice() / self::getTaxRate()),
            ]),
            'tax' => (new Tax())->assign([
                'currencyCode' => 'EUR',
                'value' => sprintf('%.2f', self::getPrice() - self::getPrice() / self::getTaxRate()),
            ]),
            'taxRate' => '19',
            'quantity' => 10,
            'sku' => '2df7f76b-3e74-4062-a788-ab260aed5c78',
            'category' => 'PHYSICAL_GOODS',
        ]);
    }

    /**
     * @return Item
     */
    public static function getItem()
    {
        return (new Item())->assign([
            'name' => 'Test product',
            'unitAmount' => (new UnitAmount())->assign([
                'currencyCode' => 'EUR',
                'value' => (string) (self::getPrice() / self::getTaxRate()),
            ]),
            'tax' => (new Tax())->assign([
                'currencyCode' => 'EUR',
                'value' => (string) (self::getPrice() - self::getPrice() / self::getTaxRate()),
            ]),
            'taxRate' => '19',
            'quantity' => 10,
            'sku' => '2df7f76b-3e74-4062-a788-ab260aed5c78',
            'category' => 'PHYSICAL_GOODS',
        ]);
    }

    /**
     * This is the kind of struct currently calculated by the AmountProvider.
     *
     * @return Amount
     */
    public static function getMiscalculatedAmount()
    {
        return (new Amount())->assign([
            'breakdown' => [
                'itemTotal' => [
                    'value' => '83.90',
                ],
                'taxTotal' => [
                    'value' => '15.90',
                ],
            ],
            'value' => '99.90',
        ]);
    }

    /**
     * @return Amount
     */
    public static function getAmount()
    {
        return (new Amount())->assign([
            'breakdown' => [
                'itemTotal' => [
                    'value' => '83.95',
                ],
                'taxTotal' => [
                    'value' => '15.95',
                ],
            ],
            'value' => '99.90',
        ]);
    }

    /**
     * @return float
     */
    public static function getPrice()
    {
        return (float) self::PRODUCT_PRICE;
    }

    /**
     * @return int
     */
    public static function getQuantity()
    {
        return (int) self::QUANTITY;
    }

    /**
     * @return float|int
     */
    public static function getTaxRate()
    {
        return (float) self::TAX_RATE_PERCENT / 100 + 1;
    }
}
