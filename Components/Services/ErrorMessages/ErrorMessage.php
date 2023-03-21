<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ErrorMessages;

class ErrorMessage
{
    const ERROR_NAME_KEY = 'paypal_unified_error_name';

    const ERROR_MESSAGE_KEY = 'paypal_unified_error_message';

    /**
     * @var string
     */
    private $errorName;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param string $errorName
     * @param string $errorMessage
     */
    public function __construct($errorName, $errorMessage)
    {
        $this->errorName = $errorName;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getErrorName()
    {
        return $this->errorName;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return array<string,string>
     */
    public function toArray()
    {
        return [
            self::ERROR_NAME_KEY => $this->errorName,
            self::ERROR_MESSAGE_KEY => $this->errorMessage,
        ];
    }
}
