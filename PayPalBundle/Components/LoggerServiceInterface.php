<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components;

interface LoggerServiceInterface
{
    /**
     * Adds a debug statement to the logfile.
     *
     * @param string       $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function debug($message, array $context = []);

    /**
     * Adds a notification to the logfile.
     *
     * @param string       $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function notify($message, array $context = []);

    /**
     * Adds a warning to the logfile.
     *
     * @param string       $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function warning($message, array $context = []);

    /**
     * Adds an error to the logfile.
     *
     * @param string       $message
     * @param array<mixed> $context
     *
     * @return void
     */
    public function error($message, array $context = []);
}
