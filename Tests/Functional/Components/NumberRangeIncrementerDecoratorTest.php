<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\NumberRangeIncrementer;
use Shopware\Components\NumberRangeIncrementerInterface;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class NumberRangeIncrementerDecoratorTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use DatabaseTestCaseTrait;
    use ReflectionHelperTrait;

    /**
     * @return void
     *
     * @before
     */
    public function unsetSessionValue()
    {
        $this->getContainer()->get('session')->offsetUnset(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY);
        $this->getContainer()->get('session')->offsetUnset('sPaymentID');
    }

    /**
     * @return void
     */
    public function testIncrementShouldGiveOrderNumberFromSession()
    {
        $expectedResult = 5355104;
        $this->getContainer()->get('session')->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $expectedResult);
        $this->getContainer()->get('session')->offsetSet('sPaymentID', 7);

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

        $this->getContainer()->get('session')->offsetSet('sPaymentID', 7);

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
            $this->createMock(LoggerServiceInterface::class),
            $this->createMock(PaymentMethodProviderInterface::class)
        );

        static::assertSame($expectedValue, $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE));
        static::assertSame($expectedValue, $numberRangeIncrementerDecorator->increment('otherName'));
    }

    /**
     * @return void
     */
    public function testIncrementWithANotPayPalPaymentMethod()
    {
        $numberRangeIncrementerDecorator = $this->getContainer()->get('shopware.number_range_incrementer');

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', [
            'sPayment' => ['name' => 'prepayment'],
        ]);

        $resultOne = $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);
        $resultTwo = $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);
        $resultThree = $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);
        $resultFour = $numberRangeIncrementerDecorator->increment(NumberRangeIncrementerDecorator::NAME_INVOICE);

        static::assertNotEquals($resultOne, $resultTwo, 'One => Two');
        static::assertNotEquals($resultOne, $resultThree, 'One => Three');
        static::assertNotEquals($resultOne, $resultFour, 'One => Four');

        static::assertNotEquals($resultTwo, $resultOne, 'Two => One');
        static::assertNotEquals($resultTwo, $resultThree, 'Two => Three');
        static::assertNotEquals($resultTwo, $resultFour, 'Two => Four');

        static::assertNotEquals($resultThree, $resultOne, 'Three => One');
        static::assertNotEquals($resultThree, $resultTwo, 'Three => Two');
        static::assertNotEquals($resultThree, $resultFour, 'Three => Four');
    }

    /**
     * @return void
     */
    public function testGetPaymentNameShouldReturnTheSessionValue()
    {
        $expectedPaymentMethodName = 'paymentNameFromSession';

        $numberRangeIncrementerDecorator = $this->getContainer()->get('shopware.number_range_incrementer');
        static::assertInstanceOf(NumberRangeIncrementerDecorator::class, $numberRangeIncrementerDecorator);

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', ['sPayment' => ['name' => $expectedPaymentMethodName]]);

        $reflectionMethod = $this->getReflectionMethod(NumberRangeIncrementerDecorator::class, 'getPaymentName');

        $result = $reflectionMethod->invoke($numberRangeIncrementerDecorator);

        static::assertSame($expectedPaymentMethodName, $result);
    }

    /**
     * @return void
     */
    public function testGetPaymentNameShouldReturnTheNameById()
    {
        $numberRangeIncrementerDecorator = $this->getContainer()->get('shopware.number_range_incrementer');

        $this->getContainer()->get('session')->offsetUnset('sOrderVariables');
        $this->getContainer()->get('session')->offsetSet('sPaymentID', 7);

        $reflectionMethod = $this->getReflectionMethod(NumberRangeIncrementerDecorator::class, 'getPaymentName');

        $result = $reflectionMethod->invoke($numberRangeIncrementerDecorator);

        static::assertSame('SwagPaymentPayPalUnified', $result);
    }

    /**
     * @return NumberRangeIncrementerDecorator
     */
    private function getNumberRangeIncrementerDecorator()
    {
        $paymentMethodProviderInterfaceMock = $this->createMock(PaymentMethodProviderInterface::class);
        $paymentMethodProviderInterfaceMock->method('getPaymentTypeByName')->willReturn(PaymentType::PAYPAL_CLASSIC_V2);

        return new NumberRangeIncrementerDecorator(
            new NumberRangeIncrementer($this->getContainer()->get('dbal_connection')),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->createMock(LoggerServiceInterface::class),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
    }
}
