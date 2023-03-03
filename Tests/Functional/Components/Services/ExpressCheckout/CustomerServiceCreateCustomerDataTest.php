<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use Enlight_Controller_Front;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware_Components_Config;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use Symfony\Component\Form\FormFactoryInterface;

class CustomerServiceCreateCustomerDataTest extends TestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testCreateCustomerDataWithoutPurchaseUnit()
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'createCustomerData');

        $orderStruct = $this->createOrderStruct($firstName, $lastName);

        $result = $reflectionMethod->invoke($this->createCustomerService(), $orderStruct);

        static::assertSame($firstName, $result['firstname']);
        static::assertSame($lastName, $result['lastname']);

        static::assertSame('test@example.com', $result['email']);
        static::assertSame('BWZDBTXXH3264', $result['password']);
        static::assertSame('FooBar Street 123', $result['street']);
        static::assertSame('12345', $result['zipcode']);
        static::assertSame('Testing Valley', $result['city']);
    }

    /**
     * @return void
     */
    public function testCreateCustomerDataWithPurchaseUnit()
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'createCustomerData');

        $orderStruct = $this->createOrderStruct($firstName, $lastName, true);

        $result = $reflectionMethod->invoke($this->createCustomerService(), $orderStruct);

        static::assertSame($firstName, $result['firstname']);
        static::assertSame($lastName, $result['lastname']);

        static::assertSame('test@example.com', $result['email']);
        static::assertSame('BWZDBTXXH3264', $result['password']);
        static::assertSame('UnitTest Street 123', $result['street']);
        static::assertSame('98765', $result['zipcode']);
        static::assertSame('Testing Hill', $result['city']);
    }

    /**
     * @return void
     */
    public function testCreateCustomerDataWithPurchaseUnitAndWrongSeparatedName()
    {
        $firstName = 'John';
        $lastName = 'Doe';
        $nameTemplate = '%s,%s';

        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'createCustomerData');

        $orderStruct = $this->createOrderStruct($firstName, $lastName, true, $nameTemplate);

        $result = $reflectionMethod->invoke($this->createCustomerService(), $orderStruct);

        $expectedResult = \sprintf($nameTemplate, $firstName, $lastName);

        static::assertSame($expectedResult, $result['firstname']);
        static::assertSame($expectedResult, $result['lastname']);

        static::assertSame('test@example.com', $result['email']);
        static::assertSame('BWZDBTXXH3264', $result['password']);
        static::assertSame('UnitTest Street 123', $result['street']);
        static::assertSame('98765', $result['zipcode']);
        static::assertSame('Testing Hill', $result['city']);
    }

    /**
     * @return void
     */
    public function testCreateCustomerDataWithPurchaseUnitAndWrongSeparatedNameAndSpaceInSurname()
    {
        $firstName = 'John-Doe';
        $lastName = 'Doe John';
        $nameTemplate = '%s,%s';

        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'createCustomerData');

        $orderStruct = $this->createOrderStruct($firstName, $lastName, true, $nameTemplate);

        $result = $reflectionMethod->invoke($this->createCustomerService(), $orderStruct);

        static::assertSame('John-Doe,Doe', $result['firstname']);
        static::assertSame('John', $result['lastname']);

        static::assertSame('test@example.com', $result['email']);
        static::assertSame('BWZDBTXXH3264', $result['password']);
        static::assertSame('UnitTest Street 123', $result['street']);
        static::assertSame('98765', $result['zipcode']);
        static::assertSame('Testing Hill', $result['city']);
    }

    /**
     * @return void
     */
    public function testCreateCustomerDataWithPurchaseUnitAndWrongSeparatedNameAndSpaceInGivenName()
    {
        $firstName = 'John Doe';
        $lastName = 'Doe-John';
        $nameTemplate = '%s,%s';

        $reflectionMethod = $this->getReflectionMethod(CustomerService::class, 'createCustomerData');

        $orderStruct = $this->createOrderStruct($firstName, $lastName, true, $nameTemplate);

        $result = $reflectionMethod->invoke($this->createCustomerService(), $orderStruct);

        static::assertSame('John', $result['firstname']);
        static::assertSame('Doe,Doe-John', $result['lastname']);

        static::assertSame('test@example.com', $result['email']);
        static::assertSame('BWZDBTXXH3264', $result['password']);
        static::assertSame('UnitTest Street 123', $result['street']);
        static::assertSame('98765', $result['zipcode']);
        static::assertSame('Testing Hill', $result['city']);
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param bool   $addPurchaseUnit
     * @param string $nameTemplate
     *
     * @return Order
     */
    private function createOrderStruct($firstName, $lastName, $addPurchaseUnit = false, $nameTemplate = '%s %s')
    {
        $orderResponse = require __DIR__ . '/../_fixtures/PaymentFixtureOrder.php';

        static::assertTrue(\is_array($orderResponse));

        $orderStruct = (new Order())->assign($orderResponse);

        if ($addPurchaseUnit) {
            $shippingName = new Order\PurchaseUnit\Shipping\Name();
            $shippingName->setFullName(\sprintf($nameTemplate, $firstName, $lastName));

            $shippingAddress = new Order\PurchaseUnit\Shipping\Address();
            $shippingAddress->setAddressLine1('UnitTest Street 123');
            $shippingAddress->setPostalCode('98765');
            $shippingAddress->setAdminArea2('Testing Hill');

            $shipping = new Order\PurchaseUnit\Shipping();
            $shipping->setName($shippingName);
            $shipping->setAddress($shippingAddress);

            $purchaseUnit = new Order\PurchaseUnit();
            $purchaseUnit->setShipping($shipping);

            $orderStruct->setPurchaseUnits([$purchaseUnit]);
        }

        $name = new Order\Payer\Name();
        $name->setGivenName($firstName);
        $name->setSurname($lastName);

        $address = new Order\Payer\Address();
        $address->setAddressLine1('FooBar Street 123');
        $address->setPostalCode('12345');
        $address->setAdminArea2('Testing Valley');

        $orderStruct->getPayer()->setName($name);
        $orderStruct->getPayer()->setAddress($address);
        $orderStruct->getPayer()->setEmailAddress('test@example.com');
        $orderStruct->getPayer()->setPayerId('BWZDBTXXH3264');

        return $orderStruct;
    }

    /**
     * @return CustomerService
     */
    private function createCustomerService()
    {
        $modelManagerMock = $this->createMock(ModelManager::class);
        $modelManagerMock->method('getConnection')->willReturn(
            $this->getContainer()->get('dbal_connection')
        );

        return new CustomerService(
            $this->createMock(Shopware_Components_Config::class),
            $modelManagerMock,
            $this->createMock(FormFactoryInterface::class),
            $this->createMock(ContextServiceInterface::class),
            $this->createMock(RegisterServiceInterface::class),
            $this->createMock(Enlight_Controller_Front::class),
            $this->createMock(DependencyProvider::class),
            $this->createMock(PaymentMethodProviderInterface::class),
            $this->createMock(LoggerServiceInterface::class)
        );
    }
}
