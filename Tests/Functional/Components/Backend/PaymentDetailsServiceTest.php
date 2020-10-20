<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Backend;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Backend\PaymentDetailsService;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Components\Services\TransactionHistoryBuilderService;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;
use SwagPaymentPayPalUnified\Tests\Mocks\PaymentResourceMock;
use SwagPaymentPayPalUnified\Tests\Mocks\SaleResourceMock;

class PaymentDetailsServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;

    const ORDER_ID = 'orderId';
    const AUTHORIZATION_ID = 'authorizationId';
    const SALE_ID = 'saleId';
    const LEGACY_ID = 'legacyId';

    public function testGetPaymentDetailsOrder()
    {
        $result = $this->createPaymentDetailService()->getPaymentDetails(self::ORDER_ID, '0', '');

        static::assertTrue($result['success']);
        static::assertArrayHasKey('payment', $result);
        static::assertArrayHasKey('order', $result);
        static::assertArrayHasKey('history', $result);
    }

    public function testGetPaymentDetailsAuthorization()
    {
        $result = $this->createPaymentDetailService()->getPaymentDetails(self::AUTHORIZATION_ID, '0', '');

        static::assertTrue($result['success']);
        static::assertArrayHasKey('payment', $result);
        static::assertArrayHasKey('authorization', $result);
        static::assertArrayHasKey('history', $result);
    }

    public function testGetPaymentDetailsSale()
    {
        $result = $this->createPaymentDetailService()->getPaymentDetails(self::SALE_ID, '0', '');

        static::assertTrue($result['success']);
        static::assertArrayHasKey('payment', $result);
        static::assertArrayHasKey('sale', $result);
        static::assertArrayHasKey('history', $result);
    }

    public function testGetPaymentDetailsSaleThrowException()
    {
        $result = $this->createPaymentDetailService()->getPaymentDetails(PaymentResourceMock::THROW_EXCEPTION, '0', '');

        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
        static::assertArrayNotHasKey('payment', $result);
        static::assertArrayNotHasKey('sale', $result);
        static::assertArrayNotHasKey('history', $result);
    }

    public function testGetPaymentDetailsLegacy()
    {
        $legacyPaymentId = '99';
        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->executeQuery(
            \sprintf("INSERT INTO `s_core_paymentmeans` (`id`, `name`) VALUES ( %s, 'paypal');", $legacyPaymentId)
        );

        $result = $this->createPaymentDetailService()->getPaymentDetails('', $legacyPaymentId, self::LEGACY_ID);

        static::assertTrue($result['success']);
        static::assertTrue($result['legacy']);
        static::assertArrayHasKey('payment', $result);
        static::assertArrayHasKey('history', $result);
    }

    private function createPaymentDetailService()
    {
        return new PaymentDetailsService(
            new ExceptionHandlerService(new LoggerMock()),
            new PaymentResourceMock(),
            new SaleResourceMock(),
            new LegacyService(Shopware()->Container()->get('dbal_connection')),
            new TransactionHistoryBuilderService()
        );
    }
}
