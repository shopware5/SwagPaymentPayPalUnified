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
use SwagPaymentPayPalUnified\Components\ErrorCodes;

class ApmCheckoutFinish implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckoutFinish',
        ];
    }

    /**
     * @return void
     */
    public function onCheckoutFinish(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->get('subject');

        if ($subject->Request()->getActionName() !== 'finish') {
            return;
        }

        if (!$subject->Request()->getParam('requireContactToMerchant', false)) {
            return;
        }

        $subject->View()->assign('paypalUnifiedErrorCode', ErrorCodes::APM_PAYMENT_FAILED_CONTACT_MERCHANT);
    }
}
