<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm;

interface ValueHandlerInterface
{
    /**
     * @param string $paymentType
     *
     * @return bool
     */
    public function supports($paymentType);

    /**
     * @param array<mixed> $basket
     * @param array<mixed> $user
     *
     * @return array<string,mixed>
     */
    public function createValue(array $basket, array $user);
}
