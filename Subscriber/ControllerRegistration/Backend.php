<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
     * @param string                   $pluginDirectory
     * @param Enlight_Template_Manager $template
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
        $this->template->addTemplateDir(
            $this->pluginDirectory . '/Resources/views/'
        );

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
        $this->template->addTemplateDir(
            $this->pluginDirectory . '/Resources/views/'
        );

        return $this->pluginDirectory . '/Controllers/Backend/PaypalUnifiedSettings.php';
    }
}
