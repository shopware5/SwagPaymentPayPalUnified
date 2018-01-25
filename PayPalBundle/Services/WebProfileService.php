<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\WebProfileResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;

class WebProfileService
{
    /**
     * @var ClientService
     */
    private $client;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ClientService $client
     * @param ModelManager  $modelManager
     */
    public function __construct(
        ClientService $client,
        ModelManager $modelManager
    ) {
        $this->client = $client;
        $this->modelManager = $modelManager;
    }

    /**
     * This function returns the WebProfile that can be used for a transaction.
     * - Will create a new one, if it does not exist yet.
     * - Will update an existing one if the content has changed.
     *
     * @param array $settings
     * @param bool  $forExpressCheckout
     *
     * @return string
     */
    public function getWebProfile(array $settings, $forExpressCheckout = false)
    {
        $this->settings = $settings;

        $webProfileResource = new WebProfileResource($this->client);
        $currentWebProfile = $this->getCurrentWebProfile($forExpressCheckout);

        $profileList = $webProfileResource->getList();

        /** @var WebProfile $selectedRemoteProfile */
        $selectedRemoteProfile = null;

        foreach ($profileList as $remoteProfile) {
            $profileStruct = WebProfile::fromArray($remoteProfile);
            if ($profileStruct->getName() === $currentWebProfile->getName()) {
                $selectedRemoteProfile = $profileStruct;
                break;
            }
        }

        if ($selectedRemoteProfile === null) {
            //If we don't have a profile for the shop (yet) we have to create one.
            $selectedRemoteProfile = $webProfileResource->create($currentWebProfile);
        } elseif (!$currentWebProfile->equals($selectedRemoteProfile)) {
            //The web profile is not the same as the current profile, therefore we need to patch it.
            $webProfileResource->update($selectedRemoteProfile->getId(), $currentWebProfile);

            //Store the id in the current web-profile
            $currentWebProfile->setId($selectedRemoteProfile->getId());
            $selectedRemoteProfile = $currentWebProfile;
        }

        return $selectedRemoteProfile->getId();
    }

    /**
     * @param bool $forExpressCheckout
     *
     * @return WebProfile
     */
    private function getCurrentWebProfile($forExpressCheckout)
    {
        /** @var ShopRepository $shopRepo */
        $shopRepo = $this->modelManager->getRepository(Shop::class);

        /** @var Shop $shop */
        $shop = $shopRepo->getActiveById($this->settings['shopId']);
        if (!$shop) {
            $shop = $shopRepo->getActiveDefault();
        }

        $logoImage = $this->settings['logoImage'];
        $brandName = $this->settings['brandName'];

        //Prevent too long brand names
        $brandName = strlen($brandName) > 127 ? substr($brandName, 0, 124) . '...' : $brandName;

        $webProfile = new WebProfile();
        $name = $shop->getId() . $shop->getHost() . $shop->getBasePath();
        if ($forExpressCheckout) {
            $name = 'EC' . $name;
        }
        $webProfile->setName($name);
        $webProfile->setTemporary(false);

        $presentation = new WebProfilePresentation();
        $presentation->setLocaleCode($shop->getLocale()->getLocale());
        $presentation->setLogoImage($logoImage);
        $presentation->setBrandName($brandName);

        $flowConfig = new WebProfileFlowConfig();
        $flowConfig->setReturnUriHttpMethod('POST');
        if (!$forExpressCheckout) {
            $flowConfig->setUserAction('Commit');
        }

        $inputFields = new WebProfileInputFields();
        $inputFields->setAddressOverride('1');
        $inputFields->setAllowNote(false);
        $inputFields->setNoShipping(0);

        $webProfile->setFlowConfig($flowConfig);
        $webProfile->setInputFields($inputFields);
        $webProfile->setPresentation($presentation);

        return $webProfile;
    }
}
