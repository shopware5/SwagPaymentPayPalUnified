<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__ . '/../../../../autoload.php';

use Shopware\Models\Shop\Shop;

class PayPalUnifiedTestKernel extends \Shopware\Kernel
{
    public static function start()
    {
        $kernel = new self(getenv('SHOPWARE_ENV') ?: 'testing', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $container->get('models')->getRepository(Shop::class);

        if ($container->has('shopware.components.shop_registration_service')) {
            $container->get('shopware.components.shop_registration_service')->registerResources(
                $repository->getActiveDefault()
            );
        } else {
            $repository->getActiveDefault()->registerResources();
        }

        if (!self::isPluginInstalledAndActivated()) {
            die('Error: The plugin is not installed or activated, tests aborted!');
        }

        Shopware()->Loader()->registerNamespace('SwagPaymentPayPalUnified', __DIR__ . '/../');
    }

    /**
     * @return bool
     */
    private static function isPluginInstalledAndActivated()
    {
        /** @var \Doctrine\DBAL\Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "SELECT active FROM s_core_plugins WHERE name='SwagPaymentPayPalUnified'";
        $active = $db->fetchColumn($sql);

        return (bool) $active;
    }
}

PayPalUnifiedTestKernel::start();
