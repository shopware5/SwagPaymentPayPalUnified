<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;

class ExceptionHandlerServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_create_service()
    {
        $loggerMock = new LoggerMock();
        $this->getHandler($loggerMock);
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

        $this->assertEquals(123, $error->getName());
        $this->assertEquals('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $this->assertArraySubset(['message' => 'test message'], $logErrors['Could not testing due to a communication failure']);
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

        $this->assertEquals(123, $error->getName());
        $this->assertEquals('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $this->assertArraySubset(['message' => 'test message'], $logErrors['Could not testing due to a communication failure']);
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

        $this->assertEquals(123, $error->getName());
        $this->assertEquals('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        $this->assertArraySubset(['message' => 'test message', 'payload' => 'test'], $logError);
    }

    public function test_requestException_generic_error()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            json_encode(['error' => 'test error', 'error_description' => 'test error description'])
        );

        $error = $handler->handle($e, 'testing');

        $this->assertEquals('test error', $error->getName());
        $this->assertEquals('An error occurred: test error description', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        $this->assertArraySubset(
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
            json_encode([])
        );

        $error = $handler->handle($e, 'testing');

        $this->assertEquals(123, $error->getName());
        $this->assertEquals('An error occurred: test message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        $this->assertArraySubset(['message' => 'test message', 'payload' => '[]'], $logError);
    }

    public function test_requestException_error_response()
    {
        $loggerMock = new LoggerMock();
        $handler = $this->getHandler($loggerMock);
        $e = new RequestException(
            'test message',
            123,
            null,
            json_encode([
                'name' => 'error name',
                'message' => 'error message',
                'information_link' => 'error link',
            ])
        );

        $error = $handler->handle($e, 'testing');

        $this->assertEquals('error name', $error->getName());
        $this->assertEquals('An error occurred: error message', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        $this->assertArraySubset(
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
            json_encode([
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

        $this->assertEquals('error name', $error->getName());
        $this->assertEquals('An error occurred: error message: error field, error issue', $error->getMessage());

        $logErrors = $loggerMock->getErrors();

        $this->assertArrayHasKey('Could not testing due to a communication failure', $logErrors);
        $logError = $logErrors['Could not testing due to a communication failure'];
        $this->assertArraySubset(
            [
                'message' => 'test message',
                'payload' => '{"name":"error name","message":"error message","information_link":"error link","details":[{"field":"error field","issue":"error issue"}]}',
            ],
            $logError
        );
    }

    /**
     * @param LoggerMock $loggerMock
     *
     * @return ExceptionHandlerService
     */
    private function getHandler(LoggerMock $loggerMock)
    {
        return new ExceptionHandlerService($loggerMock);
    }
}
