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

class RedirectDataBuilderFactory implements RedirectDataBuilderFactoryInterface
{
    /**
     * @var ExceptionHandlerService
     */
    private $exceptionHandlerService;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(ExceptionHandlerService $exceptionHandlerService, SettingsServiceInterface $settingsService)
    {
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectDataBuilder()
    {
        return new RedirectDataBuilder($this->exceptionHandlerService, $this->settingsService);
    }
}
