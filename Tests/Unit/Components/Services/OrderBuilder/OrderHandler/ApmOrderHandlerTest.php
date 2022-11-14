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
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\ApmOrderHandler;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceFactory;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberBuilder;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;

class ApmOrderHandlerTest extends TestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;

    /**
     * @dataProvider createPurchaseUnitsWithoutSubmitCartDataProvider
     *
     * @param string $expectedAmount
     *
     * @return void
     */
    public function testCreatePurchaseUnitsWithoutSubmitCart(PayPalOrderParameter $orderParameter, $expectedAmount)
    {
        $priceFormatter = new PriceFormatter();
        $customerHelper = new CustomerHelper();
        $cartHelper = new CartHelper($customerHelper, $priceFormatter);
        $amountProvider = new AmountProvider($cartHelper, $customerHelper, $priceFormatter);

        $apmOrderHandler = $this->createApmOrderHandler(
            null,
            null,
            $amountProvider,
            null,
            null,
            null,
            new PriceFormatter(),
            new CustomerHelper()
        );

        $reflectionMethod = $this->getReflectionMethod(ApmOrderHandler::class, 'createPurchaseUnits');

        $result = $reflectionMethod->invoke($apmOrderHandler, $orderParameter);
        $purchaseUnitResult = $result[0];

        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnitResult);

        static::assertSame($expectedAmount, $purchaseUnitResult->getAmount()->getValue());
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function createPurchaseUnitsWithoutSubmitCartDataProvider()
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
                ['AmountWithTaxNumeric' => '199.99', 'sCurrencyName' => 'EUR'],
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
     * @return void
     */
    public function testCreatePurchaseUnitsWithSubmitCart()
    {
        $settingsService = $this->createMock(SettingsServiceInterface::class);
        $settingsService->expects(static::once())->method('get')->willReturn(true);

        $apmOrderHandler = $this->createApmOrderHandler(
            $settingsService,
            $this->getContainer()->get('paypal_unified.paypal_order.item_list_provider'),
            null,
            null,
            null,
            null,
            new PriceFormatter(),
            new CustomerHelper()
        );

        $userData = require __DIR__ . '/../../../../../_fixtures/s_user_data.php';
        $basket = require __DIR__ . '/_fixtures/basket.php';

        $paypalOrderParameter = new PayPalOrderParameter(
            $userData['sUserData'],
            $basket,
            PaymentType::APM_GIROPAY,
            null,
            null,
            'anyOrderId'
        );

        $reflectionMethod = $this->getReflectionMethod(ApmOrderHandler::class, 'createPurchaseUnits');

        $result = $reflectionMethod->invoke($apmOrderHandler, $paypalOrderParameter);
        $purchaseUnitResult = $result[0];

        static::assertInstanceOf(PurchaseUnit::class, $purchaseUnitResult);
        static::assertTrue(\is_array($purchaseUnitResult->getItems()));
        static::assertCount(1, $purchaseUnitResult->getItems());
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
        $extendedCustomer = require __DIR__ . '/../../../../../_fixtures/s_user_data.php';
        $customer = array_merge($extendedCustomer['sUserData'], $customer);

        $extendedCart = require __DIR__ . '/_fixtures/basket.php';
        $cart = array_merge($extendedCart, $cart);

        return new PayPalOrderParameter(
            $customer,
            $cart,
            $paymentType,
            $basketUniqueId,
            $paymentToken,
            'anyOrderId'
        );
    }

    /**
     * @return ApmOrderHandler
     */
    private function createApmOrderHandler(
        SettingsServiceInterface $settingsService = null,
        ItemListProvider $itemListProvider = null,
        AmountProvider $amountProvider = null,
        ReturnUrlHelper $returnUrlHelper = null,
        ContextServiceInterface $contextService = null,
        PhoneNumberBuilder $phoneNumberBuilder = null,
        PriceFormatter $priceFormatter = null,
        CustomerHelper $customerHelper = null,
        PaymentSourceFactory $paymentSourceFactory = null
    ) {
        $settingsService = $settingsService === null ? $this->createMock(SettingsServiceInterface::class) : $settingsService;
        $itemListProvider = $itemListProvider === null ? $this->createMock(ItemListProvider::class) : $itemListProvider;
        $amountProvider = $amountProvider === null ? $this->createMock(AmountProvider::class) : $amountProvider;
        $returnUrlHelper = $returnUrlHelper === null ? $this->createMock(ReturnUrlHelper::class) : $returnUrlHelper;
        $contextService = $contextService === null ? $this->createMock(ContextService::class) : $contextService;
        $phoneNumberBuilder = $phoneNumberBuilder === null ? $this->createMock(PhoneNumberBuilder::class) : $phoneNumberBuilder;
        $priceFormatter = $priceFormatter === null ? $this->createMock(PriceFormatter::class) : $priceFormatter;
        $customerHelper = $customerHelper === null ? $this->createMock(CustomerHelper::class) : $customerHelper;
        $paymentSourceFactory = $paymentSourceFactory === null ? $this->createMock(PaymentSourceFactory::class) : $paymentSourceFactory;

        return new ApmOrderHandler(
            $settingsService,
            $itemListProvider,
            $amountProvider,
            $returnUrlHelper,
            $contextService,
            $phoneNumberBuilder,
            $priceFormatter,
            $customerHelper,
            $paymentSourceFactory
        );
    }
}
