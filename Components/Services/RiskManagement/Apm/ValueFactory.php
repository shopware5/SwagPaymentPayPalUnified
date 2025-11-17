<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm;

use UnexpectedValueException;

class ValueFactory
{
    /**
     * @var array<ValueHandlerInterface>
     */
    private $valueHandler = [];

    /**
     * @param string       $paymentType
     * @param array<mixed> $basket
     * @param array<mixed> $user
     *
     * @return array<string,mixed>
     */
    public function createValue($paymentType, array $basket, array $user)
    {
        $handler = $this->getHandler($paymentType);

        return $handler->createValue($basket, $user);
    }

    /**
     * @return void
     */
    public function addHandler(ValueHandlerInterface $valueHandler)
    {
        $this->valueHandler[] = $valueHandler;
    }

    /**
     * @param string $paymentType
     *
     * @return ValueHandlerInterface
     */
    private function getHandler($paymentType)
    {
        foreach ($this->valueHandler as $handler) {
            if ($handler->supports($paymentType)) {
                return $handler;
            }
        }

        throw new UnexpectedValueException(
            sprintf('Value handler for payment type "%s" not found', $paymentType)
        );
    }
}
