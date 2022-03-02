<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Validation;

use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class RedirectDataBuilder
{
    /**
     * @var ExceptionHandlerService
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
     * @var bool
     */
    private $hasCode = false;

    /**
     * @var array<string, mixed>
     */
    private $data = [
        'controller' => 'checkout',
        'action' => 'shippingPayment',
    ];

    public function __construct(ExceptionHandlerService $exceptionHandlerService, SettingsServiceInterface $settingsService)
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

        $this->hasCode = true;

        return $this;
    }

    public function setException(\Exception $exception)
    {
        $error = $this->exceptionHandlerService->handle($exception, 'process checkout');

        if ($this->settingsService->hasSettings() && $this->settingsService->get(SettingsServiceInterface::SETTING_GENERAL_DISPLAY_ERRORS)) {
            $this->data['paypal_unified_error_name'] = $error->getName();
            $this->data['paypal_unified_error_message'] = $error->getMessage();

            $this->hasException = true;
        }

        return $this;
    }

    public function setRedirectToFinishAction()
    {
        $this->data['action'] = 'finish';

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRedirectData()
    {
        return $this->data;
    }

    public function hasException()
    {
        return $this->hasException;
    }

    public function getCode()
    {
        if ($this->hasCode) {
            return $this->data['paypal_unified_error_code'];
        }

        return null;
    }

    public function getErrorName()
    {
        if ($this->hasException()) {
            return $this->data['paypal_unified_error_name'];
        }

        return null;
    }

    public function getErrorMessage()
    {
        if ($this->hasException()) {
            return $this->data['paypal_unified_error_message'];
        }

        return null;
    }
}
