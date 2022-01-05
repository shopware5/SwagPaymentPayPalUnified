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
    /**
     * @var PayPalUnifiedTestKernel
     */
    private static $kernel;

    public static function start()
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        self::$kernel = new self(\getenv('SHOPWARE_ENV') ?: 'testing', true);
        self::$kernel->boot();

        $container = self::$kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(\E_ALL | \E_STRICT);

        $repository = $container->get('models')->getRepository(Shop::class);

        if ($container->has('shopware.components.shop_registration_service')) {
            $container->get('shopware.components.shop_registration_service')->registerResources(
                $repository->getActiveDefault()
            );
        } else {
            $repository->getActiveDefault()->registerResources();
        }

        if (!self::isPluginInstalledAndActivated()) {
            exit('Error: The plugin is not installed or activated, tests aborted!');
        }

        Shopware()->Loader()->registerNamespace('SwagPaymentPayPalUnified', __DIR__ . '/../');
    }

    /**
     * @return PayPalUnifiedTestKernel
     */
    public static function getKernel()
    {
        return self::$kernel;
    }

    /**
     * @return bool
     */
    private static function isPluginInstalledAndActivated()
    {
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "SELECT active FROM s_core_plugins WHERE name='SwagPaymentPayPalUnified'";
        $active = $db->fetchColumn($sql);

        return (bool) $active;
    }
}

PayPalUnifiedTestKernel::start();
