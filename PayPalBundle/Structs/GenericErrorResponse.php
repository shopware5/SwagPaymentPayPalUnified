<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

class GenericErrorResponse
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $errorDescription;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    /**
     * @param string $errorDescription
     */
    public function setErrorDescription($errorDescription)
    {
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return GenericErrorResponse
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->setError($data['error']);
        $result->setErrorDescription($data['error_description']);

        return $result;
    }
}
