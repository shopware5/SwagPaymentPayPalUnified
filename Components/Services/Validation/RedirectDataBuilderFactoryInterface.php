<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

interface RedirectDataBuilderFactoryInterface
{
    /**
     * @return RedirectDataBuilder
     */
    public function createRedirectDataBuilder();
}
