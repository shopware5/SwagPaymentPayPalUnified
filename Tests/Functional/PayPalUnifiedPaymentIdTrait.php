<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

trait PayPalUnifiedPaymentIdTrait
{
    /**
     * @return int
     */
    protected function getUnifiedPaymentId()
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $modelManager = Shopware()->Container()->get('models');

        return (new PaymentMethodProvider($connection, $modelManager))->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
    }
}
