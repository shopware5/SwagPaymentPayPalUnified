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
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\NumberRangeIncrementerInterface;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class NumberRangeIncrementerDecoratorTest extends TestCase
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
    public function testIncrementShouldGiveOrderNumberFromSession()
    {
        $expectedResult = 5355104;
        $this->getContainer()->get('session')->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $expectedResult);

        $result = $this->getNumberRangeIncrementerDecorator()->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return void
     */
    public function testIncrementShouldGiveOrderNumberFromDatabase()
    {
        $sql = 'INSERT INTO `swag_payment_paypal_unified_order_number_pool` (`id`, `order_number`) VALUES (1, "123456789");';

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->exec($sql);

        $result = (int) $this->getNumberRangeIncrementerDecorator()->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);

        static::assertSame(123456789, $result);

        $assuranceSql = 'SELECT `order_number` FROM `swag_payment_paypal_unified_order_number_pool` WHERE id = 1';
        $assuranceResult = $connection->fetchColumn($assuranceSql);

        static::assertFalse($assuranceResult);
    }

    /**
     * @return void
     */
    public function testIncrementShouldGiveOrderNumberFromOriginalService()
    {
        $sql = 'UPDATE `s_order_number` SET `number` = 1000 WHERE `name` = "invoice";';
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $result = $this->getNumberRangeIncrementerDecorator()->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);

        static::assertSame(1001, $result);
    }

    /**
     * @return void
     */
    public function testIncrementWithoutASession()
    {
        $expectedValue = 2001256;

        $numberRangeIncrementerInterfaceMock = $this->createMock(NumberRangeIncrementerInterface::class);
        $numberRangeIncrementerInterfaceMock->expects(static::exactly(2))->method('increment')->willReturn($expectedValue);

        $containerMock = $this->createMock(Container::class);
        $containerMock->expects(static::once())->method('initialized')->willReturn(false);

        $dependencyProvider = new DependencyProvider($containerMock);

        $numberRangeIncrementerDecorator = new NumberRangeIncrementerDecorator(
            $numberRangeIncrementerInterfaceMock,
            $this->createMock(Connection::class),
            $dependencyProvider,
            $this->createMock(LoggerServiceInterface::class)
        );

        static::assertSame($expectedValue, $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE));
        static::assertSame($expectedValue, $numberRangeIncrementerDecorator->increment('otherName'));
    }

    /**
     * @return NumberRangeIncrementerDecorator
     */
    private function getNumberRangeIncrementerDecorator()
    {
        $numberRangeIncrementerDecorator = $this->getContainer()->get('shopware.number_range_incrementer');
        static::assertInstanceOf(NumberRangeIncrementerDecorator::class, $numberRangeIncrementerDecorator);

        return $numberRangeIncrementerDecorator;
    }
}
