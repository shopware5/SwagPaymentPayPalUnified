<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

trait ShopRegistrationTrait
{
    /**
     * @before
     *
     * @param int $shopId
     *
     * @return void
     */
    public function registerShop($shopId = 1)
    {
        $this->getContainer()->get('paypal_unified.backend.shop_registration_service')->registerShopById($shopId);
    }

    /**
     * @after
     *
     * @return void
     */
    public function unregisterShop()
    {
        $this->getContainer()->reset('shop');
    }
}
