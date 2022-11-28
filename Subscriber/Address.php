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
use Shopware_Controllers_Frontend_Address;

class Address implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Address' => 'onPostDispatchAddress',
        ];
    }

    /**
     * @return void
     */
    public function onPostDispatchAddress(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Address $subject */
        $subject = $args->get('subject');

        if ($subject->Request()->getActionName() !== 'index') {
            return;
        }

        $subject->View()->assign([
            'invalidBillingAddress' => $subject->Request()->get('invalidBillingAddress'),
            'invalidShippingAddress' => $subject->Request()->get('invalidShippingAddress'),
        ]);
    }
}
