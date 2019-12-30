<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;

class PaymentBuilderServiceTest extends TestCase
{
    public function test_is_basket_service_available()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(PaymentType::PAYPAL_CLASSIC, 0);

        $requestService = $this->getRequestService($settingService);

        static::assertNotNull($requestService);
    }

    public function test_getPayment_return_plus_intent()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_PLUS);

        static::assertSame('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_sale_intent_without_plus()
    {
        $requestParameters = $this->getRequestData();

        static::assertSame('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_authorize_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, 1);

        static::assertSame('authorize', $requestParameters['intent']);
    }

    public function test_getPayment_return_order_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, 2);

        static::assertSame('order', $requestParameters['intent']);
    }

    public function test_getPayment_return_valid_payer()
    {
        $requestParameters = $this->getRequestData();

        static::assertSame('paypal', $requestParameters['payer']['payment_method']);
    }

    public function test_getPayment_cut_long_brand_name()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, 0, true);

        static::assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquya', $requestParameters['application_context']['brand_name']);
    }

    public function test_getPayment_return_valid_transactions()
    {
        $requestParameters = $this->getRequestData();

        // test the amount sub array
        static::assertSame('EUR', $requestParameters['transactions'][0]['amount']['currency']);
        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
        // test the amount details
        static::assertSame('55', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertSame('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
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

        static::assertSame('136.84', $requestParameters['transactions'][0]['amount']['total']);
        static::assertSame('46.22', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertSame('18.36', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_show_net_in_frontend()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['countryShipping']['taxfree'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_net_functional_test_step1()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = true;

        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['countryShipping']['taxfree_ustid'] = null;
        $userData['shippingaddress']['ustid'] = null;
        $userData['billingaddress']['ustid'] = null;
        $userData['additional']['country']['taxfree_ustid'] = null;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_net_functional_test_step2()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = null;

        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['countryShipping']['taxfree'] = false;
        $userData['shippingaddress']['ustid'] = null;
        $userData['billingaddress']['ustid'] = null;
        $userData['additional']['country']['taxfree_ustid'] = null;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_net_functional_test_step3()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = null;
        $userData['billingaddress']['ustid'] = '1';
        $userData['additional']['country']['taxfree_ustid'] = '1';

        $userData['additional']['countryShipping']['taxfree'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_net_functional_test_step4()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = null;

        $userData['additional']['country']['taxfree_ustid'] = '1';
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_net_functional_test_step5()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = null;
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

        $userData['additional']['country']['taxfree_ustid'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_gross_functional_test_step6()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = '1';
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['country']['taxfree_ustid'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    public function test_useNetPriceCalculation_should_be_gross_functional_test_step7()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['billingaddress']['ustid'] = null;

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

        $userData['shippingaddress']['ustid'] = null;
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['country']['taxfree_ustid'] = null;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
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

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString(
                '/PaypalUnified/return/basketId/MyUniqueBasketId',
                $requestParameters->getRedirectUrls()->getReturnUrl()
            );

            return;
        }
        static::assertContains(
            '/PaypalUnified/return/basketId/MyUniqueBasketId',
            $requestParameters->getRedirectUrls()->getReturnUrl()
        );
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

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
        static::assertSame('46.22', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
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

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
        static::assertSame('55', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertSame('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_with_tax_free_companies_with_vat_id()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = 'VATID123';
        $userData[PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
        static::assertSame('46.22', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
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

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
        static::assertSame('46.22', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_getPayment_return_valid_redirect_urls()
    {
        $requestParameters = $this->getRequestData();

        static::assertNotFalse(stristr($requestParameters['redirect_urls']['return_url'], 'return'));
        static::assertNotFalse(stristr($requestParameters['redirect_urls']['cancel_url'], 'cancel'));
    }

    public function test_getPayment_with_custom_products()
    {
        $requestParameters = $this->getRequestData();

        $customProductsOption = $requestParameters['transactions'][0]['item_list']['items'][0];

        // summed up price -> product price and configurations
        static::assertSame(round(2 * 59.99 + 1 * 1 + 2 * 2 + 1 * 3, 2), (float) $customProductsOption['price']);
    }

    public function test_getPayment_express_checkout_without_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, false, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        static::assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getPayment_express_checkout_with_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, true, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        static::assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getPayment_without_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, false, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_CLASSIC);

        static::assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getPayment_with_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, false, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_CLASSIC);

        static::assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
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
        static::assertSame('order', $payment->getIntent());
    }

    /**
     * @param string $paymentType
     * @param int    $intent
     *
     * @return array
     */
    private function getRequestData($paymentType = PaymentType::PAYPAL_CLASSIC, $intent = 0, $longBrandName = false)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(
            $paymentType === PaymentType::PAYPAL_PLUS,
            $intent,
            true,
            true,
            $longBrandName
        );
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
            PaymentBuilderInterface::CUSTOMER_GROUP_USE_GROSS_PRICES => true,
            'additional' => [
                'show_net' => true,
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }
}
