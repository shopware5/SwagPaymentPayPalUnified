<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
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
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ClientService            $client
     * @param SettingsServiceInterface $config
     * @param DependencyProvider       $dependencyProvider
     * @param Logger                   $pluginLogger
     */
    public function __construct(
        ClientService $client,
        SettingsServiceInterface $config,
        DependencyProvider $dependencyProvider,
        Logger $pluginLogger
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->dependencyProvider = $dependencyProvider;
        $this->logger = $pluginLogger;
    }

    /**
     * This function returns the WebProfile that can be used for a transaction.
     * - Will create a new one, if it does not exist yet.
     * - Will update an existing one if the content has changed.
     *
     * @return WebProfile
     */
    public function getWebProfile()
    {
        $webProfileResource = new WebProfileResource($this->client);
        $currentWebProfile = $this->getCurrentWebProfile();

        try {
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

            return $selectedRemoteProfile;
        } catch (RequestException $rex) {
            $this->logger->error('PayPal Unified: Could not request the web profiles.', [$rex->getMessage(), $rex->getBody()]);
        } catch (\Exception $ex) {
            $this->logger->error('PayPal Unified: An unknown error occurred while setting the web profile.', [$ex->getMessage()]);
        }

        return null;
    }

    /**
     * @return WebProfile
     */
    private function getCurrentWebProfile()
    {
        $shop = $this->dependencyProvider->getShop();
        $logoImage = $this->config->get('logo_image');
        $brandName = $this->config->get('brand_name');

        //Prevent too long brand names
        $brandName = strlen($brandName) > 127 ? substr($brandName, 0, 124) . '...' : $brandName;

        $webProfile = new WebProfile();
        $webProfile->setName($shop->getId() . $shop->getHost() . $shop->getBasePath());
        $webProfile->setTemporary(false);

        $presentation = new WebProfilePresentation();
        $presentation->setLocaleCode($shop->getLocale()->getLocale());
        $presentation->setLogoImage($logoImage);
        $presentation->setBrandName($brandName);

        $flowConfig = new WebProfileFlowConfig();
        $flowConfig->setReturnUriHttpMethod('POST');
        $flowConfig->setUserAction('Commit');

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
