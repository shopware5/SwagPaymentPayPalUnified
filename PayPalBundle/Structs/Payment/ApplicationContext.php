<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class ApplicationContext
{
    /**
     * @var string
     */
    private $brandName;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $landingPage;

    /**
     * @var string
     */
    private $shippingPreference = 'SET_PROVIDED_ADDRESS';

    /**
     * @var string
     */
    private $userAction = 'commit';

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
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
     */
    public function setUserAction($userAction)
    {
        $this->userAction = $userAction;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'brand_name' => $this->getBrandName(),
            'locale' => $this->getLocale(),
            'landing_page' => $this->getLandingPage(),
            'shipping_preference' => $this->getShippingPreference(),
            'user_action' => $this->getUserAction(),
        ];
    }
}
