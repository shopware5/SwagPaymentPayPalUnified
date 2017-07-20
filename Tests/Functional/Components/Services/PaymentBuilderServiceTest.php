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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;

class PaymentBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_basket_service_available()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);

        $requestService = $this->getRequestService($settingService);

        $this->assertNotNull($requestService);
    }

    public function test_getPayment_return_plus_intent()
    {
        $requestParameters = $this->getRequestData(true, 1);

        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_sale_intent_without_plus()
    {
        $requestParameters = $this->getRequestData();

        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_return_authorize_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(false, 1);

        $this->assertEquals('authorize', $requestParameters['intent']);
    }

    public function test_getPayment_return_order_intent_without_plus()
    {
        $requestParameters = $this->getRequestData(false, 2);

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

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
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

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);
        $params->setBasketUniqueId('MyUniqueBasketId');

        $requestParameters = $requestService->getPayment($params);

        $this->assertStringEndsWith('basketId/MyUniqueBasketId', $requestParameters->getRedirectUrls()->getReturnUrl());
    }

    public function test_getPayment_with_tax_tree_country()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);
        $requestService = $this->getRequestService($settingService);

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['country']['taxfree'] = '1';

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
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

        $customProductsOption = $requestParameters['transactions'][0]['item_list']['items'][1];

        $this->assertEquals('test: a, b', $customProductsOption['name']);
        $this->assertEquals(6, $customProductsOption['price']);
    }

    public function test_getPayment_express_checkout_without_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, false);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->assertEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    public function test_getPayment_express_checkout_with_cart()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0, true);
        $requestService = $this->getRequestService($settingService);

        $params = new PaymentBuilderParameters();
        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);
        $params->setPaymentType(PaymentType::PAYPAL_EXPRESS);

        $this->assertNotEmpty($requestService->getPayment($params)->getTransactions()->getItemList());
    }

    /**
     * @param $plusActive
     * @param $intent
     *
     * @return array
     */
    private function getRequestData($plusActive = false, $intent = 0)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock($plusActive, $intent);
        $requestService = $this->getRequestService($settingService);

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);

        return $requestService->getPayment($params)->toArray();
    }

    /**
     * @return WebProfile
     */
    private function getWebProfile()
    {
        $shop = Shopware()->Shop();

        $webProfile = new WebProfile();
        $webProfile->setName($shop->getId() . $shop->getHost() . $shop->getBasePath());
        $webProfile->setTemporary(false);

        $presentation = new WebProfilePresentation();
        $presentation->setLocaleCode($shop->getLocale()->getLocale());
        $presentation->setLogoImage(null);
        $presentation->setBrandName('Test brand name');

        $flowConfig = new WebProfileFlowConfig();
        $flowConfig->setReturnUriHttpMethod('POST');
        $flowConfig->setUserAction('Commit');

        $inputFields = new WebProfileInputFields();
        $inputFields->setAddressOverride('1');
        $inputFields->setAllowNote(false);
        $inputFields->setNoShipping(0);

        $webProfile->setFlowConfig($flowConfig);
        $webProfile->setInputFields($inputFields);
        $webProfile->setPresentation($presentation);

        return $webProfile;
    }

    /**
     * @param SettingsServiceInterface $settingService
     *
     * @return PaymentBuilderService
     */
    private function getRequestService(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');

        return new PaymentBuilderService($router, $settingService);
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
                    'quantity' => '1',
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
                    'quantity' => '1',
                    'price' => '2',
                    'customProductMode' => '3',
                ], [
                    'articlename' => 'b',
                    'quantity' => '1',
                    'price' => '3',
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
                'country' => [
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
        if ($column == 'active' && $settingsTable == SettingsTable::PLUS) {
            return $this->plus_active;
        }

        if ($column == 'payment_intent') {
            return $this->paypal_payment_intent;
        }

        if ($column == 'submit_cart' && $settingsTable == SettingsTable::EXPRESS_CHECKOUT) {
            return $this->ec_submit_cart;
        }

        return $this->$column;
    }

    public function hasSettings($settingsTable = SettingsTable::GENERAL)
    {
    }
}
