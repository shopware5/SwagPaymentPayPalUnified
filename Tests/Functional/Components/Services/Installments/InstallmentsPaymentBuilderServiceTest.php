<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $requestParameters = $this->getRequestData(true);
        $this->assertEquals('sale', $requestParameters['intent']);
    }

    public function test_getPayment_has_correct_intent_order()
    {
        $requestParameters = $this->getRequestData(true, 2);
        $this->assertEquals('order', $requestParameters['intent']);
    }

    public function test_getPayment_returns_url_with_basket_id()
    {
        $requestParameters = $this->getRequestData(true, 2, true);
        $returnUrl = $requestParameters['redirect_urls']['return_url'];

        $this->assertStringEndsWith('PaypalUnifiedInstallments/return/basketId/test-test-test', $returnUrl);
    }

    /**
     * @param bool $plusActive
     * @param int  $intent
     * @param bool $withBasketId
     *
     * @return array
     */
    private function getRequestData($plusActive = false, $intent = 0, $withBasketId = false)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock($plusActive, $intent);

        $installmentsPaymentBuilderService = $this->getInstallmentsPaymentBuilderService($settingService);

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);

        if ($withBasketId) {
            $params->setBasketUniqueId('test-test-test');
        }

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
                [
                    'ordernumber' => 'SW10137',
                    'articlename' => 'Fahrerbrille Chronos',
                    'quantity' => '1',
                    'price' => '59,99',
                    'netprice' => '50.411764705882',
                    'sCurrencyName' => 'EUR',
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
        $snippetManager = Shopware()->Container()->get('snippets');

        return new InstallmentsPaymentBuilderService($router, $settingService, $snippetManager);
    }
}
