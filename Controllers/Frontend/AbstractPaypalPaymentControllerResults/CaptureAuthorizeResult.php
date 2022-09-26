<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

class CaptureAuthorizeResult
{
    /**
     * @var bool
     */
    private $requireRestart;

    /**
     * @var Order|null
     */
    private $order;

    /**
     * @var bool
     */
    private $payerActionRequired;

    /**
     * @param bool       $requireRestart
     * @param Order|null $order
     * @param bool       $payerActionRequired
     */
    public function __construct($requireRestart, $order = null, $payerActionRequired = false)
    {
        $this->requireRestart = $requireRestart;
        $this->order = $order;
        $this->payerActionRequired = $payerActionRequired;
    }

    /**
     * @return bool
     */
    public function getRequireRestart()
    {
        return $this->requireRestart;
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function getPayerActionRequired()
    {
        return $this->payerActionRequired;
    }
}
