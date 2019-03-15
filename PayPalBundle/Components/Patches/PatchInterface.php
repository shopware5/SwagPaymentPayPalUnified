<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

interface PatchInterface
{
    const OPERATION_ADD = 'add';
    const OPERATION_REPLACE = 'replace';

    /**
     * Returns the operation that should be triggered.
     *
     * @return string
     */
    public function getOperation();

    /**
     * Returns the path for the patch call.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the value that should be transferred to PayPal
     */
    public function getValue();
}
