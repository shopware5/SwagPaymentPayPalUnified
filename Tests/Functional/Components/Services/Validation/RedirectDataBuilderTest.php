<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Validation;

use Exception;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Exception\PayPalApiException;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessageTransporter;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;

class RedirectDataBuilderTest extends TestCase
{
    const ERROR_KEY = 'aErrorKey';

    /**
     * @return void
     */
    public function testSetCode()
    {
        $code = 12;
        $redirectDataBuilder = $this->getRedirectDataBuilder();

        static::assertNull($redirectDataBuilder->getCode());

        $setCodeResult = $redirectDataBuilder->setCode($code);

        static::assertInstanceOf(RedirectDataBuilder::class, $setCodeResult);
        static::assertSame($code, $redirectDataBuilder->getCode());
    }

    /**
     * @return void
     */
    public function testSetException()
    {
        $exceptionHandlerServiceMock = $this->createExceptionHandlerServiceMock();

        $settingsServiceMock = $this->createSettingsServiceMock();

        $redirectDataBuilder = $this->getRedirectDataBuilder($exceptionHandlerServiceMock, $settingsServiceMock);

        static::assertFalse($redirectDataBuilder->hasException());

        $setExceptionResult = $redirectDataBuilder->setException(new Exception('FooBar'), 'test');
        static::assertInstanceOf(RedirectDataBuilder::class, $setExceptionResult);

        static::assertTrue($redirectDataBuilder->hasException());

        $redirectData = $redirectDataBuilder->getRedirectData();

        static::assertArrayHasKey(RedirectDataBuilder::PAYPAL_UNIFIED_ERROR_KEY, $redirectData);
        static::assertSame(self::ERROR_KEY, $redirectData[RedirectDataBuilder::PAYPAL_UNIFIED_ERROR_KEY]);
    }

    /**
     * @return void
     */
    public function testSetRedirectToFinishAction()
    {
        $redirectDataBuilder = $this->getRedirectDataBuilder();
        $redirectDataBuilder->setRedirectToFinishAction();

        $result = $redirectDataBuilder->getRedirectData()['action'];

        static::assertSame('finish', $result);
    }

    /**
     * @return void
     */
    public function testACompleteBuild()
    {
        $code = 12;

        $exceptionHandlerServiceMock = $this->createExceptionHandlerServiceMock();

        $settingsServiceMock = $this->createSettingsServiceMock();

        $redirectDataBuilder = $this->getRedirectDataBuilder($exceptionHandlerServiceMock, $settingsServiceMock);

        static::assertNull($redirectDataBuilder->getCode());
        static::assertFalse($redirectDataBuilder->hasException());

        $redirectDataBuilder->setCode($code)
            ->setException(new Exception('TestException'), 'test')
            ->setRedirectToFinishAction();

        static::assertSame($code, $redirectDataBuilder->getCode());
        static::assertTrue($redirectDataBuilder->hasException());

        $result = $redirectDataBuilder->getRedirectData();

        $expectedResult = [
            'controller' => 'checkout',
            'action' => 'finish',
            'paypal_unified_error_code' => 12,
            RedirectDataBuilder::PAYPAL_UNIFIED_ERROR_KEY => self::ERROR_KEY,
        ];

        static::assertSame($expectedResult, $result);
    }

    /**
     * @param ExceptionHandlerService|null $exceptionHandlerService
     * @param SettingsService|null         $settingsService
     * @param ErrorMessageTransporter      $errorMessageTransporter
     *
     * @return RedirectDataBuilder
     */
    private function getRedirectDataBuilder(
        $exceptionHandlerService = null,
        $settingsService = null,
        $errorMessageTransporter = null
    ) {
        if ($exceptionHandlerService === null) {
            $exceptionHandlerService = $this->getMockBuilder(ExceptionHandlerService::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        if ($settingsService === null) {
            $settingsService = $this->getMockBuilder(SettingsService::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        if ($errorMessageTransporter === null) {
            $errorMessageTransporter = $this->createMock(ErrorMessageTransporter::class);
            $errorMessageTransporter->method('setErrorMessageToSession')->willReturn(self::ERROR_KEY);
        }

        return new RedirectDataBuilder($exceptionHandlerService, $settingsService, $errorMessageTransporter);
    }

    /**
     * @return ExceptionHandlerService
     */
    private function createExceptionHandlerServiceMock()
    {
        $exceptionHandlerServiceMock = $this->getMockBuilder(ExceptionHandlerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exceptionHandlerServiceMock->method('handle')
            ->willReturn(new PayPalApiException('ErrorName', 'ErrorMessage'));

        return $exceptionHandlerServiceMock;
    }

    /**
     * @return SettingsService
     */
    private function createSettingsServiceMock()
    {
        $settingsServiceMock = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingsServiceMock->expects(static::once())
            ->method('hasSettings')
            ->willReturn(true);
        $settingsServiceMock->expects(static::once())
            ->method('get')
            ->willReturn(true);

        return $settingsServiceMock;
    }
}
