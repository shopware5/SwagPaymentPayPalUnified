<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\NumberRangeIncrementer;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class OrderNumberServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     *
     * @before
     */
    public function unsetSessionValue()
    {
        $this->getContainer()->get('session')->offsetUnset(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY);
    }

    /**
     * @return void
     */
    public function testGetOrderNumberShouldGiveFromSession()
    {
        $expectedResult = 'orderNumberFromSession';
        $this->getContainer()->get('session')->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $expectedResult);

        $result = $this->getOrderNumberService()->getOrderNumber();

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testGetOrderNumberShouldGiveFromDatabase()
    {
        $sql = 'INSERT INTO `swag_payment_paypal_unified_order_number_pool` (`id`, `order_number`) VALUES (1, "orderNumberFromDatabase");';

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->exec($sql);

        $result = $this->getOrderNumberService()->getOrderNumber();

        static::assertSame('orderNumberFromDatabase', $result);

        $assuranceSql = 'SELECT `order_number` FROM `swag_payment_paypal_unified_order_number_pool` WHERE id = 1';
        $assuranceResult = $connection->fetchColumn($assuranceSql);

        static::assertFalse($assuranceResult);
    }

    /**
     * @return void
     */
    public function testGetOrderNumberShouldGiveFromOriginalService()
    {
        $sql = 'UPDATE `s_order_number` SET `number` = 1000 WHERE `name` = "invoice";';
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $result = $this->getOrderNumberService()->getOrderNumber();

        static::assertSame('1001', $result);
    }

    /**
     * @return void
     */
    public function testPutBackOrdernumberToPool()
    {
        $expectedResult = '999';
        $this->getOrderNumberService()->restoreOrdernumberToPool($expectedResult);

        $session = $this->getContainer()->get('session');
        static::assertFalse($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
        static::assertNull($session->offsetGet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $result = $this->getOrderNumberService()->getOrderNumber();

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testReleaseOrderNumber()
    {
        $sessionValue = 'anyOrderNumber';
        $session = $this->getContainer()->get('session');
        static::assertFalse($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $session->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $sessionValue);
        static::assertTrue($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
        static::assertSame($sessionValue, $session->offsetGet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $this->getOrderNumberService()->releaseOrderNumber();
        static::assertFalse($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
        static::assertNull($session->offsetGet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
    }

    /**
     * @return void
     */
    public function testReleaseOrderNumberShouldAlsoDeleteTheEntryFromPoolDatabase()
    {
        $sessionValue = 'anyOtherOrderNumber';

        $selectSql = 'SELECT id, order_number FROM swag_payment_paypal_unified_order_number_pool WHERE order_number = "anyOtherOrderNumber"';
        $insertSql = 'INSERT INTO swag_payment_paypal_unified_order_number_pool (order_number) VALUES ("anyOtherOrderNumber")';

        $connection = $this->getContainer()->get('dbal_connection');
        $connection->exec($insertSql);

        $ensurance = $connection->fetchAssoc($selectSql);
        static::assertIsArray($ensurance);
        static::assertSame($sessionValue, $ensurance['order_number']);

        $session = $this->getContainer()->get('session');
        static::assertFalse($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $session->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $sessionValue);
        static::assertTrue($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
        static::assertSame($sessionValue, $session->offsetGet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $this->getOrderNumberService()->releaseOrderNumber();
        static::assertFalse($session->offsetExists(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));
        static::assertNull($session->offsetGet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY));

        $result = $connection->fetchAssoc($selectSql);
        static::assertFalse($result);
    }

    /**
     * @return OrderNumberService
     */
    private function getOrderNumberService()
    {
        $numberRangeIncrementerDecorator = new NumberRangeIncrementerDecorator(
            new NumberRangeIncrementer($this->getContainer()->get('dbal_connection')),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->createMock(LoggerServiceInterface::class),
            $this->createMock(PaymentMethodProviderInterface::class)
        );

        return new OrderNumberService(
            $numberRangeIncrementerDecorator,
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.dependency_provider')
        );
    }
}
