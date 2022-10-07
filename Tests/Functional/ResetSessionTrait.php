<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

trait ResetSessionTrait
{
    /**
     * @return void
     */
    private function resetSession()
    {
        if (\method_exists(\Enlight_Components_Session_Namespace::class, 'reset')) {
            Shopware()->Container()->get('session')->reset();
        }
        if (\method_exists(\Enlight_Components_Session_Namespace::class, 'unsetAll')) {
            Shopware()->Container()->get('session')->unsetAll();
        }
    }
}
