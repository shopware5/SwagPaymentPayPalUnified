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
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;

class CookieConsentTest extends TestCase
{
    use AssertStringContainsTrait;

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
        static::assertStringContains($this, 'paypal-cookie-consent-manager', $matchingPattern);
        static::assertStringContains($this, 'paypalplus_session_v2', $matchingPattern);
    }

    /**
     * @return CookieConsent
     */
    private function getSubscriber()
    {
        return new CookieConsent(Shopware()->Container()->get('snippets'));
    }
}
