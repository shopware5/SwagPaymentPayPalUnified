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

namespace SwagPaymentPayPalUnified\SDK\Resources;

use SwagPaymentPayPalUnified\SDK\Components\BasketServiceInterface;
use SwagPaymentPayPalUnified\SDK\RequestType;
use SwagPaymentPayPalUnified\SDK\RequestUri;
use SwagPaymentPayPalUnified\SDK\Components\Patches\PatchInterface;
use SwagPaymentPayPalUnified\SDK\Services\ClientService;
use SwagPaymentPayPalUnified\SDK\Services\WebProfileService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SwagPaymentPayPalUnified\Components\Services\BasketService;

class PaymentResource
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var BasketService $basketService
     */
    private $basketService;

    /**
     * @var WebProfileService $profileService
     */
    private $profileService;

    /**
     * @var ClientService $clientService
     */
    private $clientService;

    /**
     * PaymentResource constructor.
     * @param ContainerInterface $container
     * @param BasketServiceInterface $basketService
     */
    public function __construct(ContainerInterface $container, BasketServiceInterface $basketService)
    {
        $this->container = $container;
        $this->basketService = $basketService;
        $this->profileService = $this->container->get('paypal_unified.web_profile_service');
        $this->clientService = $this->container->get('paypal_unified.client_service');
    }

    /**
     * @param $orderData
     * @return array
     */
    public function create($orderData)
    {
        $basketData = $orderData['sBasket'];
        $userData = $orderData['sUserData'];

        $profile = $this->profileService->getWebProfile();
        $params = $this->basketService->getRequestParameters(
            $profile,
            $basketData,
            $userData
        );

        return $this->clientService->sendRequest(RequestType::POST, RequestUri::PAYMENT_URI, $params, true);
    }

    /**
     * @param string $payerId
     * @param string $paymentId
     * @return null|array
     */
    public function execute($payerId, $paymentId)
    {
        $requestData = ['payer_id' => $payerId];

        return $this->clientService->sendRequest(
            RequestType::POST,
            RequestUri::PAYMENT_URI . '/' . $paymentId . '/execute',
            $requestData,
            true
        );
    }

    /**
     * @param $paymentId
     * @param PatchInterface[] $patches
     */
    public function patch($paymentId, array $patches)
    {
        $requestData = [];

        /** @var PatchInterface $patch */
        foreach ($patches as $patch) {
            $requestData[] = [
                'op' => $patch->getOperation(),
                'path' => $patch->getPath(),
                'value' => $patch->getValue()
            ];
        }

        $this->clientService->sendRequest(
            RequestType::PATCH,
            RequestUri::PAYMENT_URI . '/' . $paymentId,
            $requestData,
            true
        );
    }
}
