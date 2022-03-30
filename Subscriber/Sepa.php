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

    public function __construct(
        SettingsServiceInterface $settingsService,
        ContextServiceInterface $contextService,
        ButtonLocaleService $buttonLocaleService
    ) {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
        $this->buttonLocaleService = $buttonLocaleService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return ['Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckout'];
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
