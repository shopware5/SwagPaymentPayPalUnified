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
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedV2ExpressCheckout' => 'onGetEcV2ControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedV2SmartPaymentButtons' => 'onGetSpbV2ControllerPath',
        ];
    }

    public function onGetSpbV2ControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Widgets/PaypalUnifiedV2SmartPaymentButtons.php';
    }

    /**
     * @return string
     */
    public function onGetEcV2ControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Widgets/PaypalUnifiedV2ExpressCheckout.php';
    }
}
