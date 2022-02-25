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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class LoggerService implements LoggerServiceInterface
{
    const MESSAGE_PREFIX = 'PayPal: ';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SettingsServiceInterface
     */
    private $settings;

    /**
     * @var int
     */
    private $logLevel = self::NORMAL_LOG_LEVEL;

    public function __construct(Logger $baseLogger, SettingsServiceInterface $settings)
    {
        $this->logger = $baseLogger;
        $this->settings = $settings;

        if ($this->settings->hasSettings()) {
            $this->logLevel = (int) $this->settings->get(SettingsServiceInterface::SETTING_GENERAL_LOG_LEVEL);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = [])
    {
        if ($this->logLevel === self::DEBUG_LOG_LEVEL) {
            $this->logger->debug($this->createFinalMessage($message), $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = [])
    {
        if ($this->logLevel >= self::EXTENDED_LOG_LEVEL) {
            $this->logger->warning($this->createFinalMessage($message), $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function notify($message, array $context = [])
    {
        if ($this->logLevel >= self::EXTENDED_LOG_LEVEL) {
            $this->logger->notice($this->createFinalMessage($message), $context);
        }
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
