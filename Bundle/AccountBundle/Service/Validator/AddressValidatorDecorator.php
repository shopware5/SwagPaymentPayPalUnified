<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Bundle\AccountBundle\Service\Validator;

use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Address;
use Symfony\Component\Validator\ConstraintViolationInterface;

class AddressValidatorDecorator implements AddressValidatorInterface
{
    /** @var AddressValidatorInterface */
    private $innerValidator;

    /**
     * @var \Enlight_Controller_Front
     */
    private $front;

    /**
     * PaypalAddressValidator constructor.
     *
     * @param AddressValidatorInterface $innerValidator
     * @param \Enlight_Controller_Front $front
     */
    public function __construct(AddressValidatorInterface $innerValidator, \Enlight_Controller_Front $front)
    {
        $this->innerValidator = $innerValidator;
        $this->front = $front;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Address $address)
    {
        if (!$this->front->Request() ||
            $this->front->Request()->getControllerName() !== 'PaypalUnifiedExpressCheckout'
        ) {
            $this->innerValidator->validate($address);

            return;
        }

        try {
            $this->innerValidator->validate($address);
        } catch (ValidationException $exception) {
            $violations = $exception->getViolations();

            // these values are not always provided by PayPal, but might be required due to the shop settings
            // the customer will have to adjust his address on the confirm page, per default Shopware shows a hint
            $allowedViolations = ['state', 'phone', 'additionalAddressLine1', 'additionalAddressLine2'];

            /** @var $violation ConstraintViolationInterface */
            foreach ($violations->getIterator() as $violation) {
                if (!in_array($violation->getPropertyPath(), $allowedViolations, true)) {
                    throw $exception;
                }
            }

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Address $address)
    {
        return $this->innerValidator->isValid($address);
    }
}
