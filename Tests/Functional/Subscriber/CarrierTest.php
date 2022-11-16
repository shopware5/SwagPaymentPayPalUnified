<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Subscriber\Carrier;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class CarrierTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testOnFilterOrderAttributesShouldAddNoCarrierOnFallbakckId()
    {
        $eventArgs = new Enlight_Event_EventArgs(['orderParams' => ['dispatchID' => '0']]);

        $request = new Enlight_Controller_Request_RequestHttp();
        Shopware()->Front()->setRequest($request);

        $this->getCarrierSubscriber()->onFilterOrderAttributes($eventArgs);
        $result = $eventArgs->getReturn();
        static::assertNull($result);
    }

    /**
     * @return void
     */
    public function testOnFilterOrderAttributesShouldAddAPaymentTypeClassic()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/carrier.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $eventArgs = new Enlight_Event_EventArgs(['orderParams' => ['dispatchID' => 10]]);

        $request = new Enlight_Controller_Request_RequestHttp();
        Shopware()->Front()->setRequest($request);

        $this->getCarrierSubscriber()->onFilterOrderAttributes($eventArgs);

        $result = $eventArgs->getReturn()['swag_paypal_unified_carrier'];

        static::assertSame('DHL', $result);
    }

    /**
     * @return Carrier
     */
    private function getCarrierSubscriber()
    {
        return $this->getContainer()->get('paypal_unified.subscriber.carrier');
    }
}
