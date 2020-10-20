<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;

class ExceptionHandlerServiceTest extends TestCase
{
    public function test_create_service()
    {
        $loggerMock = new LoggerMock();
        $result = $this->getHandler($loggerMock);

        static::assertInstanceOf(ExceptionHandlerService::class, $result);
    }

    public function test_exception()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new \Exception(
            'test message',
            123
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame(123, $error->getName());
        static::assertSame('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        static::assertEquals(['message' => 'test message'], $logErrors['Could not testing due to a communication failure']);
    }

    public function test_requestException_without_body()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame(123, $error->getName());
        static::assertSame('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        static::assertEquals(['message' => 'test message', 'payload' => null], $logErrors['Could not testing due to a communication failure']);
    }

    public function test_requestException_with_body_but_no_array()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            'test'
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame(123, $error->getName());
        static::assertSame('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        static::assertEquals(['message' => 'test message', 'payload' => 'test'], $logError);
    }

    public function test_requestException_generic_error()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            \json_encode(['error' => 'test error', 'error_description' => 'test error description'])
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame('test error', $error->getName());
        static::assertSame('An error occurred: test error description', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        static::assertEquals(
            [
                'message' => 'test message',
                'payload' => '{"error":"test error","error_description":"test error description"}',
            ],
            $logError
        );
    }

    public function test_requestException_no_error_struct()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            \json_encode([])
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame(123, $error->getName());
        static::assertSame('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        static::assertEquals(['message' => 'test message', 'payload' => '[]'], $logError);
    }

    public function test_requestException_error_response()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            \json_encode([
                'name' => 'error name',
                'message' => 'error message',
                'information_link' => 'error link',
            ])
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame('error name', $error->getName());
        static::assertSame('An error occurred: error message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        static::assertEquals(
            [
                'message' => 'test message',
                'payload' => '{"name":"error name","message":"error message","information_link":"error link"}',
            ],
            $logError
        );
    }

    public function test_requestException_error_response_details()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            \json_encode([
                'name' => 'error name',
                'message' => 'error message',
                'information_link' => 'error link',
                'details' => [[
                    'field' => 'error field',
                    'issue' => 'error issue',
                ]],
            ])
        );

        $error = $handler->handle($e, 'testing');

        static::assertSame('error name', $error->getName());
        static::assertSame('An error occurred: error message: error issue "error field" ', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        static::assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        static::assertEquals(
            [
                'message' => 'test message',
                'payload' => '{"name":"error name","message":"error message","information_link":"error link","details":[{"field":"error field","issue":"error issue"}]}',
            ],
            $logError
        );
    }

    /**
     * @return ExceptionHandlerService
     */
    private function getHandler(LoggerMock $loggerMock)
    {
        return new ExceptionHandlerService($loggerMock);
    }
}
