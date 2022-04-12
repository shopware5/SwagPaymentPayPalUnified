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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

/**
 * @deprecated Will be removed in 5.0.0 without replacement
 */
class ExpressCheckoutPaymentBuilderServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    public function testServiceIsAvailable()
    {
        $service = $this->getContainer()->get('paypal_unified.express_checkout.payment_builder_service');
        static::assertInstanceOf(ExpressCheckoutPaymentBuilderService::class, $service);
    }

    public function testGetPaymentHasCurrency()
    {
        $request = $this->getRequestData();

        static::assertSame('EUR', $request->getTransactions()->getAmount()->getCurrency());

        $itemList = $request->getTransactions()->getItemList();
        static::assertInstanceOf(ItemList::class, $itemList);

        foreach ($itemList->getItems() as $item) {
            static::assertSame('EUR', $item->getCurrency());
        }
    }

    public function testGetPaymentHasPlusBasketId()
    {
        $request = $this->getRequestData();

        static::assertStringEndsWith('basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], $request->getRedirectUrls()->getReturnUrl());
    }

    /**
     * @return Payment
     */
    private function getRequestData()
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);

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
        $snippetManager = $this->getContainer()->get('snippets');
        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $priceFormatter = $this->getContainer()->get('paypal_unified.common.price_formatter');
        $customerHelper = $this->getContainer()->get('paypal_unified.common.customer_helper');
        $cartHelper = $this->getContainer()->get('paypal_unified.common.cart_helper');
        $returnUrlHelper = $this->getContainer()->get('paypal_unified.common.return_url_helper');

        return new ExpressCheckoutPaymentBuilderService(
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
