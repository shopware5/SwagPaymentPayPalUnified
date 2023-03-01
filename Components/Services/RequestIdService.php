<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Enlight_Controller_Request_Request;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use UnexpectedValueException;

class RequestIdService
{
    const REQUEST_ID_KEY = 'payPalUnifiedCurrentRequestId';

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var LoggerServiceInterface
     */
    private $loggerService;

    public function __construct(DependencyProvider $dependencyProvider, LoggerServiceInterface $loggerService)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->loggerService = $loggerService;
    }

    /**
     * @return string
     */
    public function generateNewRequestId()
    {
        $newRequestId = $this->randomHex();

        $this->loggerService->debug(sprintf('%s GENERATES NEW REQUEST ID: %s', __METHOD__, $newRequestId));

        return $newRequestId;
    }

    /**
     * @param string $requestId
     *
     * @return void
     */
    public function saveRequestIdToSession($requestId)
    {
        $this->loggerService->debug(sprintf('%s SAVE REQUEST ID: %s TO SESSION', __METHOD__, $requestId));

        $requestId = $this->validateRequestId($requestId);

        $session = $this->dependencyProvider->getSession();

        $session->offsetSet(self::REQUEST_ID_KEY, $requestId);
    }

    /**
     * @return string
     */
    public function getRequestIdFromSession()
    {
        $session = $this->dependencyProvider->getSession();

        $requestId = $session->offsetGet(self::REQUEST_ID_KEY);

        $this->loggerService->debug(sprintf('%s GET REQUEST ID: %s FROM SESSION', __METHOD__, $requestId));

        return $this->validateRequestId($requestId);
    }

    /**
     * @return void
     */
    public function removeRequestIdFromSession()
    {
        $session = $this->dependencyProvider->getSession();

        $this->loggerService->debug(sprintf('%s REMOVE REQUEST ID FROM SESSION', __METHOD__));

        $session->offsetUnset(self::REQUEST_ID_KEY);
    }

    /**
     * @param string $requestId
     *
     * @return bool
     */
    public function checkRequestIdIsAlreadySetToSession($requestId)
    {
        $this->loggerService->debug(sprintf('%s CHECK REQUEST ID: %s FROM SESSION', __METHOD__, $requestId));

        $requestId = $this->validateRequestId($requestId);

        $session = $this->dependencyProvider->getSession();
        $inSessionSavedRequestId = $session->offsetGet(self::REQUEST_ID_KEY);

        if (!\is_string($inSessionSavedRequestId)) {
            return false;
        }

        if ($requestId !== $inSessionSavedRequestId) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getRequestIdFromRequest(Enlight_Controller_Request_Request $request)
    {
        try {
            $byRequestProvidedId = $this->validateRequestId($request->getParam(self::REQUEST_ID_KEY));
        } catch (UnexpectedValueException $exception) {
            $this->loggerService->debug(sprintf('%s REQUEST DOES NOT CONTAIN A VALID REQUEST ID', __METHOD__));

            return '';
        }

        $this->loggerService->debug(sprintf('%s GET REQUEST ID: %s FROM REQUEST', __METHOD__, $byRequestProvidedId));

        return $byRequestProvidedId;
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return bool
     */
    public function isRequestIdRequired($paymentType)
    {
        $supportedPaymentTypes = array_merge(PaymentType::getApmPaymentTypes(), [PaymentType::PAYPAL_PAY_UPON_INVOICE_V2]);

        if (!\in_array($paymentType, $supportedPaymentTypes)) {
            $this->loggerService->debug(sprintf('%s REQUEST ID FOR PAYMENT TYPE: %s IS NOT REQUIRED', __METHOD__, $paymentType));

            return false;
        }

        $this->loggerService->debug(sprintf('%s REQUEST ID FOR PAYMENT TYPE: %s IS REQUIRED', __METHOD__, $paymentType));

        return true;
    }

    /**
     * @param string $requestId
     *
     * @return string
     */
    private function validateRequestId($requestId)
    {
        if (!\is_string($requestId)) {
            $this->loggerService->debug(sprintf('%s PROVIDED REQUEST ID: %s IS NOT A STRING', __METHOD__, print_r($requestId, true)));

            throw new UnexpectedValueException(
                \sprintf('Provided requestId expect to be of type string got %s', \gettype($requestId))
            );
        }

        if (trim($requestId) === '') {
            $this->loggerService->debug(sprintf('%s PROVIDED REQUEST ID IS EMPTY', __METHOD__));

            throw new UnexpectedValueException('The provided requestId is empty');
        }

        $this->loggerService->debug(sprintf('%s PROVIDED REQUEST ID: %s IS VALID', __METHOD__, $requestId));

        return $requestId;
    }

    /**
     * @return string
     */
    private function randomHex()
    {
        return \sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            \mt_rand(0, 0xFFFF),
            \mt_rand(0, 0xFFFF),

            // 16 bits for "time_mid"
            \mt_rand(0, 0xFFFF),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            \mt_rand(0, 0x0FFF) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            \mt_rand(0, 0x3FFF) | 0x8000,

            // 48 bits for "node"
            \mt_rand(0, 0xFFFF),
            \mt_rand(0, 0xFFFF),
            \mt_rand(0, 0xFFFF)
        );
    }
}
