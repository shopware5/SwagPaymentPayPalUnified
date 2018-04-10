<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;

class PaymentResourceMock extends PaymentResource
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function patch($paymentId, array $patches)
    {
        throw new RequestException('patch exception');
    }
}
