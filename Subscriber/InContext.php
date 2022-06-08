<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\ButtonLocaleService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class InContext implements SubscriberInterface
{
    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ButtonLocaleService
     */
    private $buttonLocaleService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        PaymentMethodProviderInterface $paymentMethodProvider,
        ContextServiceInterface $contextService,
        ButtonLocaleService $buttonLocaleService
    ) {
        $this->settingsService = $settingsService;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->contextService = $contextService;
        $this->buttonLocaleService = $buttonLocaleService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addInContextButton'],
                ['addInfoToPaymentRequest'],
                ['addInContextInfoToRequest', 100],
            ],
        ];
    }

    public function addInContextButton(Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();
        $action = $controller->Request()->getActionName();

        if ($action !== 'confirm') {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $settings */
        $settings = $this->settingsService->getSettings();
        if (!$settings
            || !$settings->getActive()
            || !$settings->getUseInContext()
            || $settings->getUseSmartPaymentButtons()
        ) {
            return;
        }

        $view = $controller->View();

        $view->assign([
            'paypalUnifiedPaymentId' => $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME),
            'paypalUnifiedUseInContext' => $settings->getUseInContext(),
            'paypalUnifiedButtonStyleColor' => $settings->getButtonStyleColor(),
            'paypalUnifiedButtonStyleShape' => $settings->getButtonStyleShape(),
            'paypalUnifiedButtonStyleSize' => $settings->getButtonStyleSize(),
            'paypalUnifiedButtonLocale' => $this->buttonLocaleService->getButtonLocale($settings->getButtonLocale()),
            'paypalUnifiedClientId' => $settings->getSandbox() ? $settings->getSandboxClientId() : $settings->getClientId(),
            'paypalUnifiedCurrency' => $this->contextService->getContext()->getCurrency()->getCurrency(),
            'paypalUnifiedIntent' => $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_INTENT),
        ]);
    }

    public function addInContextInfoToRequest(Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'payment'
            && $request->getParam('useInContext')
            && $args->getResponse()->isRedirect()
        ) {
            $args->getSubject()->redirect([
                'controller' => 'PaypalUnifiedV2',
                'action' => 'return',
                'useInContext' => true,
            ]);
        } elseif (strtolower($request->getActionName()) === 'confirm' && $request->getParam('inContextCheckout', false)) {
            // This determines, whether the paypal-Buttons need to be rendered
            $view->assign([
                'paypalUnifiedInContextCheckout' => true,
                'paypalUnifiedInContextOrderId' => $request->getParam('paypalOrderId'),
                'paypalUnifiedInContextPayerId' => $request->getParam('payerId'),
                'paypalUnifiedInContextBasketId' => $request->getParam('basketId'),
            ]);
        }
    }

    /**
     * @return void
     */
    public function addInfoToPaymentRequest(Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if (strtolower($request->getActionName()) !== 'payment'
            || !$request->getParam('inContextCheckout', false)
            || !$args->getResponse()->isRedirect()
        ) {
            return;
        }

        $args->getSubject()->redirect([
            'controller' => 'PaypalUnifiedV2',
            'action' => 'return',
            'inContextCheckout' => true,
            'token' => $request->getParam('paypalOrderId'),
            'PayerID' => $request->getParam('payerId'),
            'basketId' => $request->getParam('basketId'),
        ]);
    }
}
