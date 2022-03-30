<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm;

use Symfony\Component\Validator\Constraints\Collection as ValidatorConstraintsCollection;

interface ValidatorHandlerInterface
{
    /**
     * @param string $paymentType
     *
     * @return bool
     */
    public function supports($paymentType);

    /**
     * @param string $paymentType
     *
     * @return ValidatorConstraintsCollection;
     */
    public function createValidator($paymentType);
}
