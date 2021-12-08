<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Enlight_Components_Session_Namespace as ShopwareSession;
use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\DependencyInjection\Container as DIContainer;
use Shopware\Models\Shop\Shop;

class DependencyProvider
{
    /**
     * @var DIContainer
     */
    private $container;

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @return Shop|null
     */
    public function getShop()
    {
        if ($this->container->has('shop')) {
            return $this->container->get('shop');
        }

        return null;
    }

    /**
     * @return \Enlight_Controller_Front|null
     */
    public function getFront()
    {
        if ($this->container->has('front')) {
            /** @var \Enlight_Controller_Front $front */
            $front = $this->container->get('front');

            return $front;
        }

        return null;
    }

    /**
     * Returns the module with the given name, if any exists.
     *
     * @param string $moduleName
     */
    public function getModule($moduleName)
    {
        /** @var \Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');

        return $modules->getModule($moduleName);
    }

    /**
     * @return ShopwareSession
     */
    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @return string|null
     */
    public function createPaymentToken()
    {
        if ($this->container->has(PaymentTokenService::class)) {
            if ($this->isBlacklistedShopwareVersionsForPaymentToken()) {
                return null;
            }

            return $this->container->get(PaymentTokenService::class)->generate();
        }

        return null;
    }

    /**
     * In older Shopware 5.6.x versions the PaymentTokenSubscriber::onPreDispatchFrontend method
     * sets the session cookie to another path than the original Session::createSession method.
     * This was fixed with Shopware 5.6.3, so these three versions are blacklisted for this feature.
     *
     * @return bool
     *
     * @see \Shopware\Components\DependencyInjection\Bridge\Session::createSession
     * @see \Shopware\Components\Cart\PaymentTokenSubscriber::onPreDispatchFrontend
     */
    private function isBlacklistedShopwareVersionsForPaymentToken()
    {
        $blacklistedShopwareVersions = ['5.6.0', '5.6.1', '5.6.2'];
        $currentShopwareVersion = $this->container->getParameter('shopware.release.version');

        return \in_array($currentShopwareVersion, $blacklistedShopwareVersions, true);
    }
}
