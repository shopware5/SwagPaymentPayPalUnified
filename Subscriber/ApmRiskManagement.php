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
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm\ValidatorFactory;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm\ValueFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApmRiskManagement implements SubscriberInterface
{
    const GROUP_EURO = 'euro';

    const GROUP_UK = 'uk';

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
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    public function __construct(
        PaymentMethodProvider $paymentMethodProvider,
        DependencyProvider $dependencyProvider,
        ValidatorInterface $validator,
        ValueFactory $valueFactory,
        ValidatorFactory $validatorFactory
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->dependencyProvider = $dependencyProvider;
        $this->validator = $validator;
        $this->valueFactory = $valueFactory;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sAdmin::sManageRisks::after' => 'afterManageRisks',
            'Shopware_Modules_Admin_Execute_Risk_Rule_ApmRiskManagementRule' => 'onExecuteApmRule',
        ];
    }

    public function afterManageRisks(Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() === true) {
            return true;
        }

        $basket = $args->get('basket');
        $user = $args->get('user');
        $paymentId = (int) $args->get('paymentID');

        $activePayments = $this->paymentMethodProvider->getActivePayments($this->paymentMethodProvider->getAlternativePaymentMethodNames());

        $currentPaymentName = null;
        $currentPaymentId = null;
        foreach ($activePayments as $activePaymentName => $activePaymentId) {
            if ($paymentId !== (int) $activePaymentId) {
                continue;
            }

            $currentPaymentId = $activePaymentId;
            $currentPaymentName = $activePaymentName;

            break;
        }

        if ($currentPaymentName === null || $currentPaymentId === null) {
            return false;
        }

        if (empty($basket)) {
            $basket = [
                'content' => $this->dependencyProvider->getSession()->offsetGet('sBasketQuantity'),
                'AmountNumeric' => $this->dependencyProvider->getSession()->offsetGet('sBasketAmount'),
            ];
        }

        if (empty($user)) {
            return true;
        }

        return $args->getSubject()->executeRiskRule('ApmRiskManagementRule', $user, $basket, $currentPaymentName, $currentPaymentId);
    }

    public function onExecuteApmRule(Enlight_Event_EventArgs $args)
    {
        $user = $args->get('user');
        $basket = $args->get('basket');
        $paymentName = $args->get('value');

        $paymentType = $this->paymentMethodProvider->getPaymentTypeByName($paymentName);

        $values = $this->valueFactory->createValue($paymentType, $basket, $user);
        $validator = $this->validatorFactory->createValidator($paymentType);

        if ($paymentType === PaymentType::APM_SOFORT) {
            $group = $user['additional']['country']['countryiso'] === 'GB' ? self::GROUP_UK : self::GROUP_EURO;

            $violationList = $this->validator->validate($values, $validator, [$group]);
        } else {
            $violationList = $this->validator->validate($values, $validator);
        }

        return $violationList->count() > 0;
    }
}
