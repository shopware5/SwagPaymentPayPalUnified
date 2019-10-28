<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Resources\SaleResource;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\GetSale;

class SaleResourceMock extends SaleResource
{
    public function __construct()
    {
    }

    public function get($saleId)
    {
        return GetSale::get();
    }
}
