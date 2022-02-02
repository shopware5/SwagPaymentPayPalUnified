<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\DependencyInjection;

class OrderHandlerCompilerPass extends AbstractFactoryCompilerPass
{
    public function getFactoryId()
    {
        return 'paypal_unified.order_factory';
    }

    public function getFactoryTag()
    {
        return 'paypal_unified.order_factory_handler';
    }
}
