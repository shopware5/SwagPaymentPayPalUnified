<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Shopware\Components\DependencyInjection\Container as DIContainer;
use Shopware\Models\Shop\DetachedShop;

class DependencyProvider
{
    /**
     * @var DIContainer
     */
    private $container;

    /**
     * @param DIContainer $container
     */
    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @return null|DetachedShop
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
     *
     * @return mixed
     */
    public function getModule($moduleName)
    {
        /** @var \Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');

        return $modules->getModule($moduleName);
    }
}
