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
use Shopware\Models\Shop\DetachedShop;

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
     * @return DetachedShop|null
     */
    public function getShop()
    {
        if ($this->container->has('shop')) {
            return $this->container->get('shop');
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
            return $this->container->get(PaymentTokenService::class)->generate();
        }

        return null;
    }
}
