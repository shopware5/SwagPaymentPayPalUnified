<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\WebhookHandler;

use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleComplete;

class SaleCompleteTest extends \PHPUnit_Framework_TestCase
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
        $instance = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $this->assertInstanceOf(SaleComplete::class, $instance);
    }

    public function test_invoke_returns_true_because_the_order_status_has_been_updated()
    {
        $instance = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $this->assertTrue($instance->invoke($this->getWebhookStruct()));

        $sql = 'SELECT cleared FROM s_order WHERE id=' . self::TEST_ORDER_ID;

        $status = Shopware()->Db()->fetchOne($sql);
        $this->assertEquals(PaymentStatus::PAYMENT_STATUS_APPROVED, $status);
    }

    public function test_invoke_returns_false_because_the_order_does_not_exist()
    {
        $instance = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));

        $this->assertFalse($instance->invoke($this->getWebhookStruct('ORDER_NOT_AVAILABLE')));
    }

    public function test_getEventType_is_correct()
    {
        $instance = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), Shopware()->Container()->get('models'));
        $this->assertEquals(WebhookEventTypes::PAYMENT_SALE_COMPLETED, $instance->getEventType());
    }

    public function test_invoke_will_return_false_without_active_entity_manager()
    {
        $instance = new SaleComplete(Shopware()->Container()->get('paypal_unified.logger_service'), new EntityManagerMock());

        $this->assertFalse($instance->invoke($this->getWebhookStruct(self::TEST_ORDER_ID)));
    }

    /**
     * @param string $id
     *
     * @return Webhook
     */
    private function getWebhookStruct($id = 'TEST_ID')
    {
        return Webhook::fromArray([
            'event_type' => WebhookEventTypes::PAYMENT_SALE_COMPLETED,
            'id' => 1,
            'create_time' => '',
            'resource' => [
                'parent_payment' => $id,
            ],
        ]);
    }
}
