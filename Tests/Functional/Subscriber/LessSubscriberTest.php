<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\Subscriber\Less;

class LessSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = new Less(__DIR__ . '../../../');
        static::assertNotNull($subscriber);
    }

    public function test_onCollectLessFiles()
    {
        $lessDefinitions = (new Less(__DIR__ . '../../../'))->onCollectLessFiles();

        static::assertCount(1, $lessDefinitions);
    }

    public function test_getSubscribedEvents()
    {
        $events = Less::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertEquals('onCollectLessFiles', $events['Theme_Compiler_Collect_Plugin_Less']);
    }
}
