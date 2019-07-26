<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\Documents\Installments;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithCorrectPaymentId;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithoutSubject;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithWrongPaymentId;

class InstallmentsSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;

    public function test_construct()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Installments::getSubscribedEvents();

        static::assertCount(1, $events);
        static::assertSame('onBeforeRenderDocument', $events['Shopware_Components_Document::assignValues::after']);
    }

    public function test_onBeforeRenderDocument_returns_when_no_document_was_given()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $hookArgs = new HookArgsWithoutSubject(Shopware()->Container()->has('shopware.benchmark_bundle.collector'));

        static::assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_returns_when_wrong_payment_id_was_given()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $hookArgs = new HookArgsWithWrongPaymentId(Shopware()->Container()->has('shopware.benchmark_bundle.collector'));

        static::assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_handleDocument()
    {
        $subscriber = new Installments(
            Shopware()->Container()->get('paypal_unified.installments.order_credit_info_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $this->updateOrderPaymentId(15, $this->getInstallmentsPaymentId());
        $this->insertTestData();

        $hookArgs = new HookArgsWithCorrectPaymentId(Shopware()->Container()->has('shopware.benchmark_bundle.collector'));

        $subscriber->onBeforeRenderDocument($hookArgs);
        $view = $hookArgs->getView();

        static::assertNotNull($view->getVariable('paypalInstallmentsCredit')->value);
    }

    private function insertTestData()
    {
        /** @var Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "INSERT INTO `swag_payment_paypal_unified_financing_information` (`payment_id`, `fee_amount`, `total_cost`, `term`, `monthly_payment`)
                VALUES ('TEST_PAYMENT_ID', 10.01, 1400.04, 12, 67.68);";
        $db->executeUpdate($sql);

        $sql = "UPDATE s_order SET temporaryID='TEST_PAYMENT_ID' WHERE id=15";
        $db->executeUpdate($sql);
    }

    /**
     * @param int $orderId
     * @param int $paymentId
     */
    private function updateOrderPaymentId($orderId, $paymentId)
    {
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = 'UPDATE s_order SET paymentID=:paymentId WHERE id=:orderId';
        $db->executeUpdate($sql, [
            ':paymentId' => $paymentId,
            ':orderId' => $orderId,
        ]);
    }
}
