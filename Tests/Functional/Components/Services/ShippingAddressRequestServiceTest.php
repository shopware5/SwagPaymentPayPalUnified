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

use SwagPaymentPayPalUnified\Components\Services\ShippingAddressRequestService;

class ShippingAddressRequestServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ADDRESS_CITY = 'TEST_CITY';
    const TEST_ADDRESS_STREET = 'TEST_STREET';
    const TEST_ADDRESS_ZIPCODE = 'TEST_ZIPCODE';
    const TEST_ADDRESS_FIRSTNAME = 'TEST_FIRST_NAME';
    const TEST_ADDRESS_LASTNAME = 'TEST_LAST_NAME';
    const TEST_ADDRESS_COUNTRY = 'DE';
    const TEST_ADDRESS_STATE = 'NW';

    public function test_service_available()
    {
        $this->assertNotNull(Shopware()->Container()->get('paypal_unified.shipping_address_request_service'));
    }

    public function test_getAddress_success()
    {
        $testAddressData = [
            'shippingaddress' => [
                'city' => self::TEST_ADDRESS_CITY,
                'street' => self::TEST_ADDRESS_STREET,
                'zipcode' => self::TEST_ADDRESS_ZIPCODE,
                'firstname' => self::TEST_ADDRESS_FIRSTNAME,
                'lastname' => self::TEST_ADDRESS_LASTNAME,
            ],
            'additional' => [
                'countryShipping' => [
                    'countryiso' => self::TEST_ADDRESS_COUNTRY,
                ],
            ],
        ];

        /** @var ShippingAddressRequestService $addressService */
        $addressService = Shopware()->Container()->get('paypal_unified.shipping_address_request_service');
        $testAddress = $addressService->getAddress($testAddressData);

        $this->assertNotNull($testAddress);
        $this->assertEquals(self::TEST_ADDRESS_CITY, $testAddress->getCity());
        $this->assertEquals(self::TEST_ADDRESS_COUNTRY, $testAddress->getCountryCode());
        $this->assertEquals(self::TEST_ADDRESS_FIRSTNAME . ' ' . self::TEST_ADDRESS_LASTNAME, $testAddress->getRecipientName());
        $this->assertEquals(self::TEST_ADDRESS_ZIPCODE, $testAddress->getPostalCode());
        $this->assertEquals(self::TEST_ADDRESS_STREET, $testAddress->getLine1());
        $this->assertNull($testAddress->getState());
    }

    public function test_getPatch_attach_state()
    {
        $testAddressData = [
            'shippingaddress' => [
                'city' => self::TEST_ADDRESS_CITY,
                'street' => self::TEST_ADDRESS_STREET,
                'zipcode' => self::TEST_ADDRESS_ZIPCODE,
                'firstname' => self::TEST_ADDRESS_FIRSTNAME,
                'lastname' => self::TEST_ADDRESS_LASTNAME,
            ],
            'additional' => [
                'countryShipping' => [
                    'countryiso' => self::TEST_ADDRESS_COUNTRY,
                ],
                'stateShipping' => [
                    'shortcode' => self::TEST_ADDRESS_STATE,
                ],
            ],
        ];

        /** @var ShippingAddressRequestService $addressService */
        $addressService = Shopware()->Container()->get('paypal_unified.shipping_address_request_service');
        $testAddress = $addressService->getAddress($testAddressData);

        $this->assertEquals(self::TEST_ADDRESS_STATE, $testAddress->getState());
    }
}
