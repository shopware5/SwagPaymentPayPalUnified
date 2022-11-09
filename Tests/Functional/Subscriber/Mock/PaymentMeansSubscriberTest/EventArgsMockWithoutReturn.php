<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Mock\PaymentMeansSubscriberTest;

use Enlight_Event_EventArgs;

class EventArgsMockWithoutReturn extends Enlight_Event_EventArgs
{
    /**
     * @var array<array{id: int}>
     */
    public $result;

    /**
     * @return array<array{id: int}>
     */
    public function getReturn()
    {
        return [];
    }

    /**
     * @param array<array{id: int}> $result
     */
    public function setReturn($result)
    {
        $this->result = $result;
    }
}
