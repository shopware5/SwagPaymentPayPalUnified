<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\Less;

class LessSubscriberTest extends TestCase
{
    public function testCanBeCreated()
    {
        $subscriber = new Less(__DIR__ . '../../../');
        static::assertNotNull($subscriber);
    }

    public function testOnCollectLessFiles()
    {
        $lessDefinitions = (new Less(__DIR__ . '../../../'))->onCollectLessFiles();

        static::assertCount(1, $lessDefinitions);
    }

    public function testGetSubscribedEvents()
    {
        $events = Less::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onCollectLessFiles', $events['Theme_Compiler_Collect_Plugin_Less']);
    }
}
