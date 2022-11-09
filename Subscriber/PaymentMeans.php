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
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    public function __construct(
        SettingsServiceInterface $settingsService,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->settingsService = $settingsService;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter' => 'onFilterPaymentMeans',
        ];
    }

    /**
     * @return void
     */
    public function onFilterPaymentMeans(Enlight_Event_EventArgs $args)
    {
        $availableMethods = $args->getReturn();

        $activePayPalPaymentMethods = $this->paymentMethodProvider->getActivePayments(PaymentMethodProvider::getAllUnifiedNames());
        $activePayPalPaymentMethodIds = array_map('\intval', array_values($activePayPalPaymentMethods));

        foreach ($availableMethods as $index => $paymentMethod) {
            if (\in_array((int) $paymentMethod['id'], $activePayPalPaymentMethodIds, true)
                && (!$this->settingsService->hasSettings() || !$this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ACTIVE))
            ) {
                // Force unset the payment method, because it's not available without any settings.
                unset($availableMethods[$index]);
            }
        }

        $args->setReturn($availableMethods);
    }
}
