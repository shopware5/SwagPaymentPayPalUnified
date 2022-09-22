<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerCreateShopwareOrderTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testCreateShopwareOrder()
    {
        $orderNumberService = $this->getContainer()->get('paypal_unified.order_number_service');
        $orderNumber = $orderNumberService->getOrderNumber();

        $session = $this->getContainer()->get('session');
        $session->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $orderNumber);
        $session->offsetSet('sUserId', 1);
        $session->offsetSet('sOrderVariables', [
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
        ]);

        $orderDataServiceMock = $this->createMock(OrderDataService::class);
        $orderDataServiceMock->expects(static::once())->method('applyPaymentTypeAttribute');

        $orderNumberServiceMock = $this->createMock(OrderNumberService::class);
        $orderNumberServiceMock->expects(static::once())->method('releaseOrderNumber');

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_ORDER_DATA_SERVICE => $orderDataServiceMock,
            self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberServiceMock,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'createShopwareOrder');

        $result = $reflectionMethod->invokeArgs(
            $abstractController,
            [$orderNumber, PaymentType::PAYPAL_CLASSIC_V2, Status::PAYMENT_STATE_COMPLETELY_PAID]
        );

        $orderNumberService->releaseOrderNumber();
        static::assertSame($orderNumber, $result);
    }
}
