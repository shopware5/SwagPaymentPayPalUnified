<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Bundle\AccountBundle\Service\Validator;

use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Address;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class AddressValidatorDecorator implements AddressValidatorInterface
{
    /**
     * @var AddressValidatorInterface
     */
    private $innerValidator;

    /**
     * @var \Enlight_Controller_Front
     */
    private $front;

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
        $request = $this->front->Request();
        if (!$request) {
            $this->innerValidator->validate($address);

            return;
        }

        $controllerName = $request->getControllerName();
        $payPalController = ['paypal_unified_express_checkout', 'paypalunifiedexpresscheckout', 'paypalunifiedv2expresscheckout'];
        if (!\in_array(\strtolower($controllerName), $payPalController, true)) {
            $this->innerValidator->validate($address);

            return;
        }

        try {
            $this->innerValidator->validate($address);
        } catch (ValidationException $exception) {
            /** @var ConstraintViolationList $violations */
            $violations = $exception->getViolations();

            // these values are not always provided by PayPal, but might be required due to the shop settings
            // the customer will have to adjust his address on the confirm page, per default Shopware shows a hint
            $allowedViolations = ['state', 'phone', 'additionalAddressLine1', 'additionalAddressLine2'];

            /** @var ConstraintViolationInterface $violation */
            foreach ($violations->getIterator() as $violation) {
                if (!\in_array($violation->getPropertyPath(), $allowedViolations, true)) {
                    throw $exception;
                }
            }
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
