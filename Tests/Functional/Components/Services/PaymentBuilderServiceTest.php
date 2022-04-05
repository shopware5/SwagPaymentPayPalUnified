<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;

class PaymentBuilderServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsBasketServiceAvailable()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);

        $requestService = $this->getRequestService($settingService);

        static::assertNotNull($requestService);
    }

    /**
     * @return void
     */
    public function testGetPaymentReturnPlusIntent()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_PLUS);

        static::assertSame('sale', $requestParameters['intent']);
    }

    /**
     * @return void
     */
    public function testGetPaymentReturnSaleIntentWithoutPlus()
    {
        $requestParameters = $this->getRequestData();

        static::assertSame('sale', $requestParameters['intent']);
    }

    /**
     * @return void
     */
    public function testGetPaymentReturnValidPayer()
    {
        $requestParameters = $this->getRequestData();

        static::assertSame('paypal', $requestParameters['payer']['payment_method']);
    }

    /**
     * @return void
     */
    public function testGetPaymentCutLongBrandName()
    {
        $requestParameters = $this->getRequestData(PaymentType::PAYPAL_CLASSIC, true);

        static::assertSame('Lorem ipsum dolor sit amet consetetur sadipscing elitr sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquya', $requestParameters['application_context']['brand_name']);
    }

    /**
     * @return void
     */
    public function testGetPaymentReturnValidTransactions()
    {
        $requestParameters = $this->getRequestData();

        // test the amount sub array
        static::assertSame('EUR', $requestParameters['transactions'][0]['amount']['currency']);
        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
        // test the amount details
        static::assertSame('55.00', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertSame('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    /**
     * @return void
     */
    public function testGetPaymentWithShowGross()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
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

    /**
     * @return void
     */
    public function testGetPaymentWithShowNetInFrontend()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['countryShipping']['taxfree'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeNetFunctionalTestStep1()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = true;

        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
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

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeNetFunctionalTestStep2()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = null;

        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
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

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeNetFunctionalTestStep3()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = '1';
        $userData['additional']['country']['taxfree_ustid'] = '1';

        $userData['additional']['countryShipping']['taxfree'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeNetFunctionalTestStep4()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = null;

        $userData['additional']['country']['taxfree_ustid'] = '1';
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
    }

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeNetFunctionalTestStep5()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = null;
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

        $userData['additional']['country']['taxfree_ustid'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('114.99', $requestParameters['transactions'][0]['amount']['total']);
    }

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeGrossFunctionalTestStep6()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        // Should match
        $userData['additional']['countryShipping']['taxfree'] = null;
        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['billingaddress']['ustid'] = null;
        $userData['shippingaddress']['ustid'] = '1';
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;
        $userData['additional']['country']['taxfree_ustid'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        $requestParameters = $requestService->getPayment($params);
        $requestParameters = $requestParameters->toArray();

        static::assertSame('96.63', $requestParameters['transactions'][0]['amount']['total']);
    }

    /**
     * @return void
     */
    public function testUseNetPriceCalculationShouldBeGrossFunctionalTestStep7()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        // Should match
        $userData['billingaddress']['ustid'] = null;

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = true;

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

    /**
     * @return void
     */
    public function testGetPaymentWithBasketUniqueId()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setBasketUniqueId('MyUniqueBasketId');

        $requestParameters = $requestService->getPayment($params);

        if (\method_exists($this, 'assertStringContainsString')) {
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

    /**
     * @return void
     */
    public function testGetPaymentWithTaxFreeCountry()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
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

    /**
     * @return void
     */
    public function testGetPaymentWithTaxFreeCompaniesWithoutVatId()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
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
        static::assertSame('55.00', $requestParameters['transactions'][0]['amount']['details']['shipping']);
        static::assertSame('59.99', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        static::assertSame('0.00', $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    /**
     * @return void
     */
    public function testGetPaymentWithTaxFreeCompaniesWithVatId()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = 'VATID123';
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = false;

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

    /**
     * @return void
     */
    public function testGetPaymentWithTaxFreeCompaniesWithVatIdShipping()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);
        $requestService = $this->getRequestService($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['countryShipping']['taxfree_ustid'] = '1';
        $userData['additional']['country']['taxfree_ustid'] = '1';
        $userData['shippingaddress']['ustid'] = 'VATID123';

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

    /**
     * @return void
     */
    public function testGetPaymentReturnValidRedirectUrls()
    {
        $requestParameters = $this->getRequestData();

        static::assertNotFalse(\stristr($requestParameters['redirect_urls']['return_url'], 'return'));
        static::assertNotFalse(\stristr($requestParameters['redirect_urls']['cancel_url'], 'cancel'));
    }

    /**
     * @return void
     */
    public function testGetPaymentWithCustomProducts()
    {
        $requestParameters = $this->getRequestData();

        $customProductsOption = $requestParameters['transactions'][0]['item_list']['items'][0];

        // summed up price -> product price and configurations
        static::assertSame(\round(2 * 59.99 + 1 * 1 + 2 * 2 + 1 * 3, 2), (float) $customProductsOption['price']);
    }

    /**
     * @return void
     */
    public function testGetPaymentExpressCheckoutWithoutCart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, false, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        static::assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    /**
     * @return void
     */
    public function testGetPaymentExpressCheckoutWithCart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, true, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        static::assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    /**
     * @return void
     */
    public function testGetPaymentWithoutCart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, false, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_CLASSIC);

        static::assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    /**
     * @return void
     */
    public function testGetPaymentWithCart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, false, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_CLASSIC);

        static::assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    /**
     * @param string $paymentType
     * @param bool   $longBrandName
     *
     * @return array<string, mixed>
     */
    private function getRequestData($paymentType = PaymentType::PAYPAL_CLASSIC, $longBrandName = false)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(
            $paymentType === PaymentType::PAYPAL_PLUS,
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
        $snippetManager = Shopware()->Container()->get('snippets');
        $dependencyProvider = Shopware()->Container()->get('paypal_unified.dependency_provider');
        $priceFormatter = Shopware()->Container()->get('paypal_unified.common.price_formatter');
        $customerHelper = Shopware()->Container()->get('paypal_unified.common.customer_helper');
        $cartHelper = Shopware()->Container()->get('paypal_unified.common.cart_helper');
        $returnUrlHelper = Shopware()->Container()->get('paypal_unified.common.return_url_helper');

        return new PaymentBuilderService(
            $settingService,
            $snippetManager,
            $dependencyProvider,
            $priceFormatter,
            $customerHelper,
            $cartHelper,
            $returnUrlHelper
        );
    }

    /**
     * @return array<string, mixed>
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
     * @return array<string, mixed>
     */
    private function getUserDataAsArray()
    {
        return [
            CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES => true,
            'additional' => [
                'show_net' => true,
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }
}
