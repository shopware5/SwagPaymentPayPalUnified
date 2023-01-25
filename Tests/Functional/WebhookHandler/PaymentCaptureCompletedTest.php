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
use SwagPaymentPayPalUnified\WebhookHandlers\PaymentCaptureCompleted;

class PaymentCaptureCompletedTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetEventType()
    {
        static::assertSame(
            WebhookEventTypes::PAYMENT_CAPTURE_COMPLETED,
            $this->createPaymentCaptureCompleted()->getEventType()
        );
    }

    /**
     * @return void
     */
    public function testInvokeShouldReturnFalseBecauseThereIsNoOrderServiceResult()
    {
        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('SwagPaymentPayPalUnified\WebhookHandlers\PaymentCaptureCompleted::invoke expect OrderAndPaymentStatusResult, got NULL');

        $this->createPaymentCaptureCompleted()->invoke($webhook);
    }

    /**
     * @return void
     */
    public function testInvokeShouldReturnTrueAndUpdateOrderAndPaymentStatus()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);
        $this->updatePaymentStatus();

        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $result = $this->createPaymentCaptureCompleted()->invoke($webhook);

        static::assertTrue($result);
        static::assertSame(Status::PAYMENT_STATE_COMPLETELY_PAID, $this->getPaymentStatus());
    }

    /**
     * @return PaymentCaptureCompleted
     */
    private function createPaymentCaptureCompleted()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $modelManager = $this->getContainer()->get('models');

        $logger = $this->createMock(LoggerServiceInterface::class);
        $paymentStatusService = new PaymentStatusService($modelManager, $logger, $connection, $dependencyProvider);
        $orderDataService = $this->getContainer()->get('paypal_unified.order_data_service');

        return new PaymentCaptureCompleted($logger, $paymentStatusService, $orderDataService);
    }

    /**
     * @return void
     */
    private function updatePaymentStatus()
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->update('s_order')
            ->set('cleared', ':newPaymentStatus')
            ->where('transactionID = :transactionId')
            ->setParameter('newPaymentStatus', Status::PAYMENT_STATE_OPEN)
            ->setParameter('transactionId', 'unitTestTransactionId')
            ->execute();

        static::assertSame(Status::PAYMENT_STATE_OPEN, $this->getPaymentStatus());
    }

    /**
     * @return int
     */
    private function getPaymentStatus()
    {
        return (int) $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['cleared'])
            ->from('s_order')
            ->where('transactionID = :transactionId')
            ->setParameter('transactionId', 'unitTestTransactionId')
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
    }
}
