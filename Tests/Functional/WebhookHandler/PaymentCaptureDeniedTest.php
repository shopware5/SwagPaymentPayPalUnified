<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use PDO;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\PaymentCaptureDenied;

class PaymentCaptureDeniedTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetEventType()
    {
        static::assertSame(
            WebhookEventTypes::PAYMENT_CAPTURE_DENIED,
            $this->createPaymentCaptureDenied()->getEventType()
        );
    }

    /**
     * @return void
     */
    public function testInvokeShouldThrowExceptionBecauseThereIsNoOrderServiceResult()
    {
        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('SwagPaymentPayPalUnified\WebhookHandlers\PaymentCaptureDenied::invoke expect OrderAndPaymentStatusResult, got NULL');

        $this->createPaymentCaptureDenied()->invoke($webhook);
    }

    /**
     * @return void
     */
    public function testInvokeShouldReturnTrueAndUpdateOrderAndPaymentStatus()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $result = $this->createPaymentCaptureDenied()->invoke($webhook);
        $databaseResult = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['status as orderStatus', 'cleared as paymentStatus'])
            ->from('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', 'unitTestTransactionId')
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        static::assertTrue($result);
        static::assertSame(Status::ORDER_STATE_CLARIFICATION_REQUIRED, (int) $databaseResult['orderStatus']);
        static::assertSame(Status::PAYMENT_STATE_OPEN, (int) $databaseResult['paymentStatus']);
    }

    /**
     * @return PaymentCaptureDenied
     */
    private function createPaymentCaptureDenied()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $modelManager = $this->getContainer()->get('models');

        $logger = $this->createMock(LoggerServiceInterface::class);
        $paymentStatusService = new PaymentStatusService($modelManager, $logger, $connection, $dependencyProvider);
        $orderDataService = $this->getContainer()->get('paypal_unified.order_data_service');

        return new PaymentCaptureDenied($logger, $paymentStatusService, $orderDataService);
    }
}
