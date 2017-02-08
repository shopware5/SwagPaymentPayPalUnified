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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\Components\Services\PaymentAddressPatchService;

class PaymentAddressPatchServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ADDRESS_CITY = 'TEST_CITY';
    const TEST_ADDRESS_STREET = 'TEST_STREET';
    const TEST_ADDRESS_ZIPCODE = 'TEST_ZIPCODE';
    const TEST_ADDRESS_FIRSTNAME = 'TEST_FIRST_NAME';
    const TEST_ADDRESS_LASTNAME = 'TEST_LAST_NAME';
    const TEST_ADDRESS_COUNTRYID = 2;
    const TEST_ADDRESS_STATEID = 3;
    const TEST_PATCH_COUNTRYCODE = 'DE';
    const TEST_PATCH_STATECODE = 'NW';

    public function test_service_available()
    {
        $this->assertNotNull(Shopware()->Container()->get('paypal_unified.payment_address_patch_service'));
    }

    public function test_getPatch_success()
    {
        $testAddressData = [
            'city' => self::TEST_ADDRESS_CITY,
            'street' => self::TEST_ADDRESS_STREET,
            'zipcode' => self::TEST_ADDRESS_ZIPCODE,
            'firstname' => self::TEST_ADDRESS_FIRSTNAME,
            'lastname' => self::TEST_ADDRESS_LASTNAME,
            'countryId' => self::TEST_ADDRESS_COUNTRYID
        ];

        /** @var PaymentAddressPatchService $patchService */
        $patchService = Shopware()->Container()->get('paypal_unified.payment_address_patch_service');
        $testAddressPatch = $patchService->getPatch($testAddressData)->getValue();

        $this->assertNotNull($testAddressPatch);
        $this->assertEquals(self::TEST_ADDRESS_CITY, $testAddressPatch['city']);
        $this->assertEquals(self::TEST_PATCH_COUNTRYCODE, $testAddressPatch['country_code']);
        $this->assertEquals(self::TEST_ADDRESS_FIRSTNAME . ' ' . self::TEST_ADDRESS_LASTNAME, $testAddressPatch['recipient_name']);
        $this->assertEquals(self::TEST_ADDRESS_ZIPCODE, $testAddressPatch['postal_code']);
        $this->assertEquals(self::TEST_ADDRESS_STREET, $testAddressPatch['line1']);
        $this->assertNull($testAddressPatch['state']);
    }

    public function test_getPatch_exception()
    {
        $testAddressData = [
            'countryId' => 1000
        ];

        /** @var PaymentAddressPatchService $patchService */
        $patchService = Shopware()->Container()->get('paypal_unified.payment_address_patch_service');
        $this->expectException(\Exception::class);
        $patchService->getPatch($testAddressData)->getValue();
    }

    public function test_getPatch_attach_state()
    {
        $testAddressData = [
            'city' => self::TEST_ADDRESS_CITY,
            'street' => self::TEST_ADDRESS_STREET,
            'zipcode' => self::TEST_ADDRESS_ZIPCODE,
            'firstname' => self::TEST_ADDRESS_FIRSTNAME,
            'lastname' => self::TEST_ADDRESS_LASTNAME,
            'countryId' => self::TEST_ADDRESS_COUNTRYID,
            'stateId' => self::TEST_ADDRESS_STATEID
        ];

        /** @var PaymentAddressPatchService $patchService */
        $patchService = Shopware()->Container()->get('paypal_unified.payment_address_patch_service');
        $testAddressPatch = $patchService->getPatch($testAddressData)->getValue();

        $this->assertEquals(self::TEST_PATCH_STATECODE, $testAddressPatch['state']);
    }
}
