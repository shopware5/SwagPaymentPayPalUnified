<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Bundle\AccountBundle\Service\Validator;

use Enlight_Controller_Front;
use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface as AddressValInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Address;
use SwagPaymentPayPalUnified\Bundle\AccountBundle\Service\Validator\AddressValidatorDecorator as AddressDecorator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class AddressValidatorDecoratorTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstruct()
    {
        $validator = new AddressDecorator(new AddressValidatorMock(), Shopware()->Container()->get('front'));
        static::assertNotNull($validator);
    }

    /**
     * @return void
     */
    public function testIsValidInnerValidator()
    {
        $validator = new AddressDecorator(new AddressValidatorMock(), Shopware()->Container()->get('front'));
        static::assertTrue($validator->isValid(new Address()));
    }

    /**
     * @return void
     */
    public function testValidateReturnWithoutRequest()
    {
        $front = new FrontMock();

        $innerAddressValidator = $this->createInnerAddressValidator();

        $validator = new AddressDecorator($innerAddressValidator, $front);

        $validator->validate(new Address());
    }

    /**
     * @return void
     */
    public function testValidateReturnWithWrongControllerName()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $front = new FrontMock();
        $front->setRequest($request);

        $innerAddressValidator = $this->createInnerAddressValidator();

        $validator = new AddressDecorator($innerAddressValidator, $front);
        $validator->validate(new Address());
    }

    /**
     * @return void
     */
    public function testValidateThrowValidationExceptionCountry()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('PaypalUnifiedV2ExpressCheckout');
        $front = new FrontMock();
        $front->setRequest($request);

        $validator = new AddressDecorator(new AddressValidatorWithStateException(), $front);

        $this->expectException(ValidationException::class);
        $validator->validate(new Address());
    }

    /**
     * @return void
     */
    public function testValidateThrowNoValidationException()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('PaypalUnifiedV2ExpressCheckout');
        $front = new FrontMock();
        $front->setRequest($request);

        $innerAddressValidator = $this->createInnerAddressValidator();

        $validator = new AddressDecorator($innerAddressValidator, $front);

        $validator->validate(new Address());
    }

    /**
     * @return AddressValInterface&MockObject
     */
    private function createInnerAddressValidator()
    {
        $innerAddressValidator = $this->createMock(AddressValInterface::class);
        $innerAddressValidator->expects(static::once())->method('validate');

        return $innerAddressValidator;
    }
}

class AddressValidatorMock implements AddressValInterface
{
    public function validate(Address $address)
    {
    }

    /**
     * @return bool
     */
    public function isValid(Address $address)
    {
        return true;
    }
}

class AddressValidatorWithStateException implements AddressValInterface
{
    /**
     * @throws ValidationException
     */
    public function validate(Address $address)
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation('State invalid.', '', [], '', '', 'state'));

        throw new ValidationException($violations);
    }

    /**
     * @return bool
     */
    public function isValid(Address $address)
    {
        return true;
    }
}

class FrontMock extends Enlight_Controller_Front
{
    /**
     * @var Enlight_Controller_Request_RequestTestCase
     */
    protected $request;

    public function __construct()
    {
    }

    /**
     * @param Enlight_Controller_Request_RequestTestCase $request
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }
}
