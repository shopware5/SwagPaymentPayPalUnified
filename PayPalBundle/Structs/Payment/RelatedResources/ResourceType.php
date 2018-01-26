<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class ResourceType
{
    const SALE = 'sale';
    const REFUND = 'refund';
    const AUTHORIZATION = 'authorization';
    const ORDER = 'order';
    const CAPTURE = 'capture';
}
