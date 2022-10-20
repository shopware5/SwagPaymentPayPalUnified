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
    const ORDER = 'order';
    const REQUIRE_RESTART = 'requireRestart';
    const PAYER_ACTION_REQUIRED = 'payerActionRequired';
    const INSTRUMENT_DECLINED = 'instrumentDeclined';

    /**
     * @var Order|null
     */
    private $order = null;

    /**
     * @var bool
     */
    private $requireRestart = false;

    /**
     * @var bool
     */
    private $payerActionRequired = false;

    /**
     * @var bool
     */
    private $instrumentDeclined = false;

    /**
     * @param string|null     $key
     * @param bool|Order|null $value
     */
    public function __construct($key = null, $value = null)
    {
        if ($key === null) {
            return;
        }

        $this->$key = $value;
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
