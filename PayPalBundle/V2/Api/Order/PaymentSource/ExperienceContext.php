<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class ExperienceContext extends PayPalApiStruct
{
    const PAYMENT_METHOD_PREFERENCE = 'IMMEDIATE_PAYMENT_REQUIRED';

    const PAYMENT_METHOD = 'PAYPAL';

    const SHIPPING_PREFERENCE_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';

    const SHIPPING_PREFERENCE_GET_FROM_FILE = 'GET_FROM_FILE';

    const USER_ACTION_PAY_NOW = 'PAY_NOW';

    const USER_ACTION_CONTINUE = 'CONTINUE';

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $brandName;

    /**
     * @var string
     */
    protected $logoUrl;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var string
     */
    protected $cancelUrl;

    /**
     * @var array
     */
    protected $customerServiceInstructions;

    /**
     * @var string|null
     */
    protected $paymentMethodPreference;

    /**
     * @var string|null
     */
    protected $paymentMethodSelected;

    /**
     * @var string|null
     */
    protected $landingPage;

    /**
     * @var string|null
     */
    protected $shippingPreference;

    /**
     * @var string|null
     */
    protected $userAction;

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     *
     * @return void
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @param string $logoUrl
     *
     * @return void
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return void
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     *
     * @return void
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * @return array
     */
    public function getCustomerServiceInstructions()
    {
        return $this->customerServiceInstructions;
    }

    /**
     * @param array $customerServiceInstructions
     *
     * @return void
     */
    public function setCustomerServiceInstructions($customerServiceInstructions)
    {
        $this->customerServiceInstructions = $customerServiceInstructions;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethodPreference()
    {
        return $this->paymentMethodPreference;
    }

    /**
     * @param string $paymentMethodPreference
     *
     * @return void
     */
    public function setPaymentMethodPreference($paymentMethodPreference)
    {
        $this->paymentMethodPreference = $paymentMethodPreference;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethodSelected()
    {
        return $this->paymentMethodSelected;
    }

    /**
     * @param string $paymentMethodSelected
     *
     * @return void
     */
    public function setPaymentMethodSelected($paymentMethodSelected)
    {
        $this->paymentMethodSelected = $paymentMethodSelected;
    }

    /**
     * @return string|null
     */
    public function getLandingPage()
    {
        return $this->landingPage;
    }

    /**
     * @param string $landingPage
     *
     * @return void
     */
    public function setLandingPage($landingPage)
    {
        $this->landingPage = $landingPage;
    }

    /**
     * @return string|null
     */
    public function getShippingPreference()
    {
        return $this->shippingPreference;
    }

    /**
     * @param string $shippingPreference
     *
     * @return void
     */
    public function setShippingPreference($shippingPreference)
    {
        $this->shippingPreference = $shippingPreference;
    }

    /**
     * @return string|null
     */
    public function getUserAction()
    {
        return $this->userAction;
    }

    /**
     * @param string $userAction
     *
     * @return void
     */
    public function setUserAction($userAction)
    {
        $this->userAction = $userAction;
    }
}
