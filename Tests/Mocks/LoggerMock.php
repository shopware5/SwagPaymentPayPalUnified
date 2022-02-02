<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

class LoggerMock implements LoggerServiceInterface
{
    /**
     * @var array
     */
    private $errors;

    public function __construct()
    {
        $this->errors = [];
    }

    /**
     * {@inheritdoc}
     */
    public function notify($message, array $context = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->errors[$message] = $context;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function debug($message, array $context = [])
    {
    }
}
