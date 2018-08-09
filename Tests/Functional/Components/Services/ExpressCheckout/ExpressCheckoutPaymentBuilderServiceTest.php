<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\ExpressCheckoutPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\SettingsServicePaymentBuilderServiceMock;

class ExpressCheckoutPaymentBuilderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_serviceIsAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.express_checkout.payment_builder_service');
        $this->assertEquals(ExpressCheckoutPaymentBuilderService::class, get_class($service));
    }

    public function test_getPayment_has_currency()
    {
        $request = $this->getRequestData();

        $this->assertEquals('EUR', $request->getTransactions()->getAmount()->getCurrency());

        foreach ($request->getTransactions()->getItemList()->getItems() as $item) {
            $this->assertEquals('EUR', $item->getCurrency());
        }
    }

    public function test_getPayment_has_plus_basketId()
    {
        $request = $this->getRequestData();

        $this->assertStringEndsWith('basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], $request->getRedirectUrls()->getReturnUrl());
    }

    /**
     * @return Payment
     */
    private function getRequestData()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);

        $ecRequestService = $this->getExpressCheckoutRequestBuilder($settingService);

        $profile = $this->getWebProfile();
        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setWebProfileId($profile->getId());
        $params->setUserData($userData);

        return $ecRequestService->getPayment($params, 'EUR');
    }

    /**
     * @param SettingsServiceInterface $settingService
     *
     * @return ExpressCheckoutPaymentBuilderService
     */
    private function getExpressCheckoutRequestBuilder(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');
        $snippetManager = Shopware()->Container()->get('snippets');

        return new ExpressCheckoutPaymentBuilderService($router, $settingService, $snippetManager);
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
}
