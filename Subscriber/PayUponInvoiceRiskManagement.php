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
use Enlight_Hook_HookArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice as PayUponInvoiceModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayUponInvoiceRiskManagement implements SubscriberInterface
{
    const PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED = 'PayPalUnifiedPayUponInvoiceBlocked';
    const PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY = 'payPalUnifiedPayUponInvoiceErrorList';

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        DependencyProvider $dependencyProvider,
        ValidatorInterface $validator,
        ContextServiceInterface $contextService,
        SettingsServiceInterface $settingsService
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->dependencyProvider = $dependencyProvider;
        $this->validator = $validator;
        $this->contextService = $contextService;
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sAdmin::sManageRisks::after' => 'afterManageRisks',
            'Shopware_Modules_Admin_Execute_Risk_Rule_PayPalUnifiedInvoiceRiskManagementRule' => 'onExecuteRule',
        ];
    }

    /**
     * @return bool
     */
    public function afterManageRisks(Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() === true) {
            return true;
        }

        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ((int) $args->get('paymentID') !== $paymentId) {
            return false;
        }

        $basket = $args->get('basket');
        $user = $args->get('user');

        if (empty($basket)) {
            $basket = [
                'content' => $this->dependencyProvider->getSession()->offsetGet('sBasketQuantity'),
                'AmountNumeric' => $this->dependencyProvider->getSession()->offsetGet('sBasketAmount'),
            ];
        }

        return $args->getSubject()->executeRiskRule('PayPalUnifiedInvoiceRiskManagementRule', $user, $basket, '', $paymentId);
    }

    /**
     * @return bool
     */
    public function onExecuteRule(Enlight_Event_EventArgs $args)
    {
        $user = $args->get('user');
        $basket = $args->get('basket');
        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ($args->get('paymentID') !== $paymentId) {
            return false;
        }

        if (!\is_array($user)) {
            return false;
        }

        if ($this->checkForMissingTechnicalRequirements()) {
            return true;
        }

        $violationList = $this->validate($user, (float) $basket['AmountNumeric']);

        return $violationList->count() > 0;
    }

    /**
     * @param array<string,mixed> $user
     * @param float               $amountNumeric
     *
     * @return ConstraintViolationListInterface
     */
    private function validate(array $user, $amountNumeric)
    {
        $values = [
            'country' => $user['additional']['country']['countryiso'],
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'amount' => $amountNumeric,
        ];

        return $this->validator->validate(
            $values,
            new Collection([
                'country' => new EqualTo('DE'),
                'currency' => new EqualTo('EUR'),
                'amount' => new Range(['min' => 5.0, 'max' => 2500.00]),
            ])
        );
    }

    /**
     * Do not show PUI if the customer has no chance to fill in the required data
     *
     * @return bool
     */
    private function checkForMissingTechnicalRequirements()
    {
        $generalSettings = $this->settingsService->getSettings($this->contextService->getShopContext()->getShop()->getId());
        if (!$generalSettings instanceof General) {
            return true;
        }

        $payUponInvoiceSettings = $this->settingsService->getSettings($this->contextService->getShopContext()->getShop()->getId(), SettingsTable::PAY_UPON_INVOICE);
        if (!$payUponInvoiceSettings instanceof PayUponInvoiceModel) {
            return true;
        }

        $payUponInvoiceActive = $payUponInvoiceSettings->isActive();
        $onboardingCompleted = $generalSettings->getSandbox() ? $payUponInvoiceSettings->isSandboxOnboardingCompleted() : $payUponInvoiceSettings->isOnboardingCompleted();

        if (!$payUponInvoiceActive || !$onboardingCompleted) {
            return true;
        }

        return false;
    }
}
