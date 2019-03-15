<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber\ControllerRegistration;

use Enlight\Event\SubscriberInterface;
use Enlight_Template_Manager;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory, Enlight_Template_Manager $template)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified' => 'onGetBackendControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings' => 'onGetBackendSettingsControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedGeneralSettings' => 'onGetBackendGeneralSettingsControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedExpressSettings' => 'onGetBackendExpressSettingsControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedInstallmentsSettings' => 'onGetBackendInstallmentsSettingsControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPlusSettings' => 'onGetBackendPlusSettingsControllerPath',
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnified event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendControllerPath()
    {
        $this->template->addTemplateDir($this->pluginDirectory . '/Resources/views/');

        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnified.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendSettingsControllerPath()
    {
        $this->template->addTemplateDir($this->pluginDirectory . '/Resources/views/');

        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedSettings.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedGeneralSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendGeneralSettingsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedGeneralSettings.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedExpressSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendExpressSettingsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedExpressSettings.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedInstallmentsSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendInstallmentsSettingsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedInstallmentsSettings.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Backend_PaypalUnifiedPlusSettings event.
     * Returns the path to the backend application controller.
     *
     * @return string
     */
    public function onGetBackendPlusSettingsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedPlusSettings.php';
    }
}
