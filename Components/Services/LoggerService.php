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
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SettingsServiceInterface
     */
    private $settings;

    /**
     * @param Logger                   $baseLogger
     * @param SettingsServiceInterface $settings
     */
    public function __construct(Logger $baseLogger, SettingsServiceInterface $settings)
    {
        $this->logger = $baseLogger;
        $this->settings = $settings;
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function warning($message, array $context = [])
    {
        if (!$this->settings->hasSettings()) {
            return;
        }

        if ((int) $this->settings->get('log_level') === 1) {
            $finalMessage = 'PayPal Products: ' . $message;
            $this->logger->addWarning($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function notify($message, array $context = [])
    {
        if (!$this->settings->hasSettings()) {
            return;
        }

        if ((int) $this->settings->get('log_level') === 1) {
            $finalMessage = 'PayPal Products: ' . $message;
            $this->logger->addInfo($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = [])
    {
        $finalMessage = 'PayPal Products: ' . $message;
        $this->logger->addError($finalMessage, $context);
    }
}
