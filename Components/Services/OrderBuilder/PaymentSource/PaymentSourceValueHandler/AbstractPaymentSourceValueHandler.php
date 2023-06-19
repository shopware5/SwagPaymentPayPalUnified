<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\AbstractApmPaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\AbstractPaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use UnexpectedValueException;

abstract class AbstractPaymentSourceValueHandler
{
    const NAME_TEMPLATE = '%s %s';

    /**
     * @var SettingsServiceInterface
     */
    protected $settingsService;

    /**
     * @var ContextServiceInterface
     */
    protected $contextService;

    /**
     * @var ReturnUrlHelper
     */
    protected $returnUrlHelper;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ContextServiceInterface $contextService,
        ReturnUrlHelper $returnUrlHelper
    ) {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
        $this->returnUrlHelper = $returnUrlHelper;
    }

    /**
     * @return AbstractPaymentSource
     */
    abstract public function createPaymentSourceValue(PayPalOrderParameter $orderParameter);

    /**
     * @param string $paymentType
     *
     * @return bool
     */
    abstract public function supports($paymentType);

    protected function setDefaultValues(
        AbstractApmPaymentSource $apmPaymentSourceValue,
        PayPalOrderParameter $orderParameter
    ) {
        $customer = $orderParameter->getCustomer();

        $apmPaymentSourceValue->setName(
            $this->createName(
                $customer['additional']['user']['firstname'],
                $customer['additional']['user']['lastname']
            )
        );

        $apmPaymentSourceValue->setCountryCode($customer['additional']['country']['countryiso']);
    }

    /**
     * @return ExperienceContext
     */
    protected function createExperienceContext(PayPalOrderParameter $orderParameter)
    {
        $shop = $this->contextService->getShopContext()->getShop();
        $generalSettings = $this->settingsService->getSettings($shop->getId());
        if (!$generalSettings instanceof General) {
            throw new UnexpectedValueException(
                \sprintf('Expect instance of SwagPaymentPayPalUnified\Models\Settings\General, got %s', \gettype($generalSettings))
            );
        }

        $experienceContext = new ExperienceContext();
        $experienceContext->setPaymentMethodPreference(ExperienceContext::PAYMENT_METHOD_PREFERENCE);
        $experienceContext->setPaymentMethodSelected(ExperienceContext::PAYMENT_METHOD);

        $experienceContext->setShippingPreference($this->getShippingPreference($orderParameter->getPaymentType()));
        $experienceContext->setUserAction($this->getUserAction($orderParameter->getPaymentType()));

        if ($generalSettings->getBrandName() !== '') {
            $experienceContext->setBrandName($this->shortensBrandName((string) $generalSettings->getBrandName()));
        }

        $experienceContext->setLocale(str_replace('_', '-', $shop->getLocale()->getLocale()));
        $experienceContext->setLandingPage($this->getLandingPage($generalSettings));

        if ($this->requiresUrls($orderParameter->getPaymentType())) {
            $experienceContext->setCancelUrl($this->returnUrlHelper->getCancelUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken()));
            $experienceContext->setReturnUrl(
                $this->returnUrlHelper->createRedirectUrl(
                    'PaypalUnifiedApm',
                    'return',
                    $orderParameter->getBasketUniqueId(),
                    $orderParameter->getPaymentToken()
                )
            );
        }

        return $experienceContext;
    }

    /**
     * @return string
     */
    private function getLandingPage(General $generalSettings)
    {
        $currentLandingPage = $generalSettings->getLandingPageType();

        $possibleLandingPageTypes = [
            ExperienceContext::LANDING_PAGE_TYPE_BILLING,
            ExperienceContext::LANDING_PAGE_TYPE_LOGIN,
            ExperienceContext::LANDING_PAGE_TYPE_NO_PREFERENCE,
        ];

        if (!in_array($currentLandingPage, $possibleLandingPageTypes)) {
            return ExperienceContext::LANDING_PAGE_TYPE_NO_PREFERENCE;
        }

        return $currentLandingPage;
    }

    /**
     * @param string $firstname
     * @param string $lastname
     *
     * @return string
     */
    private function createName($firstname, $lastname)
    {
        return sprintf(self::NAME_TEMPLATE, $firstname, $lastname);
    }

    /**
     * @param string $brandName
     *
     * @return string
     */
    private function shortensBrandName($brandName)
    {
        if (\strlen($brandName) > 127) {
            $brandName = \substr($brandName, 0, 127);
        }

        return $brandName;
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return string
     */
    private function getUserAction($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_EXPRESS_V2 ? ExperienceContext::USER_ACTION_CONTINUE : ExperienceContext::USER_ACTION_PAY_NOW;
    }

    /**
     * @param PaymentType::* $paymentType
     *
     * @return string
     */
    private function getShippingPreference($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_EXPRESS_V2 ? ExperienceContext::SHIPPING_PREFERENCE_GET_FROM_FILE : ExperienceContext::SHIPPING_PREFERENCE_PROVIDED_ADDRESS;
    }

    /**
     * @param PaymentType::* $getPaymentType
     *
     * @return bool
     */
    private function requiresUrls($getPaymentType)
    {
        return \in_array($getPaymentType, PaymentType::getApmPaymentTypes());
    }
}
