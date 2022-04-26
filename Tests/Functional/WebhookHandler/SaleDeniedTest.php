<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use PHPUnit\Framework\TestCase;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleDenied;

class SaleDeniedTest extends TestCase
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
        $instance = new SaleDenied(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertInstanceOf(SaleDenied::class, $instance);
    }

    public function testInvokeReturnsTrueBecauseTheOrderStatusHasBeenUpdated()
    {
        $instance = new SaleDenied(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertTrue($instance->invoke($this->getWebhookStruct()));

        $sql = 'SELECT cleared FROM s_order WHERE id=' . self::TEST_ORDER_ID;

        $status = (int) Shopware()->Db()->fetchOne($sql);
        static::assertSame(Status::PAYMENT_STATE_OPEN, $status);
    }

    public function testInvokeReturnsFalseBecauseTheOrderDoesNotExist()
    {
        $instance = new SaleDenied(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );

        static::assertFalse($instance->invoke($this->getWebhookStruct('ORDER_NOT_AVAILABLE')));
    }

    public function testGetEventTypeIsCorrect()
    {
        $instance = new SaleDenied(
            $this->getContainer()->get('paypal_unified.logger_service'),
            $this->getContainer()->get('paypal_unified.payment_status_service')
        );
        static::assertSame(WebhookEventTypes::PAYMENT_SALE_DENIED, $instance->getEventType());
    }

    public function testInvokeWillReturnFalseWithoutActiveEntityManager()
    {
        $instance = new SaleDenied(
            $this->getContainer()->get('paypal_unified.logger_service'),
            new PaymentStatusService(
                new EntityManagerMock(),
                $this->getContainer()->get('paypal_unified.logger_service'),
                $this->getContainer()->get('dbal_connection'),
                $this->getContainer()->get('paypal_unified.settings_service')
            )
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
