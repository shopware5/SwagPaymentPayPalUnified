<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\ExpressCheckoutPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;

class ExpressCheckoutPaymentBuilderServiceTest extends TestCase
{
    public function test_serviceIsAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.express_checkout.payment_builder_service');
        static::assertSame(ExpressCheckoutPaymentBuilderService::class, get_class($service));
    }

    public function test_getPayment_has_currency()
    {
        $request = $this->getRequestData();

        static::assertSame('EUR', $request->getTransactions()->getAmount()->getCurrency());

        foreach ($request->getTransactions()->getItemList()->getItems() as $item) {
            static::assertSame('EUR', $item->getCurrency());
        }
    }

    public function test_getPayment_has_plus_basketId()
    {
        $request = $this->getRequestData();

        static::assertStringEndsWith('basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], $request->getRedirectUrls()->getReturnUrl());
    }

    /**
     * @return Payment
     */
    private function getRequestData()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);

        $ecRequestService = $this->getExpressCheckoutRequestBuilder($settingService);

        $basketData = $this->getBasketDataArray();
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        return $ecRequestService->getPayment($params, 'EUR');
    }

    /**
     * @return ExpressCheckoutPaymentBuilderService
     */
    private function getExpressCheckoutRequestBuilder(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');
        $snippetManager = Shopware()->Container()->get('snippets');
        $dependencyProvider = Shopware()->Container()->get('paypal_unified.dependency_provider');

        return new ExpressCheckoutPaymentBuilderService($router, $settingService, $snippetManager, $dependencyProvider);
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
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }
}
