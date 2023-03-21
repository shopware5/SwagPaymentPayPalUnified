<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

use Exception;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessage;
use SwagPaymentPayPalUnified\Components\Services\ErrorMessages\ErrorMessageTransporter;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class RedirectDataBuilder
{
    const PAYPAL_UNIFIED_ERROR_KEY = 'paypal_unified_error_key';

    const PAYPAL_UNIFIED_ERROR_CODE = 'paypal_unified_error_code';

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandlerService;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var bool
     */
    private $hasException = false;

    /**
     * @var array{controller: 'checkout', action: 'shippingPayment'|'finish', paypal_unified_error_code?: int, paypal_unified_error_name?: string|int, paypal_unified_error_message?: string, paypal_unified_error_key?: string }
     */
    private $data = [
        'controller' => 'checkout',
        'action' => 'shippingPayment',
    ];

    /**
     * @var ErrorMessageTransporter
     */
    private $errorMessageTransporter;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandlerService,
        SettingsServiceInterface $settingsService,
        ErrorMessageTransporter $errorMessageTransporter
    ) {
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->settingsService = $settingsService;
        $this->errorMessageTransporter = $errorMessageTransporter;
    }

    /**
     * @param int $code
     *
     * @return RedirectDataBuilder
     */
    public function setCode($code)
    {
        $this->data[self::PAYPAL_UNIFIED_ERROR_CODE] = $code;

        return $this;
    }

    /**
     * @param string $currentAction
     *
     * @return RedirectDataBuilder
     */
    public function setException(Exception $exception, $currentAction)
    {
        $error = $this->exceptionHandlerService->handle($exception, $currentAction);

        if ($this->settingsService->hasSettings() && $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_DISPLAY_ERRORS)) {
            $this->data[self::PAYPAL_UNIFIED_ERROR_KEY] = $this->errorMessageTransporter->setErrorMessageToSession((string) $error->getName(), $error->getMessage());

            $this->hasException = true;
        }

        return $this;
    }

    /**
     * @return RedirectDataBuilder
     */
    public function setRedirectToFinishAction()
    {
        $this->data['action'] = 'finish';

        return $this;
    }

    /**
     * @return array{controller: 'checkout', action: 'shippingPayment'|'finish', paypal_unified_error_code?: int, paypal_unified_error_name?: string|int, paypal_unified_error_message?: string, paypal_unified_error_key?: string }
     */
    public function getRedirectData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function hasException()
    {
        return $this->hasException;
    }

    /**
     * @return int|null
     */
    public function getCode()
    {
        return isset($this->data[self::PAYPAL_UNIFIED_ERROR_CODE]) ? $this->data[self::PAYPAL_UNIFIED_ERROR_CODE] : null;
    }

    /**
     * @return string|int|null
     *
     * @deprecated in 6.0.2, will be removed with 7.0.0.
     */
    public function getErrorName()
    {
        return isset($this->data[ErrorMessage::ERROR_NAME_KEY]) ? $this->data[ErrorMessage::ERROR_NAME_KEY] : null;
    }

    /**
     * @return string|null
     *
     * @deprecated in 6.0.2, will be removed with 7.0.0.
     */
    public function getErrorMessage()
    {
        return isset($this->data[ErrorMessage::ERROR_MESSAGE_KEY]) ? $this->data[ErrorMessage::ERROR_MESSAGE_KEY] : null;
    }
}
