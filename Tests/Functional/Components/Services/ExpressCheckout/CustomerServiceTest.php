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
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address as PayerAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address as ShippingAddress;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Name as ShippingName;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class CustomerServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use ShopRegistrationTrait;

    const STREET = 'Teststreet 1a';
    const ADDRESSLINE_2 = 'Basement';
    const POSTAL_CODE = '48624';
    const CITY = 'Schöppingen';
    const COUNTRY_CODE = 'DE';
    const STATE_CODE = 'NW';
    const GIVEN_NAME = 'GivenName';
    const SURNAME = 'Surname';
    const FULL_NAME_1 = 'Different';
    const FULL_NAME_2 = 'Name Test';
    const CUSTOMER_EMAIL_ADDRESS = 'phpunit@test.com';

    /**
     * @dataProvider createNewCustomerCases
     *
     * @param bool   $purchaseUnitAvailable
     * @param string $expectedFirstName
     * @param string $expectedLastName
     *
     * @return void
     */
    public function testCreateNewCustomer($purchaseUnitAvailable, $expectedFirstName, $expectedLastName)
    {
        $payer = $this->createPayer();

        $orderStruct = new Order();
        $orderStruct->setPayer($payer);

        if ($purchaseUnitAvailable) {
            $purchaseUnit = new PurchaseUnit();
            $shipping = $this->createShipping();
            $purchaseUnit->setShipping($shipping);
            $orderStruct->setPurchaseUnits([$purchaseUnit]);
        }

        $this->setFrontRequest();

        $this->getCustomerService()->createNewCustomer($orderStruct);

        $customer = $this->getCustomerByMail(self::CUSTOMER_EMAIL_ADDRESS);

        static::assertNotNull($customer);
        static::assertSame('1', $customer['accountmode']);
        static::assertSame($expectedFirstName, $customer['firstname']);
        static::assertSame($expectedLastName, $customer['lastname']);

        static::assertNotNull($this->getContainer()->get('session')->get('sUserId'));
    }

    /**
     * @return \Generator<string, array<bool|string>>
     */
    public function createNewCustomerCases()
    {
        yield 'purchase unit available' => [
            true,
            self::FULL_NAME_1,
            self::FULL_NAME_2,
        ];

        yield 'purchase unit not available' => [
            false,
            self::GIVEN_NAME,
            self::SURNAME,
        ];
    }

    /**
     * @param string $mail
     *
     * @return array<string, mixed>
     */
    private function getCustomerByMail($mail)
    {
        $db = $this->getContainer()->get('dbal_connection');

        $sql = 'SELECT * FROM s_user WHERE email LIKE :emailAddress';

        return $db->fetchAll($sql, ['emailAddress' => $mail])[0];
    }

    /**
     * @return void
     */
    private function setFrontRequest()
    {
        $this->getContainer()->get('front')->setRequest(new \Enlight_Controller_Request_RequestTestCase());
    }

    /**
     * @return CustomerService
     */
    private function getCustomerService()
    {
        return $this->getContainer()->get('paypal_unified.express_checkout.customer_service');
    }

    /**
     * @return Payer
     */
    private function createPayer()
    {
        $name = $this->createName();
        $phone = $this->createPhone();
        $address = $this->cratePayerAddress();

        $payer = new Payer();
        $payer->setName($name);
        $payer->setPhone($phone);
        $payer->setAddress($address);
        $payer->setEmailAddress(self::CUSTOMER_EMAIL_ADDRESS);
        $payer->setPayerId('TestUser');

        return $payer;
    }

    /**
     * @return Name
     */
    private function createName()
    {
        $name = new Name();
        $name->setGivenName(self::GIVEN_NAME);
        $name->setSurname(self::SURNAME);

        return $name;
    }

    /**
     * @return Phone
     */
    private function createPhone()
    {
        $phoneNumber = new PhoneNumber();
        $phoneNumber->setNationalNumber('0123456789');

        $phone = new Phone();
        $phone->setPhoneNumber($phoneNumber);

        return $phone;
    }

    /**
     * @return PayerAddress
     */
    private function cratePayerAddress()
    {
        $address = new PayerAddress();
        $address->setAddressLine1(self::STREET);
        $address->setAddressLine2(self::ADDRESSLINE_2);
        $address->setPostalCode(self::POSTAL_CODE);
        $address->setAdminArea2(self::CITY);
        $address->setCountryCode(self::COUNTRY_CODE);
        $address->setAdminArea1(self::STATE_CODE);

        return $address;
    }

    /**
     * @return Shipping
     */
    private function createShipping()
    {
        $shippingName = new ShippingName();
        $shippingName->setFullName(sprintf('%s %s', self::FULL_NAME_1, self::FULL_NAME_2));

        $shippingAddress = new ShippingAddress();
        $shippingAddress->setCountryCode(self::COUNTRY_CODE);
        $shippingAddress->setPostalCode(self::POSTAL_CODE);
        $shippingAddress->setAddressLine1(self::STREET);
        $shippingAddress->setAddressLine2(self::ADDRESSLINE_2);
        $shippingAddress->setAdminArea2(self::CITY);

        $shipping = new Shipping();
        $shipping->setName($shippingName);
        $shipping->setAddress($shippingAddress);

        return $shipping;
    }
}
