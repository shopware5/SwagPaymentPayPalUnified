<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm;

use Symfony\Component\Validator\Constraints\Collection;
use UnexpectedValueException;

class ValidatorFactory
{
    /**
     * @var array<ValidatorHandlerInterface>
     */
    private $validatorHandler = [];

    /**
     * @param string $paymentType
     *
     * @return Collection
     */
    public function createValidator($paymentType)
    {
        $validatorHandler = $this->getHandler($paymentType);

        return $validatorHandler->createValidator($paymentType);
    }

    /**
     * @return void
     */
    public function addHandler(ValidatorHandlerInterface $valueHandler)
    {
        $this->validatorHandler[] = $valueHandler;
    }

    /**
     * @param string $paymentType
     *
     * @return ValidatorHandlerInterface
     */
    private function getHandler($paymentType)
    {
        foreach ($this->validatorHandler as $handler) {
            if ($handler->supports($paymentType)) {
                return $handler;
            }
        }

        throw new UnexpectedValueException(
            sprintf('Validator handler for payment type "%s" not found', $paymentType)
        );
    }
}
