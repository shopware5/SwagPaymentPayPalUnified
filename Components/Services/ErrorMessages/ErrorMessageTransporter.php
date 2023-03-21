<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ErrorMessages;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Uuid;

class ErrorMessageTransporter
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(DependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @param string $errorName
     * @param string $errorMessage
     *
     * @return string
     */
    public function setErrorMessageToSession($errorName, $errorMessage)
    {
        $errorKey = Uuid::generateUuid();

        $this->dependencyProvider->getSession()->offsetSet($errorKey, $this->createErrorMessageObject($errorName, $errorMessage)->toArray());

        return $errorKey;
    }

    /**
     * @param string $errorKey
     *
     * @return ErrorMessage
     */
    public function getErrorMessageFromSession($errorKey)
    {
        $session = $this->dependencyProvider->getSession();
        $errorName = '';
        $errorMessage = '';

        if (!$session->offsetExists($errorKey)) {
            return $this->createErrorMessageObject($errorName, $errorMessage);
        }

        $sessionValues = $session->offsetGet($errorKey);
        if (isset($sessionValues[ErrorMessage::ERROR_NAME_KEY])) {
            $errorName = $sessionValues[ErrorMessage::ERROR_NAME_KEY];
        }

        if (isset($sessionValues[ErrorMessage::ERROR_MESSAGE_KEY])) {
            $errorMessage = $sessionValues[ErrorMessage::ERROR_MESSAGE_KEY];
        }

        $session->offsetUnset($errorKey);

        return $this->createErrorMessageObject($errorName, $errorMessage);
    }

    /**
     * @param string $errorName
     * @param string $errorMessage
     *
     * @return ErrorMessage
     */
    private function createErrorMessageObject($errorName, $errorMessage)
    {
        return new ErrorMessage($errorName, $errorMessage);
    }
}
