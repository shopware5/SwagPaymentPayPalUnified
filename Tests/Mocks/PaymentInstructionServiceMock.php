<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Models\PaymentInstruction;

class PaymentInstructionServiceMock extends PaymentInstructionService
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getInstructions($orderNumber)
    {
        if (!$orderNumber) {
            return null;
        }

        $instructions = new PaymentInstruction();
        $instructions->setAccountHolder('testAccountHolder');
        $instructions->setAmount(200.50);
        $instructions->setBankName('testBankName');
        $instructions->setBic('testBic');
        $instructions->setDueDate('2100-04-04');
        $instructions->setIban('testIban');
        $instructions->setOrderNumber($orderNumber);
        $instructions->setReference('testReference');

        return $instructions;
    }
}
