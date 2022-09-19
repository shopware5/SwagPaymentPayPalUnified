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
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
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
     * @return NumberRangeIncrementerDecorator
     */
    private function getNumberRangeIncrementerDecorator()
    {
        $numberRangeIncrementerDecorator = $this->getContainer()->get('shopware.number_range_incrementer');
        static::assertInstanceOf(NumberRangeIncrementerDecorator::class, $numberRangeIncrementerDecorator);

        return $numberRangeIncrementerDecorator;
    }
}
