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

namespace SwagPaymentPayPalUnified\Tests\Functional\Bundle\AccountBundle\Service\Validator;

use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface as AddressValInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Address;
use SwagPaymentPayPalUnified\Bundle\AccountBundle\Service\Validator\AddressValidatorDecorator as AddressDecorator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class AddressValidatorDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function test_construct()
    {
        $validator = new AddressDecorator(new AddressValidatorMock(), Shopware()->Container()->get('front'));
        $this->assertNotNull($validator);
    }

    public function test_isValid_inner_validator()
    {
        $validator = new AddressDecorator(new AddressValidatorMock(), Shopware()->Container()->get('front'));
        $this->assertTrue($validator->isValid(new Address()));
    }

    public function test_validate_return_without_request()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();

        $front = new FrontMock();

        $validator = new AddressDecorator(new AddressValidatorMock(), $front);
        $this->assertNull($validator->validate(new Address()));
    }

    public function test_validate_return_with_wrong_controller_name()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('fake');
        $front = new FrontMock();
        $front->setRequest($request);

        $validator = new AddressDecorator(new AddressValidatorMock(), $front);
        $this->assertNull($validator->validate(new Address()));
    }

    public function test_validate_throw_validation_exception_country()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('PaypalUnifiedExpressCheckout');
        $front = new FrontMock();
        $front->setRequest($request);

        $validator = new AddressDecorator(new AddressValidatorWithStateException(), $front);

        $this->expectException(\Shopware\Components\Api\Exception\ValidationException::class);
        $validator->validate(new Address());
    }

    public function test_validate_throw_no_validation_exception()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setControllerName('PaypalUnifiedExpressCheckout');
        $front = new FrontMock();
        $front->setRequest($request);

        $validator = new AddressDecorator(new AddressValidatorMock(), $front);

        $this->assertNull($validator->validate(new Address()));
    }
}

class AddressValidatorMock implements AddressValInterface
{
    /**
     * @param Address $address
     *
     * @throws ValidationException
     */
    public function validate(Address $address)
    {
        // TODO: Implement validate() method.
    }

    /**
     * @param Address $address
     *
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
     * @param Address $address
     *
     * @throws ValidationException
     */
    public function validate(Address $address)
    {
        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation('State invalid.', '', [], '', '', 'state'));

        throw new ValidationException($violations);
    }

    /**
     * @param Address $address
     *
     * @return bool
     */
    public function isValid(Address $address)
    {
        return true;
    }
}

class FrontMock extends \Enlight_Controller_Front
{
    /**
     * @var \Enlight_Controller_Request_RequestTestCase
     */
    protected $request;

    public function __construct()
    {
    }

    public function Request()
    {
        return $this->request;
    }

    public function setRequest(\Enlight_Controller_Request_RequestTestCase $request)
    {
        $this->request = $request;
    }
}
