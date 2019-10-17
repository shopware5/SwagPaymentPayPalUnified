<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Installments\InstallmentsPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;

class InstallmentsPaymentBuilderServiceTest extends TestCase
{
    public function test_serviceIsAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.installments.payment_builder_service');
        static::assertSame(InstallmentsPaymentBuilderService::class, get_class($service));
    }

    public function test_getPayment_has_correct_intent_order_fallback()
    {
        $requestParameters = $this->getRequestData(true, 1);
        static::assertSame('order', $requestParameters['intent']);
    }

    public function test_getPayment_has_correct_intent_sale()
    {
        $requestParameters = $this->getRequestData(true);
        static::assertSame('sale', $requestParameters['intent']);
    }

    public function test_getPayment_has_correct_intent_order()
    {
        $requestParameters = $this->getRequestData(true, 2);
        static::assertSame('order', $requestParameters['intent']);
    }

    public function test_getPayment_returns_url_with_basket_id()
    {
        $requestParameters = $this->getRequestData(true, 2, true);
        $returnUrl = $requestParameters['redirect_urls']['return_url'];

        static::assertContains('PaypalUnifiedInstallments/return/basketId/test-test-test', $returnUrl);
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

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
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
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }

    /**
     * @return PaymentBuilderService
     */
    private function getInstallmentsPaymentBuilderService(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');
        $snippetManager = Shopware()->Container()->get('snippets');
        $dependencyProvider = Shopware()->Container()->get('paypal_unified.dependency_provider');

        return new InstallmentsPaymentBuilderService($router, $settingService, $snippetManager, $dependencyProvider);
    }
}
