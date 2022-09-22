<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerGetOrderIdTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetOrderIdShouldThrowException()
    {
        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_DBAL_CONNECTION => $this->getContainer()->get('dbal_connection'),
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'getOrderId');

        static::expectException(OrderNotFoundException::class);
        static::expectExceptionMessage('Could not find order with search parameter "Order number" and value "anyNotExistentOrderNumber"');

        $reflectionMethod->invoke($abstractController, 'anyNotExistentOrderNumber');
    }

    /**
     * @return void
     */
    public function testGetOrderId()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/_fixtures/get_order_id.sql');
        static::assertTrue(\is_string($sql));
        $connection->exec($sql);

        $abstractController = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_DBAL_CONNECTION => $connection,
        ]);

        $reflectionMethod = $this->getReflectionMethod(AbstractPaypalPaymentController::class, 'getOrderId');

        $result = $reflectionMethod->invoke($abstractController, '111999547666');

        static::assertSame(999547666, $result);
    }
}
