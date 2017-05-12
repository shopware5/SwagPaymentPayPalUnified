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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;

class ValidationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_valid_product_price()
    {
        $productPrice = 134.99;

        $priceValid = $this->getValidtionService()->validatePrice($productPrice);

        $this->assertTrue($priceValid);
    }

    public function test_invalid_product_price()
    {
        $productPrice = 34.99;

        $priceValid = $this->getValidtionService()->validatePrice($productPrice);

        $this->assertFalse($priceValid);
    }

    /**
     * @return ValidationService
     */
    private function getValidtionService()
    {
        return new ValidationService();
    }
}
