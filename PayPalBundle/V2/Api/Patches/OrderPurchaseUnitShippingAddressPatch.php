<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;

class OrderPurchaseUnitShippingAddressPatch extends Patch
{
    const PATH = "/purchase_units/@reference_id=='default'/shipping/address";
}
