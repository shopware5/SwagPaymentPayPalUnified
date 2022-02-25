<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var int
     */
    private $unifiedPaymentId;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        PaymentMethodProvider $paymentMethodProvider
    ) {
        $this->unifiedPaymentId = $paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $this->settingsService = $settingsService;
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

    public function onFilterPaymentMeans(\Enlight_Event_EventArgs $args)
    {
        /** @var array $availableMethods */
        $availableMethods = $args->getReturn();

        foreach ($availableMethods as $index => $paymentMethod) {
            if ((int) $paymentMethod['id'] === $this->unifiedPaymentId
                && (!$this->settingsService->hasSettings() || !$this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_ACTIVE))
            ) {
                //Force unset the payment method, because it's not available without any settings.
                unset($availableMethods[$index]);
            }
        }

        $args->setReturn($availableMethods);
    }
}
