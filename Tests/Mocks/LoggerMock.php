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
     * @var array<string, mixed>
     */
    private $errors;

    /**
     * @var array<string, mixed>
     */
    private $debug;

    public function __construct()
    {
        $this->errors = [];
        $this->debug = [];
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
     * @return array<string, mixed>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string                   $message
     * @param array<int|string, mixed> $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->debug[$message] = $context;
    }

    /**
     * @return array<string, array<int,mixed>>
     */
    public function getDebug()
    {
        return $this->debug;
    }
}
