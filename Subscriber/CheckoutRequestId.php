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
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\RequestIdService;
use UnexpectedValueException;

class CheckoutRequestId implements SubscriberInterface
{
    /**
     * @var RequestIdService
     */
    private $requestIdService;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(
        RequestIdService $requestIdService,
        PaymentMethodProvider $paymentMethodProvider,
        DependencyProvider $dependencyProvider
    ) {
        $this->requestIdService = $requestIdService;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['onCheckoutAddRequestId'],
                ['forwardTo'],
            ],
        ];
    }

    /**
     * @return void
     */
    public function onCheckoutAddRequestId(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');

        if ($subject->Request()->getActionName() !== 'confirm') {
            return;
        }

        $subject->View()->assign(RequestIdService::REQUEST_ID_KEY, $this->requestIdService->generateNewRequestId());
        $this->requestIdService->removeRequestIdFromSession();
    }

    /**
     * @return void
     */
    public function forwardTo(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');
        $request = $subject->Request();

        if (\strtolower($request->getActionName()) !== 'payment'
            || !$subject->Response()->isRedirect()
        ) {
            return;
        }

        $payment = $this->getPaymentFromSession();

        try {
            $paymentType = $this->paymentMethodProvider->getPaymentTypeByName($payment['name']);
        } catch (UnexpectedValueException $unexpectedValueException) {
            return;
        }

        if (!$this->requestIdService->isRequestIdRequired($paymentType)) {
            return;
        }

        $redirectTo = $request->getParams();
        $redirectTo['controller'] = $payment['action'];
        $redirectTo['action'] = 'index';

        $subject->redirect($redirectTo);
    }

    /**
     * @return array<string,mixed>
     */
    private function getPaymentFromSession()
    {
        $sOrderVariables = $this->dependencyProvider->getSession()->get('sOrderVariables');

        return $sOrderVariables['sPayment'];
    }
}
