<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as EventArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Controllers_Frontend_Account;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\ButtonLocaleService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class Sepa implements SubscriberInterface
{
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

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ContextServiceInterface $contextService,
        ButtonLocaleService $buttonLocaleService,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
        $this->buttonLocaleService = $buttonLocaleService;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['onCheckout'],
                ['addVarsForEligibility'],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'addVarsForEligibility',
        ];
    }

    /**
     * @return void
     */
    public function addVarsForEligibility(EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Account $subject */
        $subject = $args->getSubject();

        $acceptedActionNames = [
            'payment',
            'shippingPayment',
        ];

        if (!\in_array($subject->Request()->getActionName(), $acceptedActionNames)) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        if (!$this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME)) {
            return;
        }

        $subject->View()->assign([
            'paypalUnifiedUseSepa' => true,
            'paypalUnifiedSpbClientId' => $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId(),
            'paypalUnifiedSpbIntent' => $generalSettings->getIntent(),
            'paypalUnifiedSpbButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
            'paypalUnifiedSpbCurrency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'paypalUnifiedSepaPaymentId' => $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME),
        ]);
    }

    /**
     * @return void
     */
    public function onCheckout(EventArgs $args)
    {
        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();
        $view = $subject->View();

        if ($subject->Request()->getParam('sepaCheckout', false)) {
            return;
        }

        $paymentMethod = $subject->View()->getAssign('sPayment');
        if ($paymentMethod['name'] !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME) {
            return;
        }

        $view->assign([
            'paypalUnifiedSepaPayment' => true,
            'paypalUnifiedSpbClientId' => $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId(),
            'paypalUnifiedSpbCurrency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'paypalUnifiedSpbIntent' => $generalSettings->getIntent(),
            'paypalUnifiedSpbStyleShape' => $generalSettings->getButtonStyleShape(),
            'paypalUnifiedSpbStyleSize' => $generalSettings->getButtonStyleSize(),
            'paypalUnifiedSpbButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
        ]);
    }
}
