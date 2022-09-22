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
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class FraudNet implements SubscriberInterface
{
    const FRAUD_NET_SOURCE_WEBSITE_IDENTIFIER = 'shopware-v5_checkout-page';

    const FRAUD_NET_SESSION_KEY = 'fraudNetSessionId';

    const PAYMENT_METHODS_REQUIRED_FRAUD_NET = [
        PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
    ];

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(DependencyProvider $dependencyProvider, SettingsServiceInterface $settingsService)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->settingsService = $settingsService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckout',
        ];
    }

    public function onCheckout(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');

        if ($subject->Request()->getActionName() !== 'confirm') {
            return;
        }

        $selectedPayment = $subject->View()->getAssign('sPayment');
        if (!\in_array($selectedPayment['name'], self::PAYMENT_METHODS_REQUIRED_FRAUD_NET)) {
            return;
        }

        $subject->View()->assign([
            'fraudNetSessionId' => $this->getFraudNetSessionId(),
            'fraudNetFlowId' => self::FRAUD_NET_SOURCE_WEBSITE_IDENTIFIER,
            'fraudnetSandbox' => (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_SANDBOX),
            'usePayPalFraudNet' => true,
        ]);
    }

    /**
     * @return string
     */
    private function getFraudNetSessionId()
    {
        $fraudNetSessionId = $this->dependencyProvider->getSession()->offsetGet(self::FRAUD_NET_SESSION_KEY);

        if ($fraudNetSessionId === null) {
            $fraudNetSessionId = bin2hex((string) openssl_random_pseudo_bytes(16));
            $this->dependencyProvider->getSession()->offsetSet(self::FRAUD_NET_SESSION_KEY, $fraudNetSessionId);
        }

        return $fraudNetSessionId;
    }
}
