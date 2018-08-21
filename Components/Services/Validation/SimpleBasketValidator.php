<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class SimpleBasketValidator implements BasketValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $basket, array $user, Payment $payment)
    {
        if ($user['additional']['charge_vat']) {
            $basketAmount = number_format($basket['AmountNumeric'], 2);
        } else {
            $basketAmount = number_format($basket['AmountNetNumeric'], 2);
        }
        $paymentAmount = number_format($payment->getTransactions()->getAmount()->getTotal(), 2);

        if ($user['additional']['charge_vat'] && $basket['AmountWithTaxNumeric']) {
            $basketAmount = number_format($basket['AmountWithTaxNumeric'], 2);
        }

        return $basketAmount === $paymentAmount;
    }
}
