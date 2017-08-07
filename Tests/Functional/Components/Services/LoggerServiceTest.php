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

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class LoggerServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;

    public function test_warning_returns_without_settings()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $loggerService->warning('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertEmpty($lastLine);
    }

    public function test_warning_returns_without_required_log_level()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $this->insertTestSettings(2);

        $loggerService->warning('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertEmpty($lastLine);
    }

    public function test_warning_adds_line()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $this->insertTestSettings();

        $loggerService->warning('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertContains('Test message', $lastLine);
    }

    public function test_notify_returns_without_settings()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $loggerService->notify('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertEmpty($lastLine);
    }

    public function test_notify_returns_without_required_log_level()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $this->insertTestSettings(2);

        $loggerService->notify('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertEmpty($lastLine);
    }

    public function test_notify_adds_line()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $this->insertTestSettings();

        $loggerService->notify('Test message');

        $lastLine = $this->getLastLine($fileName);
        $this->assertContains('Test message', $lastLine);
    }

    public function test_error_adds_line()
    {
        $fileName = $this->getLogfile();

        //Reset the logfile
        file_put_contents($fileName, '');

        /** @var LoggerServiceInterface $loggerService */
        $loggerService = Shopware()->Container()->get('paypal_unified.logger_service');

        $loggerService->error('A very fatal error');

        $lastLine = $this->getLastLine($fileName);
        $this->assertContains('A very fatal error', $lastLine);
    }

    /**
     * @param int $logLevel
     */
    private function insertTestSettings($logLevel = 1)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'logLevel' => $logLevel,
        ]);
    }

    /**
     * @return string
     */
    private function getLogfile()
    {
        $env = Shopware()->Container()->getParameter('kernel.environment');

        $fileName = __DIR__ . '/../../../../../../../var/log/plugin_' . $env . '-' . date('Y-m-d') . '.log';

        return $fileName;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getLastLine($file)
    {
        $lines = explode("\n", file_get_contents($file));
        $lineCount = count($lines);

        return $lines[$lineCount - 2]; //the actual last line is blank
    }
}
