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
use Enlight_View_Default as View;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\ButtonLocaleService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class SmartPaymentButtons implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var ButtonLocaleService
     */
    private $buttonLocaleService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        PaymentMethodProviderInterface $paymentMethodProvider,
        ButtonLocaleService $buttonLocaleService
    ) {
        $this->settingsService = $settingsService;
        $this->snippetManager = $snippetManager;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->buttonLocaleService = $buttonLocaleService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addSpbInfoOnConfirm'],
                ['addInfoToPaymentRequest'],
                ['addSmartPaymentButtons', 101],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'addSmartPaymentButtonMarks',
        ];
    }

    public function addSmartPaymentButtons(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();
        $availableActions = ['confirm', 'shippingpayment'];

        if (!\in_array(\strtolower($request->getActionName()), $availableActions, true)) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();

        if ($generalSettings === null
            || !$generalSettings->getUseSmartPaymentButtons()
            || $request->getParam('spbCheckout', false)
        ) {
            return;
        }

        $this->changePaymentDescription($view, 'sPayments');

        $sandbox = $generalSettings->getSandbox();
        $view->assign('paypalUnifiedUseSmartPaymentButtons', true);
        $view->assign('paypalUnifiedModeSandbox', $sandbox);
        $view->assign('paypalUnifiedSpbClientId', $sandbox ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId());
        $view->assign('paypalUnifiedSpbCurrency', $view->getAssign('sBasket')['sCurrencyName']);
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
        $view->assign('paypalUnifiedIntent', $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT));
        $view->assign('paypalUnifiedButtonLocale', $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()));
    }

    public function addSmartPaymentButtonMarks(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();
        $availableActions = ['index', 'payment'];

        if (!\in_array(\strtolower($request->getActionName()), $availableActions, true)) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();

        if ($generalSettings === null
            || !$generalSettings->getUseSmartPaymentButtons()
        ) {
            return;
        }

        $this->changePaymentDescription($view, 'sPaymentMeans');

        $view->assign('paypalUnifiedUseSmartPaymentButtonMarks', true);
        $view->assign('paypalUnifiedSpbClientId', $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId());
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
    }

    public function addSpbInfoOnConfirm(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if (\strtolower($request->getActionName()) !== 'confirm' || !$request->getParam('spbCheckout', false)) {
            return;
        }

        $view->assign('paypalUnifiedSpbCheckout', true);
        $view->assign('paypalUnifiedAdvancedCreditDebitCardCheckout', (bool) $request->getParam('acdcCheckout', false));
        $view->assign('paypalUnifiedSpbOrderId', $request->getParam('orderId'));
        $view->assign('paypalUnifiedSpbPayerId', $request->getParam('payerId'));
        $view->assign('paypalUnifiedSpbBasketId', $request->getParam('basketId'));
    }

    public function addInfoToPaymentRequest(ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if (\strtolower($request->getActionName()) !== 'payment'
            || !$request->getParam('spbCheckout', false)
            || !$args->getResponse()->isRedirect()
        ) {
            return;
        }

        $args->getSubject()->redirect([
            'controller' => 'PaypalUnifiedV2',
            'action' => 'return',
            'spbCheckout' => true,
            'acdcCheckout' => (bool) $request->getParam('acdcCheckout', false),
            'token' => $request->getParam('orderId'),
            'PayerID' => $request->getParam('payerId'),
            'basketId' => $request->getParam('basketId'),
        ]);
    }

    /**
     * @param string $paymentsViewParameter
     */
    private function changePaymentDescription(View $view, $paymentsViewParameter)
    {
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $paymentMethods = $view->getAssign($paymentsViewParameter);

        $paymentDescription = $this->snippetManager->getNamespace('frontend/paypal_unified/smart_payment_buttons/payment')->get('description');
        foreach ($paymentMethods as &$paymentMethod) {
            if ((int) $paymentMethod['id'] === $unifiedPaymentId) {
                $paymentMethod['additionaldescription'] = '<span id="spbMarksContainer"></span>' . $paymentDescription;
                break;
            }
        }
        unset($paymentMethod);
        $view->assign($paymentsViewParameter, $paymentMethods);
    }
}
