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
     * @var bool
     */
    private $instrumentDeclined;

    /**
     * @param bool       $requireRestart
     * @param Order|null $order
     * @param bool       $payerActionRequired
     * @param bool       $instrumentDeclined
     */
    public function __construct($requireRestart, $order = null, $payerActionRequired = false, $instrumentDeclined = false)
    {
        $this->requireRestart = $requireRestart;
        $this->order = $order;
        $this->payerActionRequired = $payerActionRequired;
        $this->instrumentDeclined = $instrumentDeclined;
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

    /**
     * @return bool
     */
    public function getInstrumentDeclined()
    {
        return $this->instrumentDeclined;
    }
}
