<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\OrderProvider;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class OrderProviderTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetDataFromOrderProvider()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/order.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $orderProvider = $this->getOrderProvider();
        $order = $orderProvider->getNotSyncedTrackingOrders();

        static::assertEquals([
            1 => [
                [
                    'id' => '199',
                    'transactionID' => 'foo',
                    'trackingCode' => 'bar',
                    'status' => '1',
                    'carrier' => 'DHL',
                    'shopId' => 1,
                ],
            ],
            2 => [
                [
                    'id' => '200',
                    'transactionID' => 'foo',
                    'trackingCode' => 'bar',
                    'status' => '1',
                    'carrier' => 'DHL',
                    'shopId' => 2,
                ],
            ],
        ], $order);
    }

    /**
     * @return OrderProvider
     */
    private function getOrderProvider()
    {
        return $this->getContainer()->get('paypal_unified.order_provider');
    }
}
