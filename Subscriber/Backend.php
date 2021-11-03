<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @param string $pluginDir
     */
    public function __construct($pluginDir)
    {
        $this->pluginDir = $pluginDir;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onLoadBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Config' => 'onPostDispatchConfig',
        ];
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatchSecure_Backend_Index event.
     * Extends the backend icon set by the paypal icon.
     */
    public function onLoadBackendIndex(ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $view->addTemplateDir($this->pluginDir . '/Resources/views/');
        $view->extendsTemplate('backend/paypal_unified/menu_icon.tpl');
    }

    public function onPostDispatchConfig(ActionEventArgs $arguments)
    {
        $view = $arguments->getSubject()->View();

        if ($arguments->getRequest()->getActionName() === 'load') {
            $view->addTemplateDir($this->pluginDir . '/Resources/views/');
            $view->extendsTemplate('backend/config/view/form/document_paypal_unified.js');
        }
    }
}
