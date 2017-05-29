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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Installments\InstallmentsPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\SettingsServicePaymentBuilderServiceMock;

class InstallmentsPaymentBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_serviceIsAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.installments.payment_builder_service');
        $this->assertEquals(InstallmentsPaymentBuilderService::class, get_class($service));
    }

    public function test_getPayment_has_correct_intent_order_fallback()
    {
        $requestParameters = $this->getRequestData(true, 1);
        $this->assertEquals('order', $requestParameters['intent']);
    }

    public function test_getPayment_has_correct_intent_sale()
    {
        $requestParameters = $this->getRequestData(true, 0);
        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_has_correct_intent_order()
    {
        $requestParameters = $this->getRequestData(true, 2);
        $this->assertEquals('order', $requestParameters['intent']);
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

        $installmentsPaymentBuilderService = $this->getInstallmentsPaymentBuilderService($settingService);

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfile($profile);
        $params->setUserData($userData);

        return $installmentsPaymentBuilderService->getPayment($params)->toArray();
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
                'ordernumber' => 'SW10137',
                'articlename' => 'Fahrerbrille Chronos',
                'quantity' => '1',
                'price' => '59,99',
                'netprice' => '50.411764705882',
                'sCurrencyName' => 'EUR',
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
    private function getInstallmentsPaymentBuilderService(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');

        return new InstallmentsPaymentBuilderService($router, $settingService);
    }
}
