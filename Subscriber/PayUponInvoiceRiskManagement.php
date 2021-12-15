<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PayUponInvoiceRiskManagement implements SubscriberInterface
{
    /**
     * @var PaymentMethodProvider
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

    public function __construct(
        PaymentMethodProvider $paymentMethodProvider,
        DependencyProvider $dependencyProvider,
        ValidatorInterface $validator,
        ContextServiceInterface $contextService
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->dependencyProvider = $dependencyProvider;
        $this->validator = $validator;
        $this->contextService = $contextService;
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

    public function afterManageRisks(\Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() === true) {
            return true;
        }

        $basket = $args->get('basket');
        $user = $args->get('user');
        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ((int) $args->get('paymentID') !== $paymentId) {
            return false;
        }

        if (empty($basket)) {
            $basket = [
                'content' => $this->dependencyProvider->getSession()->offsetGet('sBasketQuantity'),
                'AmountNumeric' => $this->dependencyProvider->getSession()->offsetGet('sBasketAmount'),
            ];
        }

        return $args->getSubject()->executeRiskRule('PayPalUnifiedInvoiceRiskManagementRule', $user, $basket, '', $paymentId);
    }

    public function onExecuteRule(\Enlight_Event_EventArgs $args)
    {
        $user = $args->get('user');
        $basket = $args->get('basket');
        $paymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);

        if ($args->get('paymentID') !== $paymentId) {
            return false;
        }

        $values = [
            'country' => $user['additional']['country']['countryiso'],
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'amount' => $basket['AmountNumeric'],
            'phoneNumber' => $user['billingaddress']['phone'],
        ];

        // Full name, email, delivery and billing address, date of birth, and phone number.
        $violationList = $this->validator->validate(
            $values,
            new Collection([
                'country' => new EqualTo('DE'),
                'currency' => new EqualTo('EUR'),
                'amount' => new Range(['min' => 5.0, 'max' => 2500.00]),
                'phoneNumber' => new NotBlank(),
            ])
        );

        return $violationList->count() > 0;
    }
}
