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

class FraudNet implements SubscriberInterface
{
    const FRAUD_NET_SOURCE_WEBSITE_IDENTIFIER = 'shopware-v5_checkout-page';

    const PAYMENT_METHODS_REQUIRED_FRAUD_NET = [
        PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
        PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME,
    ];

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(DependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
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

        $subject->View()->assign('fraudNetSessionId', $this->getFraudNetSessionId());
        $subject->View()->assign('fraudNetFlowId', self::FRAUD_NET_SOURCE_WEBSITE_IDENTIFIER);
        $subject->View()->assign('usePayPalFraudNet', true);
    }

    /**
     * @return string
     */
    private function getFraudNetSessionId()
    {
        $fraudNetSessionId = $this->dependencyProvider->getSession()->offsetGet('fraudNetSessionId');

        if ($fraudNetSessionId === null) {
            $fraudNetSessionId = bin2hex((string) openssl_random_pseudo_bytes(16));
            $this->dependencyProvider->getSession()->offsetSet('fraudNetSessionId', $fraudNetSessionId);
        }

        return $fraudNetSessionId;
    }
}
