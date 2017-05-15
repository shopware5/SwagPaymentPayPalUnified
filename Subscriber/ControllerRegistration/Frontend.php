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

class Frontend implements SubscriberInterface
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
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified' => 'onGetFrontendControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedInstallments' => 'onGetInstallmentsPaymentControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook' => 'onGetWebhookControllerPath',
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedInstallments' => 'onGetInstallmentsControllerPath',
        ];
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnified event.
     * Returns the path to the frontend controller.
     *
     * @return string
     */
    public function onGetFrontendControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnified.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedWebhook event.
     * Returns the path to the webhook controller.
     *
     * @return string
     */
    public function onGetWebhookControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedWebhook.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Widgets_PaypalUnifiedInstallments event.
     * Returns the path to the installments controller.
     *
     * @return string
     */
    public function onGetInstallmentsControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Widgets/PaypalUnifiedInstallments.php';
    }

    /**
     * Handles the Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaypalUnifiedInstallments event.
     * Returns the path to the installments controller.
     *
     * @return string
     */
    public function onGetInstallmentsPaymentControllerPath()
    {
        return $this->pluginDirectory . '/Controllers/Frontend/PaypalUnifiedInstallments.php';
    }
}
