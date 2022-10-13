<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Enlight_Components_Session_Namespace as ShopwareSession;
use Enlight_Controller_Front;
use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\DependencyInjection\Container as DIContainer;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Modules;

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
        if (!$this->container->initialized('shop')) {
            return null;
        }

        $shop = $this->container->get('shop');

        if (!$shop instanceof Shop) {
            return null;
        }

        return $shop;
    }

    /**
     * @return Enlight_Controller_Front|null
     */
    public function getFront()
    {
        if ($this->container->initialized('front')) {
            return $this->container->get('front');
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
        /** @var Shopware_Components_Modules $modules */
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
        if ($this->container->initialized(PaymentTokenService::class)) {
            if ($this->isBlacklistedShopwareVersionsForPaymentToken()) {
                return null;
            }

            return $this->container->get(PaymentTokenService::class)->generate();
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isInitialized($name)
    {
        return $this->container->initialized($name);
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
