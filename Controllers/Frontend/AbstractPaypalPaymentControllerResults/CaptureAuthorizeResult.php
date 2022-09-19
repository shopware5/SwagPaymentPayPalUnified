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
     * @param bool       $requireRestart
     * @param Order|null $order
     */
    public function __construct($requireRestart, $order = null)
    {
        $this->requireRestart = $requireRestart;
        $this->order = $order;
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
}
