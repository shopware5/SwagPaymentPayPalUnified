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
use Shopware_Components_Snippet_Manager as SnippetManager;

class Register implements SubscriberInterface
{
    /**
     * @var SnippetManager
     */
    private $snippetManager;

    public function __construct(SnippetManager $snippetManager)
    {
        $this->snippetManager = $snippetManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Register' => 'onLoginForm',
        ];
    }

    /**
     * @return void
     */
    public function onLoginForm(ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        if ($controller->Request()->getActionName() !== 'index') {
            return;
        }

        $isPaymentApproveTimeout = (bool) $controller->Request()->get('paymentApproveTimeout', false);
        if (!$isPaymentApproveTimeout) {
            return;
        }

        $controller->View()->assign('sErrorMessages', ['message' => $this->snippetManager->getNamespace('frontend/paypal_unified/register/error-messages')->get('captureTimeout')]);
    }
}
