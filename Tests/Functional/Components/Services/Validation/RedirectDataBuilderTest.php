<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Validation;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Exception\PayPalApiException;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;

class RedirectDataBuilderTest extends TestCase
{
    public function testSetCode()
    {
        $code = 12;
        $redirectDataBuilder = $this->getRedirectDataBuilder();

        static::assertNull($redirectDataBuilder->getCode());

        $setCodeResult = $redirectDataBuilder->setCode($code);

        static::assertInstanceOf(RedirectDataBuilder::class, $setCodeResult);
        static::assertSame($code, $redirectDataBuilder->getCode());
    }

    public function testSetException()
    {
        $exceptionHandlerServiceMock = $this->createExceptionHandlerServiceMock();

        $settingsServiceMock = $this->createSettingsServiceMock();

        $redirectDataBuilder = $this->getRedirectDataBuilder($exceptionHandlerServiceMock, $settingsServiceMock);

        static::assertFalse($redirectDataBuilder->hasException());

        $setExceptionResult = $redirectDataBuilder->setException(new \Exception('FooBar'));
        static::assertInstanceOf(RedirectDataBuilder::class, $setExceptionResult);

        static::assertTrue($redirectDataBuilder->hasException());
        static::assertSame('ErrorName', $redirectDataBuilder->getErrorName());
        static::assertSame('ErrorMessage', $redirectDataBuilder->getErrorMessage());
    }

    public function testSetRedirectToFinishAction()
    {
        $redirectDataBuilder = $this->getRedirectDataBuilder();
        $redirectDataBuilder->setRedirectToFinishAction();

        $result = $redirectDataBuilder->getRedirectData()['action'];

        static::assertSame('finish', $result);
    }

    public function testACompleteBuild()
    {
        $code = 12;

        $exceptionHandlerServiceMock = $this->createExceptionHandlerServiceMock();

        $settingsServiceMock = $this->createSettingsServiceMock();

        $redirectDataBuilder = $this->getRedirectDataBuilder($exceptionHandlerServiceMock, $settingsServiceMock);

        static::assertNull($redirectDataBuilder->getCode());
        static::assertNull($redirectDataBuilder->getErrorName());
        static::assertNull($redirectDataBuilder->getErrorMessage());
        static::assertFalse($redirectDataBuilder->hasException());

        $redirectDataBuilder->setCode($code)
            ->setException(new \Exception('TestException'))
            ->setRedirectToFinishAction();

        static::assertSame($code, $redirectDataBuilder->getCode());
        static::assertNotNull($redirectDataBuilder->getErrorName());
        static::assertNotNull($redirectDataBuilder->getErrorMessage());
        static::assertTrue($redirectDataBuilder->hasException());

        $result = $redirectDataBuilder->getRedirectData();

        $expectedResult = [
            'controller' => 'checkout',
            'action' => 'finish',
            'paypal_unified_error_code' => 12,
            'paypal_unified_error_name' => 'ErrorName',
            'paypal_unified_error_message' => 'ErrorMessage',
        ];

        static::assertSame($expectedResult, $result);
    }

    /**
     * @param ExceptionHandlerService|null $exceptionHandlerService
     * @param SettingsService|null         $settingsService
     *
     * @return RedirectDataBuilder
     */
    private function getRedirectDataBuilder($exceptionHandlerService = null, $settingsService = null)
    {
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

        return new RedirectDataBuilder($exceptionHandlerService, $settingsService);
    }

    private function createExceptionHandlerServiceMock()
    {
        $exceptionHandlerServiceMock = $this->getMockBuilder(ExceptionHandlerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exceptionHandlerServiceMock->method('handle')
            ->willReturn(new PayPalApiException('ErrorName', 'ErrorMessage'));

        return $exceptionHandlerServiceMock;
    }

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
