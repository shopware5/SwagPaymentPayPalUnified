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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Validation;

use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class SimpleBasketValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_valid()
    {
        $basketData = ['AmountNumeric' => 14.31];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(14.31);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertTrue($this->getBasketValidator()->validate($basketData, $payment));
    }

    public function test_is_invalid()
    {
        $basketData = ['AmountNumeric' => 14.32];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(14.31);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertFalse($this->getBasketValidator()->validate($basketData, $payment));
    }

    private function getBasketValidator()
    {
        return new SimpleBasketValidator();
    }
}
