<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleRefunded;

class SaleRefundedTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    const TEST_ORDER_ID = '15';

    /**
     * @before
     */
    public function setOrderTransacactionId()
    {
        $sql = "UPDATE s_order SET temporaryID = 'TEST_ID' WHERE id=" . self::TEST_ORDER_ID;

        Shopware()->Db()->executeUpdate($sql);
    }

    public function testCanConstruct()
    {
        $instance = new SaleRefunded(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertInstanceOf(SaleRefunded::class, $instance);
    }

    public function testInvokeReturnsTrueBecauseTheOrderStatusHasBeenUpdated()
    {
        $instance = new SaleRefunded(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertTrue($instance->invoke($this->getWebhookStruct()));

        $sql = 'SELECT cleared FROM s_order WHERE id=' . self::TEST_ORDER_ID;

        $status = (int) Shopware()->Db()->fetchOne($sql);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_REFUNDED, $status);
    }

    public function testInvokeReturnsFalseBecauseTheOrderDoesNotExist()
    {
        $instance = new SaleRefunded(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertFalse($instance->invoke($this->getWebhookStruct('ORDER_NOT_AVAILABLE')));
    }

    public function testGetEventTypeIsCorrect()
    {
        $instance = new SaleRefunded(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );
        static::assertSame(WebhookEventTypes::PAYMENT_SALE_REFUNDED, $instance->getEventType());
    }

    public function testInvokeWillReturnFalseWithoutActiveEntityManager()
    {
        $instance = new SaleRefunded(
            $this->getContainer()->get('paypal_unified.logger_service'),
            new PaymentStatusService(new EntityManagerMock(), $this->getContainer()->get('paypal_unified.logger_service'))
        );

        static::assertFalse($instance->invoke($this->getWebhookStruct(self::TEST_ORDER_ID)));
    }

    /**
     * @param string $id
     *
     * @return Webhook
     */
    private function getWebhookStruct($id = 'TEST_ID')
    {
        return Webhook::fromArray([
            'event_type' => WebhookEventTypes::PAYMENT_SALE_REFUNDED,
            'id' => 1,
            'create_time' => '',
            'resource' => [
                'parent_payment' => $id,
            ],
        ]);
    }
}
