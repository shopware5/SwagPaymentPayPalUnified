<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\RiskManagement\Apm\Validator;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm\Validator\SofortValidatorHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SofortValidatorHandlerTest extends TestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testCreateValidatorShouldNotContainItalyAsCountry()
    {
        $validatorConstraintsCollection = $this->getValidator()->createValidator(PaymentType::APM_SOFORT);

        /** @var ValidatorInterface $validator */
        $validator = $this->getContainer()->get('validator');
        $violationList = $validator->validate(
            ['country' => 'IT', 'currency' => 'EUR', 'amount' => 12.99],
            $validatorConstraintsCollection,
            'euro'
        );

        static::assertCount(1, $violationList);

        $violation = $violationList[0];
        static::assertInstanceOf(ConstraintViolationInterface::class, $violation);

        static::assertSame('The value you selected is not a valid choice.', $violation->getMessage());
        static::assertSame('IT', $violation->getInvalidValue());
    }

    /**
     * @return SofortValidatorHandler
     */
    private function getValidator()
    {
        return new SofortValidatorHandler();
    }
}
