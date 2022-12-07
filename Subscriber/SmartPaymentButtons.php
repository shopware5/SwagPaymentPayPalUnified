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
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addSmartPaymentButtons',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'addSmartPaymentButtonMarks',
        ];
    }

    /**
     * @return void
     */
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

        $view->assign([
            'paypalUnifiedUseSmartPaymentButtons' => true,
            'paypalUnifiedSpbClientId' => $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId(),
            'paypalUnifiedSpbCurrency' => $view->getAssign('sBasket')['sCurrencyName'],
            'paypalUnifiedPaymentId' => $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME),
            'paypalUnifiedIntent' => $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT),
            'paypalUnifiedButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
            'paypalUnifiedSpbButtonStyleShape' => $generalSettings->getButtonStyleShape(),
            'paypalUnifiedSpbButtonStyleSize' => $generalSettings->getButtonStyleSize(),
        ]);
    }

    /**
     * @return void
     */
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
