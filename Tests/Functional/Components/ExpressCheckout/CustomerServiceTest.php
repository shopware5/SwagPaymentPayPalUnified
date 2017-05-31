<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\ExpressCheckout;

use Doctrine\DBAL\Connection;
use SwagPaymentPayPalUnified\Components\ExpressCheckout\CustomerService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class CustomerServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_service_is_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.express_checkout.customer_service');
        $this->assertEquals(CustomerService::class, get_class($service));
    }

    public function test_construct()
    {
        $service = new CustomerService(
            Shopware()->Container()->get('config'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('shopware.form.factory'),
            Shopware()->Container()->get('shopware_storefront.context_service'),
            Shopware()->Container()->get('shopware_account.register_service'),
            Shopware()->Container()->get('front'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );

        $this->assertNotNull($service);
    }

    public function test_createNewCustomer()
    {
        $payment = new Payment();

        $payer = new Payer();
        $payerInfo = new PayerInfo();
        $payerInfo->setFirstName('Shopware');
        $payerInfo->setLastName('PHPUnit');
        $payerInfo->setPhone('0123456789');
        $payerInfo->setEmail('phpunit@test.com');
        $payerInfo->setPayerId('TestUser');

        $billingAddress = new Address();
        $billingAddress->setCountryCode('DE');
        $billingAddress->setState('NW');
        $billingAddress->setPhone('0123456789');
        $billingAddress->setLine1('Teststreet 1a');
        $billingAddress->setLine2('Basement');
        $billingAddress->setPostalCode('48624');
        $billingAddress->setCity('Schöppingen');

        $payerInfo->setBillingAddress($billingAddress);
        $payer->setPayerInfo($payerInfo);
        $payment->setPayer($payer);

        $service = $this->getCustomerService();

        $this->setFrontRequest();
        $service->createNewCustomer($payment);

        $user = $this->getUserByMail()[0];

        $this->assertNotNull($user);
        $this->assertEquals('1', $user['accountmode']);
        $this->assertEquals('Shopware', $user['firstname']);
        $this->assertEquals('PHPUnit', $user['lastname']);

        $this->assertNotNull(Shopware()->Container()->get('session')->offsetGet('sUserId'));
    }

    private function getUserByMail($mail = 'phpunit@test.com')
    {
        /** @var Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

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
        return Shopware()->Container()->get('paypal_unified.express_checkout.customer_service');
    }
}
