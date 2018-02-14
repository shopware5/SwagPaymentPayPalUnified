<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class MediaGarbageCollector implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Collect_MediaPositions' => 'onCollectMediaPositions',
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectMediaPositions()
    {
        return new ArrayCollection([
            new MediaPosition('swag_payment_paypal_unified_settings_general', 'logo_image', 'path'),
        ]);
    }
}
