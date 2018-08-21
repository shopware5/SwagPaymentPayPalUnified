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
    /**
     * @dataProvider test_is_valid_dataProvider
     *
     * @param float $amountNumeric
     * @param float amountNetNumeric
     * @param int|null $chargeVat
     * @param float    $totalAmount
     * @param bool     $expectedResult
     */
    public function test_is_valid($amountNumeric, $amountNetNumeric, $chargeVat, $totalAmount, $expectedResult)
    {
        $basketData = [
            'AmountNumeric' => $amountNumeric,
            'AmountNetNumeric' => $amountNetNumeric,
        ];
        $userData = [
            'additional' => [
            'charge_vat' => $chargeVat,
        ], ];

        $payment = new Payment();
        $transactions = new Payment\Transactions();
        $amount = new Payment\Transactions\Amount();
        $amount->setTotal($totalAmount);

        $transactions->setAmount($amount);
        $payment->setTransactions($transactions);

        $this->assertSame($expectedResult, $this->getBasketValidator()->validate($basketData, $userData, $payment));
    }

    public function test_is_valid_dataProvider()
    {
        return [
            [15.00, 12.61, 1, 15.00, true],
            [15.00, 12.61, null, 12.61, true],
            [15.00, 12.61, null, 15, false],
            [15.00, 12.61, 1, 14.00, false],
        ];
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
