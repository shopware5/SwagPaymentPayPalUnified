<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Exception;

class PayPalApiException extends \Exception
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string $message
     */
    public function __construct($name, $message)
    {
        $this->name = $name;

        parent::__construct($message);
    }

    /**
     * @return string|int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCompleteMessage()
    {
        return $this->getMessage() . ' [' . $this->getName() . ']';
    }
}
