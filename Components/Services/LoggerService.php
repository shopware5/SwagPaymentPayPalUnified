<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
            $finalMessage = '[Warning] PayPal Products: ' . $message;
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
            $finalMessage = '[Info] PayPal Products: ' . $message;
            $this->logger->addInfo($finalMessage, $context);
        }
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = [])
    {
        $finalMessage = '[Error] PayPal Products: ' . $message;
        $this->logger->addInfo($finalMessage, $context);
    }
}
