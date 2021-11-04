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
    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = CookieConsent::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('addPayPalCookie', $events['CookieCollector_Collect_Cookies']);
    }

    public function testAddPayPalCookie()
    {
        $cookieCollection = $this->getSubscriber()->addPayPalCookie();
        static::assertNotNull($cookieCollection);

        $cookie = $cookieCollection->first();
        static::assertInstanceOf(CookieStruct::class, $cookie);
        static::assertSame('paypal-cookies', $cookie->getName());

        $matchingPattern = $cookie->getMatchingPattern();
        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('paypal-cookie-consent-manager', $matchingPattern);
            static::assertStringContainsString('paypalplus_session_v2', $matchingPattern);

            return;
        }

        static::assertContains('paypal-cookie-consent-manager', $matchingPattern);
        static::assertContains('paypalplus_session_v2', $matchingPattern);
    }

    /**
     * @return CookieConsent
     */
    private function getSubscriber()
    {
        return new CookieConsent(Shopware()->Container()->get('snippets'));
    }
}
