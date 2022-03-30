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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SepaRiskManagement implements SubscriberInterface
{
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

    /**
     * @var int
     */
    private $sepaPaymentId;

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
        $this->sepaPaymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'sAdmin::sManageRisks::after' => 'afterRiskManagement',
            'Shopware_Modules_Admin_Execute_Risk_Rule_PayPalUnifiedSepaRiskManagementRule' => 'onExecuteSepaRule',
        ];
    }

    /**
     * @return bool
     */
    public function afterRiskManagement(Enlight_Hook_HookArgs $args)
    {
        if ($args->getReturn() === true) {
            return true;
        }

        if ((int) $args->get('paymentID') !== $this->sepaPaymentId) {
            return false;
        }

        $generalSettings = $this->settingsService->getSettings($this->contextService->getShopContext()->getShop()->getId());
        if (!$generalSettings instanceof General || !$generalSettings->getActive()) {
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

        return $args->getSubject()->executeRiskRule('PayPalUnifiedSepaRiskManagementRule', $user, $basket, '', $this->sepaPaymentId);
    }

    /**
     * @return bool
     */
    public function onExecuteSepaRule(Enlight_Event_EventArgs $args)
    {
        $user = $args->get('user');

        if ($args->get('paymentID') !== $this->sepaPaymentId) {
            return false;
        }

        $values = [
            'country' => $user['additional']['country']['countryiso'],
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
        ];

        $violationList = $this->validator->validate(
            $values,
            new Collection([
                'country' => new EqualTo('DE'),
                'currency' => new EqualTo('EUR'),
            ])
        );

        return $violationList->count() > 0;
    }
}
