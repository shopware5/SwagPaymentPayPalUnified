<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\OrderBuilder\OrderHandler;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\ApmOrderHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;

class ApmOrderHandlerTest extends TestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider createPurchaseUnitsTestDataProvider
     *
     * @param string $expectedAmount
     *
     * @return void
     */
    public function testCreatePurchaseUnits(PayPalOrderParameter $orderParameter, $expectedAmount)
    {
        $apmOrderHandler = $this->createApmOrderHandler(null, null, new PriceFormatter(), null, new CustomerHelper());
        $reflectionMethod = $this->getReflectionMethod(ApmOrderHandler::class, 'createPurchaseUnits');

        $result = $reflectionMethod->invoke($apmOrderHandler, $orderParameter);
        $purchaseUnitResult = $result[0];

        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnitResult);

        static::assertSame($expectedAmount, $purchaseUnitResult->getAmount()->getValue());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createPurchaseUnitsTestDataProvider()
    {
        yield 'User charge vat and use net prices' => [
            $this->createPayPalOrderParameter(
                ['additional' => ['charge_vat' => true, 'show_net' => true]],
                ['AmountNumeric' => '99.99', 'sCurrencyName' => 'EUR'],
                PaymentType::APM_SOFORT,
                'AnyBasketUniqueId',
                'AnyPaymentToke'
            ),
            '99.99',
        ];

        yield 'User charge vat and use gross prices' => [
            $this->createPayPalOrderParameter(
                ['additional' => ['charge_vat' => true, 'show_net' => false]],
                ['sAmountWithTax' => '199.99', 'sCurrencyName' => 'EUR'],
                PaymentType::APM_SOFORT,
                'AnyBasketUniqueId',
                'AnyPaymentToke'
            ),
            '199.99',
        ];

        yield 'User dont charge vat' => [
            $this->createPayPalOrderParameter(
                ['additional' => ['charge_vat' => false, 'show_net' => false]],
                ['AmountNetNumeric' => '299.99', 'sCurrencyName' => 'EUR'],
                PaymentType::APM_SOFORT,
                'AnyBasketUniqueId',
                'AnyPaymentToke'
            ),
            '299.99',
        ];
    }

    /**
     * @param array<string,mixed> $customer
     * @param array<string,mixed> $cart
     * @param PaymentType::*      $paymentType
     * @param string              $basketUniqueId
     * @param string              $paymentToken
     *
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter(array $customer, array $cart, $paymentType, $basketUniqueId, $paymentToken)
    {
        return new PayPalOrderParameter(
            $customer,
            $cart,
            $paymentType,
            $basketUniqueId,
            $paymentToken
        );
    }

    /**
     * @return ApmOrderHandler
     */
    private function createApmOrderHandler(
        ContextServiceInterface $contextService = null,
        ReturnUrlHelper $returnUrlHelper = null,
        PriceFormatter $priceFormatter = null,
        PaymentSourceFactory $paymentSourceFactory = null,
        CustomerHelper $customerHelper = null
    ) {
        $contextService = $contextService === null ? $this->createMock(ContextService::class) : $contextService;
        $returnUrlHelper = $returnUrlHelper === null ? $this->createMock(ReturnUrlHelper::class) : $returnUrlHelper;
        $priceFormatter = $priceFormatter === null ? $this->createMock(PriceFormatter::class) : $priceFormatter;
        $paymentSourceFactory = $paymentSourceFactory === null ? $this->createMock(PaymentSourceFactory::class) : $paymentSourceFactory;
        $customerHelper = $customerHelper === null ? $this->createMock(CustomerHelper::class) : $customerHelper;

        return new ApmOrderHandler($contextService, $returnUrlHelper, $priceFormatter, $paymentSourceFactory, $customerHelper);
    }
}
