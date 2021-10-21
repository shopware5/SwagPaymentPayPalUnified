<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class ApplicationContext extends PayPalApiStruct
{
    const LANDING_PAGE_TYPE_LOGIN = 'LOGIN';
    const LANDING_PAGE_TYPE_BILLING = 'BILLING';
    const LANDING_PAGE_TYPE_NO_PREFERENCE = 'NO_PREFERENCE';

    const SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';
    const SHIPPING_PREFERENCE_NO_SHIPPING = 'NO_SHIPPING';
    const SHIPPING_PREFERENCE_GET_FROM_FILE = 'GET_FROM_FILE';

    const USER_ACTION_CONTINUE = 'CONTINUE';
    const USER_ACTION_PAY_NOW = 'PAY_NOW';

    /**
     * @var string
     */
    protected $brandName;

    /**
     * @var string
     */
    protected $landingPage = self::LANDING_PAGE_TYPE_NO_PREFERENCE;

    /**
     * @var string
     */
    protected $shippingPreference = self::SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS;

    /**
     * @var string
     */
    protected $userAction = self::USER_ACTION_PAY_NOW;

    /**
     * @var string
     */
    protected $returnUrl;

    /**
     * @var string
     */
    protected $cancelUrl;

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
     * @return string
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
     * @return string
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
}
