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

        /*
         * This is the `s`-parameter provided to the fraudnet script. Either we
         * as a partner, or the merchants themselves need to get this from
         * "our paypal representative": "The FraudNet team will provide the
         * source website identifier to your team."
         *
         * TODO: (PT-12531) get the identifier and either read it from settingsService (merchant-specific) or hard-code it (partner-specific)
         *
         * @see https://developer.paypal.com/docs/limited-release/fraudnet/integrate/add-parameter-block/#configuration-parameters
         */
        $subject->View()->assign('fraudNetFlowId', 'b9abd91c51ba11ecbf630242ac130002');

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
