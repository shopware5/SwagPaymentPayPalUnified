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
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleDenied;

class SaleDeniedTest extends TestCase
{
    use DatabaseTestCaseTrait;

    const TEST_ORDER_ID = 15;

    /**
     * @before
     */
    public function setOrderTransacactionId()
    {
        $sql = "UPDATE s_order SET temporaryID = 'TEST_ID' WHERE id=" . self::TEST_ORDER_ID;

        Shopware()->Db()->executeUpdate($sql);
    }

    public function test_can_construct()
    {
        $instance = new SaleDenied(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        static::assertInstanceOf(SaleDenied::class, $instance);
    }

    public function test_invoke_returns_true_because_the_order_status_has_been_updated()
    {
        $instance = new SaleDenied(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        static::assertTrue($instance->invoke($this->getWebhookStruct()));

        $sql = 'SELECT cleared FROM s_order WHERE id=' . self::TEST_ORDER_ID;

        $status = (int) Shopware()->Db()->fetchOne($sql);
        static::assertSame(PaymentStatus::PAYMENT_STATUS_OPEN, $status);
    }

    public function test_invoke_returns_false_because_the_order_does_not_exist()
    {
        $instance = new SaleDenied(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );

        static::assertFalse($instance->invoke($this->getWebhookStruct('ORDER_NOT_AVAILABLE')));
    }

    public function test_getEventType_is_correct()
    {
        $instance = new SaleDenied(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            Shopware()->Container()->get('paypal_unified.payment_status_service')
        );
        static::assertSame(WebhookEventTypes::PAYMENT_SALE_DENIED, $instance->getEventType());
    }

    public function test_invoke_will_return_false_without_active_entity_manager()
    {
        $instance = new SaleDenied(
            Shopware()->Container()->get('paypal_unified.logger_service'),
            new PaymentStatusService(new EntityManagerMock())
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
            'event_type' => WebhookEventTypes::PAYMENT_SALE_DENIED,
            'id' => 1,
            'create_time' => '',
            'resource' => [
                'parent_payment' => $id,
            ],
        ]);
    }
}
