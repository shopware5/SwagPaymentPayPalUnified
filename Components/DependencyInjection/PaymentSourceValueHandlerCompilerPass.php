<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\DependencyInjection;

class PaymentSourceValueHandlerCompilerPass extends AbstractFactoryCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function getFactoryId()
    {
        return 'paypal_unified.payment_source_value_factory';
    }

    /**
     * {@inheritdoc}
     */
    public function getFactoryTag()
    {
        return 'paypal_unified.payment_source_value_handler';
    }
}
