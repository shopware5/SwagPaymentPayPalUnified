<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

interface ExceptionHandlerServiceInterface
{
    /**
     * @param string $currentAction
     *
     * @return PayPalApiException The error message and name extracted from the exception
     */
    public function handle(\Exception $e, $currentAction);
}
