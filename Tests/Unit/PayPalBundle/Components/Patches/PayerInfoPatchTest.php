<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\PayPalBundle\Components\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PayerInfoPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;

class PayerInfoPatchTest extends \PHPUnit_Framework_TestCase
{
    public function test_getPath()
    {
        $patch = new PayerInfoPatch($this->getPayerInfo());

        $this->assertEquals('/payer/payer_info', $patch->getPath());
    }

    public function test_getOperation()
    {
        $patch = new PayerInfoPatch($this->getPayerInfo());

        $this->assertEquals('replace', $patch->getOperation());
    }

    public function test_getValue()
    {
        $patch = new PayerInfoPatch($this->getPayerInfo());

        $value = $patch->getValue();

        //Payer info
        $this->assertCount(7, $value);
        $this->assertEquals('123456789', $value['phone']);
        $this->assertEquals('test@example.com', $value['email']);
        $this->assertEquals('Firstname', $value['first_name']);
        $this->assertEquals('Lastname', $value['last_name']);
        $this->assertEquals('DE', $value['country_code']);

        //Billing address
        $this->assertEquals('DE', $value['billing_address']['country_code']);
        $this->assertEquals('123456789', $value['billing_address']['phone']);
        $this->assertEquals('Schöppingen', $value['billing_address']['city']);
        $this->assertEquals('Ebbinghoff 10', $value['billing_address']['line1']);
        $this->assertEquals('48624', $value['billing_address']['postal_code']);
        $this->assertEquals('NW', $value['billing_address']['state']);
    }

    /**
     * @return PayerInfo
     */
    private function getPayerInfo()
    {
        $payerInfo = new PayerInfo();
        $payerInfo->setPhone('123456789');
        $payerInfo->setLastName('Lastname');
        $payerInfo->setFirstName('Firstname');
        $payerInfo->setCountryCode('DE');
        $payerInfo->setEmail('test@example.com');

        $billingAddress = new Address();
        $billingAddress->setCountryCode('DE');
        $billingAddress->setPhone('123456789');
        $billingAddress->setCity('Schöppingen');
        $billingAddress->setLine1('Ebbinghoff 10');
        $billingAddress->setPostalCode('48624');
        $billingAddress->setState('NW');

        $payerInfo->setBillingAddress($billingAddress);

        return $payerInfo;
    }
}
