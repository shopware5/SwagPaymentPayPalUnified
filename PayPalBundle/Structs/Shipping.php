<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Shipping\Tracker;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Shipping extends PayPalApiStruct
{
    /**
     * @var Tracker[]
     */
    private $trackers;

    /**
     * @return Tracker[]
     */
    public function getTrackers()
    {
        return $this->trackers;
    }

    /**
     * @param Tracker[] $trackers
     *
     * @return void
     */
    public function setTrackers(array $trackers)
    {
        $this->trackers = $trackers;
    }

    /**
     * @return array{trackers: array<string, mixed>}
     */
    public function toArray()
    {
        return ['trackers' => array_map(function ($tracker) {
            return $tracker->toArray();
        }, $this->trackers)];
    }
}
