<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock;

class HookArgsWithoutSubject extends \Enlight_Hook_HookArgs
{
    /**
     * @param bool $isShopware55
     */
    public function __construct($isShopware55 = false)
    {
        if ($isShopware55) {
            parent::__construct(new \stdClass(), '');
        }
    }

    public function getSubject()
    {
        return null;
    }
}
