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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Subscriber\Documents\Invoice;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;

class InvoiceSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;

    const TEST_ORDER_NUMBER = 20001;
    const TEST_AMOUNT_VALUE = 50.5;
    const TEST_DUE_DATE = '01-01-2000';
    const TEST_REFERENCE = 'TEST_REFERENCE_NUMBER';
    const TEST_BANK_IBAN = 'TEST_IBAN';
    const TEST_BANK_BIC = 'TEST_BIC';
    const TEST_BANK_BANK_NAME = 'TEST_BANK';
    const TEST_BANK_ACCOUNT_HOLDER = 'TEST_ACCOUNT_HOLDER';

    public function test_construct()
    {
        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection')
        );
        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents()
    {
        $events = Invoice::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('onBeforeRenderDocument', $events['Shopware_Components_Document::assignValues::after']);
    }

    public function test_onBeforeRenderDocument_returns_when_no_document_was_given()
    {
        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection')
        );
        $hookArgs = new HookArgsWithoutSubject();

        $this->assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_returns_when_wrong_payment_id_was_given()
    {
        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $hookArgs = new HookArgsWithWrongPaymentId();

        $this->assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_returns_when_wrong_payment_type()
    {
        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $this->updateOrderPaymentId(15, $this->getUnifiedPaymentId());
        $hookArgs = new HookArgsWithCorrectPaymentId();

        $this->assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function test_onBeforeRenderDocument_handleDocument()
    {
        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection')
        );

        $this->updateOrderPaymentId(15, $this->getUnifiedPaymentId());
        $this->insertTestData();

        $hookArgs = new HookArgsWithCorrectPaymentId();

        $subscriber->onBeforeRenderDocument($hookArgs);

        /** @var \Enlight_Template_Manager $view */
        $view = $hookArgs->getTemplate();

        $this->assertNotNull($view->getVariable('PayPalUnifiedInvoiceInstruction'));
    }

    private function insertTestData()
    {
        $instructions = new PaymentInstruction();
        $instructions->setDueDate(self::TEST_DUE_DATE);
        $instructions->setReferenceNumber(self::TEST_REFERENCE);

        $testAmount = new Amount();
        $testAmount->setValue(self::TEST_AMOUNT_VALUE);
        $instructions->setAmount($testAmount);

        $testBanking = new RecipientBanking();
        $testBanking->setIban(self::TEST_BANK_IBAN);
        $testBanking->setBic(self::TEST_BANK_BIC);
        $testBanking->setBankName(self::TEST_BANK_BANK_NAME);
        $testBanking->setAccountHolderName(self::TEST_BANK_ACCOUNT_HOLDER);
        $instructions->setRecipientBanking($testBanking);

        $instructionsService = Shopware()->Container()->get('paypal_unified.payment_instruction_service');
        $instructionsService->createInstructions(self::TEST_ORDER_NUMBER, $instructions);

        $sql = "UPDATE s_order_attributes SET paypal_payment_type='PayPalPlusInvoice' WHERE orderID=15";
        $db = Shopware()->Container()->get('dbal_connection');
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
