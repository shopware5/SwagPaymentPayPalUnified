<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use UnexpectedValueException;

class PayUponInvoiceRiskManagement implements SubscriberInterface
{
    const PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY = 'PayPalUnifiedPayUponInvoiceBlockedTechnically';
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
            'Shopware_Modules_Admin_Payment_Fallback' => 'setPaymentMethodBlockedFlag',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
        ];
    }

    /**
     * @return bool
     */
    public function afterManageRisks(\Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() === true) {
            return true;
        }

        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ((int) $args->get('paymentID') !== $paymentId) {
            return false;
        }

        $generalSettings = $this->settingsService->getSettings($this->contextService->getShopContext()->getShop()->getId());

        if (!$generalSettings instanceof General) {
            $this->dependencyProvider->getSession()->offsetSet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY, true);

            return true;
        }

        $payUponInvoiceSettings = $this->settingsService->getSettings($this->contextService->getShopContext()->getShop()->getId(), SettingsTable::PAY_UPON_INVOICE);

        if (!$payUponInvoiceSettings instanceof PayUponInvoice) {
            $this->dependencyProvider->getSession()->offsetSet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY, true);

            return true;
        }

        $payUponInvoiceActive = $payUponInvoiceSettings->isActive();
        $onboardingCompleted = $generalSettings->getSandbox() ? $payUponInvoiceSettings->isSandboxOnboardingCompleted() : $payUponInvoiceSettings->isOnboardingCompleted();

        if (!$payUponInvoiceActive || !$onboardingCompleted) {
            $this->dependencyProvider->getSession()->offsetSet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY, true);

            return true;
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
    public function onExecuteRule(\Enlight_Event_EventArgs $args)
    {
        $user = $args->get('user');
        $basket = $args->get('basket');
        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ($args->get('paymentID') !== $paymentId) {
            return false;
        }

        if ($this->useSimpleValidation()) {
            $violationList = $this->validateSimple($user['additional']['country']['countryiso']);

            return $violationList->count() > 0;
        }

        $violationList = $this->validateExtended($user, (float) $basket['AmountNumeric']);

        return $this->handleViolationList($violationList);
    }

    /**
     * @return void
     */
    public function onPostDispatchCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        if ($view->getAssign('paymentBlocked') !== true) {
            return;
        }

        if ($this->dependencyProvider->getSession()->offsetGet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY)) {
            $this->dependencyProvider->getSession()->offsetUnset(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED_TECHNICALLY);

            return;
        }

        if ($this->dependencyProvider->getSession()->offsetGet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED)) {
            $view->assign([
                self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED => true,
                self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY => $this->dependencyProvider->getSession()->offsetGet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY),
            ]);

            $this->dependencyProvider->getSession()->offsetUnset(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED);
        }
    }

    /**
     * @return void
     */
    public function setPaymentMethodBlockedFlag(\Enlight_Event_EventArgs $args)
    {
        // Only show the message if the customer actually chose pay upon invoice
        if ($args->get('name') !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME) {
            return;
        }

        $this->dependencyProvider->getSession()->offsetSet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_BLOCKED, true);
    }

    /**
     * @return bool
     */
    private function useSimpleValidation()
    {
        $front = $this->dependencyProvider->getFront();

        if (!$front instanceof Enlight_Controller_Front) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s, got %s.', Enlight_Controller_Front::class, 'null'));
        }

        $request = $front->Request();

        if (!$request instanceof Enlight_Controller_Request_Request) {
            throw new UnexpectedValueException(sprintf('Expected instance of %s, got %s.', Enlight_Controller_Request_Request::class, 'null'));
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $allowList = [
            'checkout' => [
                'shippingPayment',
                'saveShippingPayment',
            ],
            'account' => [
                'payment',
            ],
        ];

        foreach ($allowList as $allowedController => $allowedActions) {
            if ($controller === $allowedController) {
                return \in_array($action, $allowedActions, true);
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function handleViolationList(ConstraintViolationListInterface $violationList)
    {
        $hasError = $violationList->count() > 0;
        if (!$hasError) {
            return false;
        }

        $errorList = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violationList as $violation) {
            $errorList[] = $violation->getPropertyPath();
        }

        $this->dependencyProvider->getSession()->offsetSet(self::PAY_PAL_UNIFIED_PAY_UPON_INVOICE_ERROR_LIST_KEY, $errorList);

        return true;
    }

    /**
     * @param string $countryiso
     *
     * @return ConstraintViolationListInterface
     */
    private function validateSimple($countryiso)
    {
        $values = [
            'country' => $countryiso,
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
        ];

        return $this->validator->validate(
            $values,
            new Collection([
                'country' => new EqualTo('DE'),
                'currency' => new EqualTo('EUR'),
            ])
        );
    }

    /**
     * @param array<string,mixed> $user
     * @param float               $amountNumeric
     *
     * @return ConstraintViolationListInterface
     */
    private function validateExtended(array $user, $amountNumeric)
    {
        $values = [
            'country' => $user['additional']['country']['countryiso'],
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'amount' => $amountNumeric,
            'phoneNumber' => $user['billingaddress']['phone'],
            'birthday' => $user['additional']['user']['birthday'],
        ];

        return $this->validator->validate(
            $values,
            new Collection([
                'country' => new EqualTo('DE'),
                'currency' => new EqualTo('EUR'),
                'amount' => new Range(['min' => 5.0, 'max' => 2500.00]),
                'phoneNumber' => new NotBlank(),
                'birthday' => [new NotBlank(), new Date()],
            ])
        );
    }
}
