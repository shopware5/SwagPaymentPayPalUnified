<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult\ThreeDSecure;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class AuthenticationResult extends PayPalApiStruct
{
    const LIABILITY_SHIFT_POSSIBLE = 'POSSIBLE';
    const LIABILITY_SHIFT_NO = 'NO';
    const LIABILITY_SHIFT_UNKNOWN = 'UNKNOWN';

    /**
     * @var string|null
     * @phpstan-var AuthenticationResult::LIABILITY_SHIFT_*|null
     */
    private $liabilityShift;

    /**
     * @var ThreeDSecure
     */
    private $threeDSecure;

    /**
     * @return string|null
     * @phpstan-return AuthenticationResult::LIABILITY_SHIFT_*|null
     */
    public function getLiabilityShift()
    {
        return $this->liabilityShift;
    }

    /**
     * @param string|null $liabilityShift
     * @phpstan-param AuthenticationResult::LIABILITY_SHIFT_*|null $liabilityShift
     *
     * @return void
     */
    public function setLiabilityShift($liabilityShift)
    {
        $this->liabilityShift = $liabilityShift;
    }

    /**
     * @return ThreeDSecure
     */
    public function getThreeDSecure()
    {
        return $this->threeDSecure;
    }

    /**
     * @param ThreeDSecure $threeDSecure
     *
     * @return void
     */
    public function setThreeDSecure($threeDSecure)
    {
        $this->threeDSecure = $threeDSecure;
    }
}
