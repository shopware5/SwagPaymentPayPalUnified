<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber\ControllerRegistration;

use Enlight\Event\SubscriberInterface;

class Widgets implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedExpressCheckout' => 'onGetEcControllerPath',
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedExpressCheckout event.
     * Returns the path to the express checkout controller.
     *
     * @return string
     */
    public function onGetEcControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Widgets/PaypalUnifiedExpressCheckout.php';
    }
}
