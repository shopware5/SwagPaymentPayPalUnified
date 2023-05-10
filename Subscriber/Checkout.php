<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\PatchOrderService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitPatch;

class Checkout implements SubscriberInterface
{
    /**
     * @var PatchOrderService
     */
    private $patchOrderService;

    public function __construct(PatchOrderService $patchOrderService)
    {
        $this->patchOrderService = $patchOrderService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckoutConfirm',
        ];
    }

    /**
     * @return void
     */
    public function onCheckoutConfirm(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');
        $request = $subject->Request();

        if (\strtolower($request->getActionName()) !== 'confirm') {
            return;
        }

        $payerActionRequired = $request->get('payerActionRequired', 0);
        $payerInstrumentDeclined = $request->get('payerInstrumentDeclined', 0);
        $threeDSecureExceptionCode = $request->get('threeDSecureExceptionCode', 0);

        $subject->View()->assign('payerActionRequired', $payerActionRequired);
        $subject->View()->assign('payerInstrumentDeclined', $payerInstrumentDeclined);
        $subject->View()->assign('threeDSecureExceptionCode', $threeDSecureExceptionCode);

        $basketHasChanged = $request->getParam('payPalCartHasChanged', false);
        $isExpressCheckout = $request->getParam('expressCheckout', false);
        $token = $request->getParam('token', false);

        if (!$basketHasChanged || !$isExpressCheckout || !$token) {
            return;
        }

        $patch = $this->patchOrderService->createOrderPurchaseUnitPatch(
            $subject->View()->getAssign('sUserData'),
            $subject->View()->getAssign('sBasket')
        );

        if (!$patch instanceof OrderPurchaseUnitPatch) {
            return;
        }

        $this->patchOrderService->patchPayPalExpressOrder([$patch], $token);
    }
}
