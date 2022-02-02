<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payee;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address as ShippingAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Name as ShippingName;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class CustomerServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    public function testServiceIsAvailable()
    {
        $service = $this->getContainer()->get('paypal_unified.express_checkout.customer_service');
        static::assertSame(CustomerService::class, \get_class($service));
    }

    public function testConstruct()
    {
        $service = new CustomerService(
            $this->getContainer()->get('config'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('shopware.form.factory'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('shopware_account.register_service'),
            $this->getContainer()->get('front'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.logger_service')
        );

        static::assertNotNull($service);
    }

    public function testCreateNewCustomer()
    {
        $orderStruct = new Order();

        $payer = new Payer();
        $payerInfo = new PayerInfo();

        $name = new Name();
        $name->setGivenName('Shopware');
        $name->setSurname('PHPUnit');

        $phoneNumber = new PhoneNumber();
        $phoneNumber->setNationalNumber('0123456789');

        $phone = new Phone();
        $phone->setPhoneNumber($phoneNumber);

        $payer->setName($name);
        $payer->setPhone($phone);
        $payer->setEmailAddress('phpunit@test.com');
        $payer->setPayerId('TestUser');

        $billingAddress = new Address();
        $billingAddress->setCountryCode('DE');
        $billingAddress->setState('NW');
        $billingAddress->setPhone('0123456789');
        $billingAddress->setLine1('Teststreet 1a');
        $billingAddress->setLine2('Basement');
        $billingAddress->setPostalCode('48624');
        $billingAddress->setCity('Schöppingen');

        $payerInfo->setBillingAddress($billingAddress);
        $orderStruct->setPayer($payer);

        $displayData = new Payee\DisplayData();

        $payee = new Payee();
        $payee->setEmailAddress('phpunit@test.com');
        $payee->setDisplayData($displayData);

        $shippingName = new ShippingName();
        $shippingName->setFullName('Shopware PHPUnit');

        $shippingAddress = new ShippingAddress();
        $shippingAddress->setCountryCode($billingAddress->getCountryCode());
        $shippingAddress->setPostalCode($billingAddress->getPostalCode());
        $shippingAddress->setAddressLine1($billingAddress->getLine1());
        $shippingAddress->setAddressLine2($billingAddress->getLine2());
        $shippingAddress->setAdminArea2($billingAddress->getCity());

        $shipping = new Shipping();
        $shipping->setName($shippingName);
        $shipping->setAddress($shippingAddress);

        $purchaseUnit = new Order\PurchaseUnit();
        $purchaseUnit->setPayee($payee);
        $purchaseUnit->setShipping($shipping);

        $service = $this->getCustomerService();

        $orderStruct->setPurchaseUnits([$purchaseUnit]);
        $this->setFrontRequest();
        $service->createNewCustomer($orderStruct);

        $user = $this->getUserByMail()[0];

        static::assertNotNull($user);
        static::assertSame('1', $user['accountmode']);
        static::assertSame('Shopware', $user['firstname']);
        static::assertSame('PHPUnit', $user['lastname']);

        static::assertNotNull($this->getContainer()->get('session')->get('sUserId'));
    }

    /**
     * @param string $mail
     *
     * @return array<array<string, mixed>>
     */
    private function getUserByMail($mail = 'phpunit@test.com')
    {
        $db = $this->getContainer()->get('dbal_connection');

        $sql = 'SELECT * FROM s_user WHERE email=:emailAddress';

        return $db->fetchAll($sql, ['emailAddress' => $mail]);
    }

    private function setFrontRequest()
    {
        Shopware()->Front()->setRequest(new \Enlight_Controller_Request_RequestTestCase());
    }

    /**
     * @return CustomerService
     */
    private function getCustomerService()
    {
        return $this->getContainer()->get('paypal_unified.express_checkout.customer_service');
    }
}
