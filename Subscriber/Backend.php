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
use Enlight_View_Default;
use SwagPaymentPayPalUnified\PayPalBundle\Services\NonceService;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var NonceService
     */
    private $nonceService;

    /**
     * @param string $pluginDir
     */
    public function __construct($pluginDir, NonceService $nonceService)
    {
        $this->pluginDir = $pluginDir;
        $this->nonceService = $nonceService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onLoadBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Config' => 'onPostDispatchConfig',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Payment' => 'onPostDispatchPayment',
        ];
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatchSecure_Backend_Index event.
     * Extends the backend icon set by the paypal icon.
     */
    public function onLoadBackendIndex(ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $request = $args->getRequest();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');
        $view->extendsTemplate('backend/paypal_unified/menu_icon.tpl');

        if ($request->getActionName() === 'index' && $request->getParam('file') === 'app') {
            foreach (['sellerNonceSandbox', 'sellerNonceLive'] as $nonceType) {
                $view->assign($nonceType, $this->nonceService->getBase64UrlEncodedRandomNonce());
            }

            $view->extendsTemplate('backend/paypal_unified_settings/mixin/onboarding_helper.js');
        }
    }

    public function onPostDispatchConfig(ActionEventArgs $arguments)
    {
        $view = $arguments->getSubject()->View();

        if ($arguments->getRequest()->getActionName() === 'load') {
            $view->addTemplateDir($this->pluginDir . '/Resources/views/');
            $view->extendsTemplate('backend/config/view/form/document_paypal_unified.js');
        }
    }

    public function onPostDispatchPayment(ActionEventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->get('subject')->View();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');

        if ($args->get('request')->getActionName() === 'load') {
            $view->extendsTemplate('backend/payment/controller/payment_paypal_unified.js');
        }
    }
}
