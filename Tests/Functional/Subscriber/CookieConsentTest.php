<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use SwagPaymentPayPalUnified\Subscriber\CookieConsent;

class CookieConsentTest extends TestCase
{
    public function test_can_be_created()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = CookieConsent::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('addPayPalCookie', $events['CookieCollector_Collect_Cookies']);
    }

    public function test_addPayPalCookie()
    {
        $cookieCollection = $this->getSubscriber()->addPayPalCookie();
        static::assertNotNull($cookieCollection);

        /** @var CookieStruct|null $cookie */
        $cookie = $cookieCollection->first();
        static::assertInstanceOf(CookieStruct::class, $cookie);
        static::assertSame('paypal-cookies', $cookie->getName());
    }

    /**
     * @return CookieConsent
     */
    private function getSubscriber()
    {
        return new CookieConsent(Shopware()->Container()->get('snippets'));
    }
}
