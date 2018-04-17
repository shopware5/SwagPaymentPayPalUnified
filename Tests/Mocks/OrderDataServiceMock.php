<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\Components\Services\OrderDataService;

class OrderDataServiceMock extends OrderDataService
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId($orderNumber)
    {
        return 'testTransactionId';
    }
}
