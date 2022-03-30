<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

class LoggerService implements LoggerServiceInterface
{
    const MESSAGE_PREFIX = 'PayPal: ';

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $baseLogger)
    {
        $this->logger = $baseLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = [])
    {
        $this->logger->debug($this->createFinalMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = [])
    {
        $this->logger->warning($this->createFinalMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notify($message, array $context = [])
    {
        $this->logger->notice($this->createFinalMessage($message), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = [])
    {
        $this->logger->error($this->createFinalMessage($message), $context);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function createFinalMessage($message)
    {
        return self::MESSAGE_PREFIX . $message;
    }
}
