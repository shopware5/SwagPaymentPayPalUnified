<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\PayPalBundle\Components\Patches;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PayerInfoPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;

class PayerInfoPatchTest extends TestCase
{
    public function test_getPath()
    {
        $patch = new PayerInfoPatch($this->getPayerInfo());

        static::assertEquals('/payer/payer_info', $patch->getPath());
    }

    public function test_getOperation()
    {
        $patch = new PayerInfoPatch($this->getPayerInfo());

        static::assertEquals('replace', $patch->getOperation());
    }

    public function test_getValue()
    {
        $value = (new PayerInfoPatch($this->getPayerInfo()))->getValue();

        //Payer info
        static::assertCount(7, $value);
        static::assertEquals('123456789', $value['phone']);
        static::assertEquals('test@example.com', $value['email']);
        static::assertEquals('Firstname', $value['first_name']);
        static::assertEquals('Lastname', $value['last_name']);
        static::assertEquals('DE', $value['country_code']);

        //Billing address
        static::assertEquals('DE', $value['billing_address']['country_code']);
        static::assertEquals('123456789', $value['billing_address']['phone']);
        static::assertEquals('Schöppingen', $value['billing_address']['city']);
        static::assertEquals('Ebbinghoff 10', $value['billing_address']['line1']);
        static::assertEquals('48624', $value['billing_address']['postal_code']);
        static::assertEquals('NW', $value['billing_address']['state']);
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
