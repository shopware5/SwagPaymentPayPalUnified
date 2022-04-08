<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class LoggerServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ContainerTrait;

    public function testWarningAddsLine()
    {
        $fileName = $this->getLogfile();

        // Reset the logfile
        \file_put_contents($fileName, '');

        $this->insertTestSettings();

        $this->getContainer()->reset('paypal_unified.logger_service');
        $loggerService = $this->getContainer()->get('paypal_unified.logger_service');

        $loggerService->warning('Test message');

        $lastLine = $this->getLastLine($fileName);

        static::assertNotNull($lastLine);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('Test message', $lastLine);

            return;
        }
        static::assertContains('Test message', $lastLine);
    }

    public function testNotifyAddsLine()
    {
        $fileName = $this->getLogfile();

        // Reset the logfile
        \file_put_contents($fileName, '');

        $this->insertTestSettings();

        $this->getContainer()->reset('paypal_unified.logger_service');
        $loggerService = $this->getContainer()->get('paypal_unified.logger_service');

        $loggerService->notify('Test message');

        $lastLine = $this->getLastLine($fileName);

        static::assertNotNull($lastLine);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('Test message', $lastLine);

            return;
        }
        static::assertContains('Test message', $lastLine);
    }

    public function testErrorAddsLine()
    {
        $fileName = $this->getLogfile();

        // Reset the logfile
        \file_put_contents($fileName, '');

        $loggerService = $this->getContainer()->get('paypal_unified.logger_service');

        $loggerService->error('A very fatal error');

        $lastLine = $this->getLastLine($fileName);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('A very fatal error', $lastLine);

            return;
        }
        static::assertContains('A very fatal error', $lastLine);
    }

    /**
     * @return void
     */
    private function insertTestSettings()
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
        ]);
    }

    /**
     * @return string
     */
    private function getLogfile()
    {
        $env = $this->getContainer()->getParameter('kernel.environment');

        return __DIR__ . '/../../../../../../../var/log/plugin_' . $env . '-' . \date('Y-m-d') . '.log';
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getLastLine($file)
    {
        $content = \file_get_contents($file);
        static::assertTrue(\is_string($content));
        $lines = \explode("\n", $content);
        static::assertTrue(\is_array($lines));
        $lineCount = \count($lines);

        return $lines[$lineCount - 2]; // the actual last line is blank
    }
}
