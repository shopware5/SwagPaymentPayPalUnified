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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class RedirectDataBuilder
{
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
     * @var array{controller: 'checkout', action: 'shippingPayment'|'finish', paypal_unified_error_code?: int, paypal_unified_error_name?: string|int, paypal_unified_error_message?: string}
     */
    private $data = [
        'controller' => 'checkout',
        'action' => 'shippingPayment',
    ];

    public function __construct(ExceptionHandlerServiceInterface $exceptionHandlerService, SettingsServiceInterface $settingsService)
    {
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->settingsService = $settingsService;
    }

    /**
     * @param int $code
     *
     * @return RedirectDataBuilder
     */
    public function setCode($code)
    {
        $this->data['paypal_unified_error_code'] = $code;

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
            $this->data['paypal_unified_error_name'] = $error->getName();
            $this->data['paypal_unified_error_message'] = $error->getMessage();

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
     * @return array{controller: 'checkout', action: 'shippingPayment'|'finish', paypal_unified_error_code?: int, paypal_unified_error_name?: string|int, paypal_unified_error_message?: string}
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
        return isset($this->data['paypal_unified_error_code']) ? $this->data['paypal_unified_error_code'] : null;
    }

    /**
     * @return string|int|null
     */
    public function getErrorName()
    {
        return isset($this->data['paypal_unified_error_name']) ? $this->data['paypal_unified_error_name'] : null;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return isset($this->data['paypal_unified_error_message']) ? $this->data['paypal_unified_error_message'] : null;
    }
}
