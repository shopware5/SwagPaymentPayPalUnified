<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__ . '/../../../../autoload.php';

use Shopware\Kernel;

class PayPalUnifiedTestKernel extends Kernel
{
    /**
     * @var PayPalUnifiedTestKernel
     */
    private static $kernel;

    /**
     * @return void
     */
    public static function start()
    {
        self::$kernel = new self(\getenv('SHOPWARE_ENV') ?: 'testing', true);
        self::$kernel->boot();

        $container = self::$kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(\E_ALL | \E_STRICT);

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
