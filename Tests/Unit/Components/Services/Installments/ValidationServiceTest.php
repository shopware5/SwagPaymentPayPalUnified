<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;

class ValidationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_valid_product_price()
    {
        $productPrice = 134.99;

        $priceValid = $this->getValidationService()->validatePrice($productPrice);

        static::assertTrue($priceValid);
    }

    public function test_invalid_product_price()
    {
        $productPrice = 34.99;

        $priceValid = $this->getValidationService()->validatePrice($productPrice);

        static::assertFalse($priceValid);
    }

    /**
     * @return ValidationService
     */
    private function getValidationService()
    {
        return new ValidationService();
    }
}
