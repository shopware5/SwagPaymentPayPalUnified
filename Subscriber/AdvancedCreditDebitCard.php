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
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Services\EUStates;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\ClientTokenResource;

class AdvancedCreditDebitCard implements SubscriberInterface
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
     * @var ClientTokenResource
     */
    private $clientTokenResource;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var EUStates
     */
    private $euStatesService;

    /**
     * @var ButtonLocaleService
     */
    private $buttonLocaleService;

    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        SettingsServiceInterface $settingsService,
        ClientTokenResource $clientTokenResource,
        ContextServiceInterface $contextService,
        DependencyProvider $dependencyProvider,
        EUStates $euStatesService,
        ButtonLocaleService $buttonLocaleService
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->settingsService = $settingsService;
        $this->clientTokenResource = $clientTokenResource;
        $this->contextService = $contextService;
        $this->dependencyProvider = $dependencyProvider;
        $this->euStatesService = $euStatesService;
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
    public function onCheckout(Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $paymentMethod = $subject->View()->getAssign('sPayment');

        if ($paymentMethod['name'] !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME) {
            return;
        }

        if ($args->getRequest()->getParam('spbCheckout')) {
            return;
        }

        if ($args->getRequest()->getActionName() === 'payment') {
            $session = $this->dependencyProvider->getSession();
            $session->offsetSet('paypalOrderId', $args->getRequest()->getParam('paypalOrderId'));

            return;
        }

        if ($args->getRequest()->getActionName() !== 'confirm') {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        if (!$this->settingsService->get('active', SettingsTable::ADVANCED_CREDIT_DEBIT_CARD)) {
            return;
        }

        $clientToken = $this->clientTokenResource->generateToken($this->contextService->getShopContext()->getShop()->getId());

        $cardHolderData = $this->createCardHolderData($subject->View()->getAssign('sUserData'));

        $viewData = [
            'paypalUnifiedAdvancedCreditDebitCardActive' => true,
            'clientId' => $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId(),
            'clientToken' => $clientToken->getClientToken(),
            'cardHolderData' => $cardHolderData,
            'paypalUnifiedCurrency' => $this->contextService->getContext()->getCurrency()->getCurrency(),
            'paypalUnifiedButtonLocale' => $this->buttonLocaleService->getButtonLocale($generalSettings->getButtonLocale()),
            'paypalUnifiedEcButtonStyleShape' => $generalSettings->getButtonStyleShape(),
            'paypalUnifiedEcButtonStyleSize' => $generalSettings->getButtonStyleSize(),
        ];

        if (!isset($cardHolderData['contingencies'])) {
            $viewData['extendedFields'] = true;
        }

        $subject->View()->assign($viewData);
    }

    /**
     * @param array<string, mixed> $userData
     *
     * @return array<string, mixed>
     */
    private function createCardHolderData($userData)
    {
        $billingAddress = $userData['billingaddress'];
        $additional = $userData['additional'];

        $cardHolderData = [
            'cardHolderName' => sprintf('%s %s', $billingAddress['firstname'], $billingAddress['lastname']),
            'billingAddress' => [
                // Street address, line 1
                'streetAddress' => $billingAddress['street'] ?: '',
                // Street address, line 2 (Ex => Unit, Apartment, etc.)
                'extendedAddress' => '',
                // City
                'locality' => $billingAddress['city'] ?: '',
                // Zip-Code
                'postalCode' => $billingAddress['zipcode'] ?: '',
                // Country Code
                'countryCodeAlpha2' => $additional['country']['countryiso'] ?: '',
            ],
        ];

        if (!$this->euStatesService->isEUCountry($additional['country']['countryiso'])) {
            return $cardHolderData;
        }

        $cardHolderData['contingencies'] = ['SCA_WHEN_REQUIRED'];

        return $cardHolderData;
    }
}
