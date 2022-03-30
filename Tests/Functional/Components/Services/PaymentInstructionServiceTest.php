<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class PaymentInstructionServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;

    const TEST_ORDER_NUMBER = '20001';
    const TEST_AMOUNT_VALUE = 50.5;
    const TEST_DUE_DATE = '01-01-2000';
    const TEST_REFERENCE = 'TEST_REFERENCE_NUMBER';
    const TEST_BANK_IBAN = 'TEST_IBAN';
    const TEST_BANK_BIC = 'TEST_BIC';
    const TEST_BANK_BANK_NAME = 'TEST_BANK';
    const TEST_BANK_ACCOUNT_HOLDER = 'TEST_ACCOUNT_HOLDER';

    public function testServiceIsAvailable()
    {
        static::assertNotNull(Shopware()->Container()->get('paypal_unified.payment_instruction_service'));
    }

    public function testGetInstruction()
    {
        $instructionsService = Shopware()->Container()->get('paypal_unified.payment_instruction_service');
        $instructionsService->createInstructions(self::TEST_ORDER_NUMBER, $this->getTestInstructions());

        $testInstructions = $instructionsService->getInstructions(self::TEST_ORDER_NUMBER);

        static::assertNotNull($testInstructions);
        static::assertSame(self::TEST_DUE_DATE, $testInstructions->getDueDate());
        static::assertSame(self::TEST_REFERENCE, $testInstructions->getReference());
        static::assertSame(self::TEST_BANK_BANK_NAME, $testInstructions->getBankName());
        static::assertSame(self::TEST_BANK_ACCOUNT_HOLDER, $testInstructions->getAccountHolder());
        static::assertSame(self::TEST_BANK_BIC, $testInstructions->getBic());
        static::assertSame(self::TEST_BANK_IBAN, $testInstructions->getIban());
        static::assertSame((string) self::TEST_AMOUNT_VALUE, $testInstructions->getAmount());

        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();
        $statement = $query->select('internalcomment')
            ->from('s_order')
            ->where('ordernumber = :orderNumber')
            ->setParameter('orderNumber', self::TEST_ORDER_NUMBER)
            ->execute();

        $internalComment = $statement->fetchColumn();

        $expected = '
{"jsonDescription":"Pay Upon Invoice Payment Instructions","orderNumber":"20001","bankName":"TEST_BANK","accountHolder":"TEST_ACCOUNT_HOLDER","iban":"TEST_IBAN","bic":"TEST_BIC","amount":"50.5","dueDate":"01-01-2000","reference":"TEST_REFERENCE_NUMBER"}
';

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString($expected, $internalComment);

            return;
        }
        static::assertContains($expected, $internalComment);
    }

    /**
     * @return PaymentInstruction
     */
    private function getTestInstructions()
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

        return $instructions;
    }
}
