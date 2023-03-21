<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ErrorMessages;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessage;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessageTransporter;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class ErrorMessageTransporterTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    const ERROR_NAME = 'AnyErrorName';

    const ERROR_MESSAGE = 'AnyErrorMessage';

    /**
     * @return void
     */
    public function testSetErrorMessageToSession()
    {
        $errorMessageTransporter = $this->createErrorMessageTransporter();

        $methodResult = $errorMessageTransporter->setErrorMessageToSession(self::ERROR_NAME, self::ERROR_MESSAGE);

        $sessionResult = $this->getContainer()->get('session')->offsetGet($methodResult);

        static::assertSame($sessionResult[ErrorMessage::ERROR_NAME_KEY], self::ERROR_NAME);
        static::assertSame($sessionResult[ErrorMessage::ERROR_MESSAGE_KEY], self::ERROR_MESSAGE);
    }

    /**
     * @return void
     */
    public function testGetErrorMessageFromSession()
    {
        $errorMessageTransporter = $this->createErrorMessageTransporter();

        $messageSessionKey = $errorMessageTransporter->setErrorMessageToSession(self::ERROR_NAME, self::ERROR_MESSAGE);

        $session = $this->getContainer()->get('session');

        $sessionResult = $session->offsetGet($messageSessionKey);

        static::assertSame(self::ERROR_NAME, $sessionResult[ErrorMessage::ERROR_NAME_KEY]);
        static::assertSame(self::ERROR_MESSAGE, $sessionResult[ErrorMessage::ERROR_MESSAGE_KEY]);

        $result = $errorMessageTransporter->getErrorMessageFromSession($messageSessionKey);

        static::assertInstanceOf(ErrorMessage::class, $result);
        static::assertSame(self::ERROR_NAME, $result->getErrorName());
        static::assertSame(self::ERROR_MESSAGE, $result->getErrorMessage());
        static::assertFalse($session->offsetExists($messageSessionKey));
    }

    /**
     * @return ErrorMessageTransporter
     */
    private function createErrorMessageTransporter()
    {
        return new ErrorMessageTransporter($this->getContainer()->get('paypal_unified.dependency_provider'));
    }
}
