<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;
use SwagPaymentPayPalUnified\Subscriber\MediaGarbageCollector;

class MediaGarbageCollectorTest extends PHPUnit_Framework_TestCase
{
    public function test_getSubscribesEvents_expects_array()
    {
        $result = MediaGarbageCollector::getSubscribedEvents();

        $subset = [
            'Shopware_Collect_MediaPositions' => 'onCollectMediaPositions',
        ];

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('Shopware_Collect_MediaPositions', $result);
        $this->assertArraySubset($subset, $result);
    }

    public function test_onCollectMediaPositions_expectsArrayCollection()
    {
        $subscriber = $this->getSubscriber();

        /** @var ArrayCollection $result */
        $result = $subscriber->onCollectMediaPositions();
        /** @var MediaPosition $innerResult */
        $innerResult = $result->first();

        $this->assertInstanceOf(ArrayCollection::class, $result);
        $this->assertCount(1, $result->toArray());

        $this->assertInstanceOf(MediaPosition::class, $innerResult);
        $this->assertSame('path', $innerResult->getMediaColumn());
        $this->assertSame(1, $innerResult->getParseType());
        $this->assertSame('logo_image', $innerResult->getSourceColumn());
        $this->assertSame('swag_payment_paypal_unified_settings_general', $innerResult->getSourceTable());
    }

    /**
     * @return MediaGarbageCollector
     */
    private function getSubscriber()
    {
        return new MediaGarbageCollector();
    }
}
