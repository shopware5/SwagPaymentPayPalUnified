<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\Exception\PayPalApiException;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\GenericErrorResponse;

class ExceptionHandlerService implements ExceptionHandlerServiceInterface
{
    const DEFAULT_MESSAGE = 'An error occurred: ';
    const LOG_MESSAGE = 'Could not %s due to a communication failure';
    const WEBHOOK_ALREADY_EXISTS_ERROR = 'WEBHOOK_URL_ALREADY_EXISTS';

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    public function __construct(LoggerServiceInterface $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $e, $currentAction)
    {
        $exceptionMessage = $e->getMessage();

        if (!$e instanceof RequestException) {
            $this->loggerService->error(\sprintf(self::LOG_MESSAGE, $currentAction), [
                'message' => $exceptionMessage,
            ]);

            return new PayPalApiException(
                $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $requestBody = $e->getBody();

        if (!$requestBody) {
            $clientException = $e->getPrevious();
            if ($clientException instanceof ClientException) {
                $requestBody = $clientException->getResponse()->getBody()->getContents();
            }
        }

        if (\strpos($requestBody, self::WEBHOOK_ALREADY_EXISTS_ERROR) === false) {
            $this->loggerService->error(\sprintf(self::LOG_MESSAGE, $currentAction), [
                'message' => $exceptionMessage,
                'payload' => $requestBody,
            ]);
        }

        if (!$requestBody) {
            return new PayPalApiException(
                (string) $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $requestBody = \json_decode($requestBody, true);

        if (!\is_array($requestBody)) {
            return new PayPalApiException(
                (string) $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        if (\array_key_exists('error', $requestBody) && \array_key_exists('error_description', $requestBody)) {
            $genericErrorStruct = GenericErrorResponse::fromArray($requestBody);

            return new PayPalApiException(
                $genericErrorStruct->getError(),
                self::DEFAULT_MESSAGE . $genericErrorStruct->getErrorDescription()
            );
        }

        $errorStruct = ErrorResponse::fromArray($requestBody);

        if (!$errorStruct) {
            return new PayPalApiException(
                (string) $e->getCode(),
                self::DEFAULT_MESSAGE . $exceptionMessage
            );
        }

        $message = self::DEFAULT_MESSAGE . $errorStruct->getMessage();
        $errorDetails = $errorStruct->getDetails();

        if (!empty($errorDetails)) {
            $message .= ': ';
            foreach ($errorDetails as $errorDetail) {
                $message .= $errorDetail->getIssue() . ' "' . $errorDetail->getField() . '" ';
            }
        }

        return new PayPalApiException(
            $errorStruct->getName(),
            $message
        );
    }
}
