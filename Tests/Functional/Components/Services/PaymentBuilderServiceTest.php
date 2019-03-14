<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class PaymentBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_basket_service_available()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(PaymentType::PAYPAL_CLASSIC, 0);

        $requestService = $this->getRequestService($settingService);

        $this->assertNotNull($requestService);
    }

    public function test_getPayment_return_plus_intent()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_PLUS);

        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_sale_intent_without_plus()
    {
        $requestParameters = $this->getRequestData();

        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_authorize_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, 1);

        $this->assertEquals('authorize', $requestParameters['intent']);
    }

    public function test_getPayment_return_order_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, 2);

        $this->assertEquals('order', $requestParameters['intent']);
    }

    public function test_getPayment_return_valid_payer()
    {
        $requestParameters = $this->getRequestData();

        $this->assertEquals('paypal', $requestParameters['payer']['payment_method']);
    }

    public function test_getPayment_return_valid_transactions()
    {
        $requestParameters = $this->getRequestData();

        // test the amount sub array
        $this->assertEquals('EUR', $requestParameters['transactions'][0]['amount']['currency']);
        $this->assertEquals('114.99', $requestParameters['transactions'][0]['amount']['total']);
        // test the amount details
        $this->assertEquals(55, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertEquals('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_show_gross()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        $this->assertEquals('136.84', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertEquals(18.36, $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_basket_unique_id()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setBasketUniqueId('MyUniqueBasketId');

        $requestParameters = $requestService->getPayment($params);

        $this->assertStringEndsWith('basketId/MyUniqueBasketId', $requestParameters->getRedirectUrls()->getReturnUrl());
    }

    public function test_getPayment_with_tax_free_country()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        $this->assertEquals('96.63', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_tax_free_companies_without_vat_id()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        $this->assertEquals('114.99', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(55, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertEquals('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_tax_free_companies_with_vat_id()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = 'VATID123';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        $this->assertEquals('96.63', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_tax_free_companies_with_vat_id_billing()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['additional']['country']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = 'VATID123';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        $this->assertEquals('96.63', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_return_valid_redirect_urls()
    {
        $requestParameters = $this->getRequestData();

        $this->assertNotFalse(stristr($requestParameters['redirect_urls']['return_url'], 'return'));
        $this->assertNotFalse(stristr($requestParameters['redirect_urls']['cancel_url'], 'cancel'));
    }

    public function test_getPayment_with_custom_products()
    {
        $requestParameters = $this->getRequestData();

        $customProductsOption = $requestParameters['transactions'][0]['item_list']['items'][0];

        // summed up price -> product price and configurations
        $this->assertEquals(round(2 * 59.99 + 1 * 1 + 2 * 2 + 1 * 3, 2), (float) $customProductsOption['price']);
    }

    public function test_getPayment_express_checkout_without_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getPayment_express_checkout_with_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getIntentAsString_throws_exception()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 99, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->expectException(\RuntimeException::class);
        $requestService->getPayment($params);
    }

    public function test_getPayment_with_installments_payment_type()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 2, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_INSTALLMENTS);

        $payment = $requestService->getPayment($params);
        $this->assertEquals('order', $payment->getIntent());
    }

    /**
     * @param string $paymentType
     * @param int    $intent
     *
     * @return array
     */
    private function getRequestData($paymentType = PaymentType::PAYPAL_CLASSIC, $intent = 0)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock($paymentType === PaymentType::PAYPAL_PLUS, $intent);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType($paymentType);

        return $requestService->getPayment($params)->toArray();
    }

    /**
     * @return PaymentBuilderService
     */
    private function getRequestService(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');
        $snippetManager = Shopware()->Container()->get('snippets');
        $dependencyProvider = Shopware()->Container()->get('paypal_unified.dependency_provider');

        return new PaymentBuilderService($router, $settingService, $snippetManager, $dependencyProvider);
    }

    /**
     * @return array
     */
    private function getBasketDataArray()
    {
        return [
            'Amount' => '59,99',
            'AmountNet' => '50,41',
            'Quantity' => 1,
            'AmountNumeric' => 114.99000000000001,
            'AmountNetNumeric' => 96.629999999999995,
            'AmountWithTax' => '136,8381',
            'AmountWithTaxNumeric' => 136.8381,
            'sCurrencyName' => 'EUR',
            'sShippingcostsWithTax' => 55.0,
            'sShippingcostsNet' => 46.219999999999999,
            'sAmountTax' => 18.359999999999999,
            'sAmountWithTax' => 136.8381,
            'content' => [
                [
                    'ordernumber' => 'SW10137',
                    'articlename' => 'Fahrerbrille Chronos',
                    'quantity' => '2',
                    'price' => '59,99',
                    'netprice' => '50.411764705882',
                    'sCurrencyName' => 'EUR',
                    'customProductMode' => '1',
                ], [
                    'articlename' => 'test',
                    'quantity' => '1',
                    'price' => '1',
                    'customProductMode' => '2',
                ], [
                    'articlename' => 'a',
                    'quantity' => '2',
                    'price' => '2',
                    'customProductMode' => '3',
                ], [
                    'articlename' => 'b',
                    'quantity' => '2',
                    'price' => '3',
                    'customProductIsOncePrice' => true,
                    'customProductMode' => '3',
                ], [
                    'articlename' => 'test-break',
                    'customProductMode' => 4,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getUserDataAsArray()
    {
        return [
            'additional' => [
                'show_net' => true,
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }
}

class SettingsServicePaymentBuilderServiceMock implements SettingsServiceInterface
{
    /**
     * @var bool
     */
    private $plus_active;

    /**
     * @var int
     */
    private $paypal_payment_intent;

    /**
     * @var bool
     */
    private $ec_submit_cart;

    public function __construct($plusActive, $paypalPaymentIntent, $submitCart = true)
    {
        // do not delete, even if PHPStorm says they are unused
        // used in the get() method
        $this->plus_active = $plusActive;
        $this->paypal_payment_intent = $paypalPaymentIntent;
        $this->ec_submit_cart = $submitCart;
    }

    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL)
    {
    }

    public function get($column, $settingsTable = SettingsTable::GENERAL)
    {
        if ($column === 'active' && $settingsTable === SettingsTable::PLUS) {
            return $this->plus_active;
        }

        if ($column === 'intent') {
            return $this->paypal_payment_intent;
        }

        if ($column === 'submit_cart' && $settingsTable === SettingsTable::EXPRESS_CHECKOUT) {
            return $this->ec_submit_cart;
        }

        if ($column === 'brand_name') {
            return 'TestBrandName';
        }

        return $this->$column;
    }

    public function hasSettings($settingsTable = SettingsTable::GENERAL)
    {
    }

    public function refreshDependencies()
    {
    }
}
