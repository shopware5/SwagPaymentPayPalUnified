<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class LoggerServiceTest extends TestCase
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
        static::assertEmpty($lastLine);
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
        static::assertEmpty($lastLine);
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

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('Test message', $lastLine);

            return;
        }
        static::assertContains('Test message', $lastLine);
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
        static::assertEmpty($lastLine);
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
        static::assertEmpty($lastLine);
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

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('Test message', $lastLine);

            return;
        }
        static::assertContains('Test message', $lastLine);
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

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('A very fatal error', $lastLine);

            return;
        }
        static::assertContains('A very fatal error', $lastLine);
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
