<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use Shopware_Components_Snippet_Manager as SnippetManager;

class CookieConsent implements SubscriberInterface
{
    /**
     * @var SnippetManager
     */
    private $snippetManager;

    public function __construct(SnippetManager $snippetManager)
    {
        $this->snippetManager = $snippetManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'CookieCollector_Collect_Cookies' => 'addPayPalCookie',
        ];
    }

    /**
     * @return CookieCollection<CookieStruct>
     */
    public function addPayPalCookie()
    {
        $collection = new CookieCollection();
        $collection->add(new CookieStruct(
            'paypal-cookies',
            // PayPal Cookies are not handled by Shopware, because they are not set by the plugin.
            // Therefore we use a regex which should not affect other cookies
            '/^paypal-cookie-consent-manager$|^paypalplus_session_v2$/',
            $this->snippetManager->getNamespace('frontend/paypal_unified/cookie_consent/cookie')->get('cookie/label'),
            CookieGroupStruct::TECHNICAL
        ));

        return $collection;
    }
}
