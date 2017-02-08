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

use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;

class BasketServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_basket_service_available()
    {
        $basketService = $this->getBasketService();

        $this->assertNotNull($basketService);
    }

    public function test_get_request_parameters_return_valid_intent()
    {
        $requestParameters = $this->getRequestData();

        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_get_request_parameters_return_valid_payer()
    {
        $requestParameters = $this->getRequestData();

        $this->assertEquals('paypal', $requestParameters['payer']['payment_method']);
    }

    public function test_get_request_parameters_return_valid_transactions()
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

    public function test_get_request_parameters_with_show_gross()
    {
        $basketService = $this->getBasketService();

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['show_net'] = false;

        $requestParameters = $basketService->getRequestParameters($profile, $basketData, $userData);

        $this->assertEquals('136.84', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertEquals(18.36, $requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_get_parameters_with_tax_tree_country()
    {
        $basketService = $this->getBasketService();

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $userData['additional']['country']['taxfree'] = '1';

        $requestParameters = $basketService->getRequestParameters($profile, $basketData, $userData);

        $this->assertEquals('96.63', $requestParameters['transactions'][0]['amount']['total']);
        $this->assertEquals(46.22, $requestParameters['transactions'][0]['amount']['details']['shipping']);
        $this->assertEquals('50.41', $requestParameters['transactions'][0]['amount']['details']['subtotal']);
        $this->assertNull($requestParameters['transactions'][0]['amount']['details']['tax']);
    }

    public function test_get_request_parameters_return_valid_redirect_urls()
    {
        $requestParameters = $this->getRequestData();

        $this->assertNotFalse(stristr($requestParameters['redirect_urls']['return_url'], 'return'));
        $this->assertNotFalse(stristr($requestParameters['redirect_urls']['cancel_url'], 'cancel'));
    }

    private function getRequestData()
    {
        $basketService = $this->getBasketService();

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        return $basketService->getRequestParameters($profile, $basketData, $userData);
    }

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

    private function getBasketService()
    {
        return Shopware()->Container()->get('paypal_unified.basket_service');
    }

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
                'ordernumber' => 'SW10137',
                'articlename' => 'Fahrerbrille Chronos',
                'quantity' => '1',
                'price' => '59,99',
                'netprice' => '50.411764705882',
                'sCurrencyName' => 'EUR',
            ],
        ];
    }

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
