<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

interface BasketValidatorInterface
{
    /**
     * Validates the basket using the shopware basket and the payment response from PayPal
     *
     * @return bool
     */
    public function validate(array $basket, array $user, Payment $payment);
}
