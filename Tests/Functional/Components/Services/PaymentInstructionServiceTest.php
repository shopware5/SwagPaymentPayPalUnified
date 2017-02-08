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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class PaymentInstructionServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    const TEST_ORDER_NUMBER = 20001;
    const TEST_AMOUNT_VALUE = 50.5;
    const TEST_DUE_DATE = '01-01-2000';
    const TEST_REFERENCE = 'TEST_REFERENCE_NUMBER';
    const TEST_BANK_IBAN = 'TEST_IBAN';
    const TEST_BANK_BIC = 'TEST_BIC';
    const TEST_BANK_BANK_NAME = 'TEST_BANK';
    const TEST_BANK_ACCOUNT_HOLDER = 'TEST_ACCOUNT_HOLDER';

    public function test_service_is_available()
    {
        $this->assertNotNull(Shopware()->Container()->get('paypal_unified.payment_instruction_service'));
    }

    public function test_getInstruction()
    {
        /** @var PaymentInstructionService $instructionsService */
        $instructionsService = Shopware()->Container()->get('paypal_unified.payment_instruction_service');
        $instructionsService->createInstructions(self::TEST_ORDER_NUMBER, $this->getTestInstructions());

        $testInstructions = $instructionsService->getInstructions(self::TEST_ORDER_NUMBER);

        $this->assertNotNull($testInstructions);
        $this->assertEquals(self::TEST_DUE_DATE, $testInstructions->getDueDate());
        $this->assertEquals(self::TEST_REFERENCE, $testInstructions->getReference());
        $this->assertEquals(self::TEST_BANK_BANK_NAME, $testInstructions->getBankName());
        $this->assertEquals(self::TEST_BANK_ACCOUNT_HOLDER, $testInstructions->getAccountHolder());
        $this->assertEquals(self::TEST_BANK_BIC, $testInstructions->getBic());
        $this->assertEquals(self::TEST_BANK_IBAN, $testInstructions->getIban());
        $this->assertEquals(self::TEST_BANK_IBAN, $testInstructions->getIban());
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
