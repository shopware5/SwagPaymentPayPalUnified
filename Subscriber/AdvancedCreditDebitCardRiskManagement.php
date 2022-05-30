<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Hook_HookArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\AdvancedCreditDebitCard as AdvancedCreditDebitCardSettings;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class AdvancedCreditDebitCardRiskManagement implements SubscriberInterface
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

    public function __construct(PaymentMethodProviderInterface $paymentMethodProvider, SettingsServiceInterface $settingsService, ContextServiceInterface $contextService)
    {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'sAdmin::sManageRisks::after' => 'afterRiskManagement',
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

        $acdcPaymentMethodId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME);

        if ((int) $args->get('paymentID') !== $acdcPaymentMethodId) {
            return false;
        }

        $shopId = $this->contextService->getShopContext()->getShop()->getId();

        $generalSettings = $this->settingsService->getSettings($shopId);
        if (!$generalSettings instanceof General || !$generalSettings->getActive()) {
            return true;
        }

        $acdcSetting = $this->settingsService->getSettings($shopId, SettingsTable::ADVANCED_CREDIT_DEBIT_CARD);
        if (!$acdcSetting instanceof AdvancedCreditDebitCardSettings || !$acdcSetting->isActive()) {
            return true;
        }

        return false;
    }
}
