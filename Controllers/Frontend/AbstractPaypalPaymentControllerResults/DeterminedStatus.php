<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults;

class DeterminedStatus
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var bool
     */
    private $paymentFailed;

    /**
     * @param bool $success
     * @param bool $paymentFailed
     */
    public function __construct($success, $paymentFailed)
    {
        $this->success = $success;
        $this->paymentFailed = $paymentFailed;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return bool
     */
    public function isPaymentFailed()
    {
        return $this->paymentFailed;
    }
}
