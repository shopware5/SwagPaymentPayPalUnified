<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Validation;

use SwagPaymentPayPalUnified\Components\Services\Validation\SimpleBasketValidator;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class SimpleBasketValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_valid()
    {
        $basketData = ['AmountNumeric' => 14.31];
        $userData = [];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(14.31);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertTrue($this->getBasketValidator()->validate($basketData, $userData, $payment));
    }

    public function test_is_invalid()
    {
        $basketData = ['AmountNumeric' => 14.32];
        $userData = [];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(14.31);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertFalse($this->getBasketValidator()->validate($basketData, $userData, $payment));
    }

    public function test_is_valid_with_charge_vat()
    {
        $basketData = ['AmountNumeric' => 14.31, 'AmountWithTaxNumeric' => 17.03];
        $userData = ['additional' => ['charge_vat' => true]];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(17.03);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertTrue($this->getBasketValidator()->validate($basketData, $userData, $payment));
    }

    public function test_is_invalid_with_charge_vat()
    {
        $basketData = ['AmountNumeric' => 14.31, 'AmountWithTaxNumeric' => 17.03];
        $userData = ['additional' => ['charge_vat' => true]];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal(14.31);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertFalse($this->getBasketValidator()->validate($basketData, $userData, $payment));
    }

    /**
     * @return SimpleBasketValidator
     */
    private function getBasketValidator()
    {
        return new SimpleBasketValidator();
    }
}
