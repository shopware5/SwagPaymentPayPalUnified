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
use SwagPaymentPayPalUnified\WebhookHandlers\CheckoutPaymentApprovalReversed;

class CheckoutPaymentApprovalReversedTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetEventType()
    {
        static::assertSame(
            WebhookEventTypes::CHECKOUT_PAYMENT_APPROVAL_REVERSED,
            $this->createCheckoutPaymentApprovalReversed()->getEventType()
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
        $this->expectExceptionMessage('SwagPaymentPayPalUnified\WebhookHandlers\CheckoutPaymentApprovalReversed::invoke expect OrderAndPaymentStatusResult, got NULL');

        $this->createCheckoutPaymentApprovalReversed()->invoke($webhook);
    }

    /**
     * @return void
     */
    public function testInvoke()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);

        $webhook = new Webhook();
        $webhook->setResource(['id' => 'unitTestTransactionId']);

        $result = $this->createCheckoutPaymentApprovalReversed()->invoke($webhook);
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
     * @return CheckoutPaymentApprovalReversed
     */
    private function createCheckoutPaymentApprovalReversed()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $modelManager = $this->getContainer()->get('models');

        $logger = $this->createMock(LoggerServiceInterface::class);
        $paymentStatusService = new PaymentStatusService($modelManager, $logger, $connection, $dependencyProvider);
        $orderDataService = $this->getContainer()->get('paypal_unified.order_data_service');

        return new CheckoutPaymentApprovalReversed($logger, $paymentStatusService, $orderDataService);
    }
}
