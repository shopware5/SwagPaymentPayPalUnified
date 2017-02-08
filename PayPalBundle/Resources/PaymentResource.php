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

namespace SwagPaymentPayPalUnified\PayPalBundle\Resources;

use SwagPaymentPayPalUnified\Components\Services\BasketService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\BasketServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PatchInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\WebProfileService;

class PaymentResource
{
    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * @var WebProfileService
     */
    private $profileService;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * PaymentResource constructor.
     *
     * @param ClientService          $clientService
     * @param WebProfileService      $webProfileService
     * @param BasketServiceInterface $basketService
     *
     * @internal param ContainerInterface $container
     */
    public function __construct(ClientService $clientService, WebProfileService $webProfileService, BasketServiceInterface $basketService)
    {
        $this->basketService = $basketService;
        $this->profileService = $webProfileService;
        $this->clientService = $clientService;
    }

    /**
     * @param $orderData
     *
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

        return $this->clientService->sendRequest(RequestType::POST, RequestUri::PAYMENT_RESOURCE, $params, true);
    }

    /**
     * @param string $payerId
     * @param string $paymentId
     *
     * @return null|array
     */
    public function execute($payerId, $paymentId)
    {
        $requestData = ['payer_id' => $payerId];

        return $this->clientService->sendRequest(
            RequestType::POST,
            RequestUri::PAYMENT_RESOURCE . '/' . $paymentId . '/execute',
            $requestData,
            true
        );
    }

    /**
     * @param string $paymentId
     *
     * @return array
     */
    public function get($paymentId)
    {
        return $this->clientService->sendRequest(RequestType::GET, RequestUri::PAYMENT_RESOURCE . '/' . $paymentId);
    }

    /**
     * @param $paymentId
     * @param PatchInterface $patch
     */
    public function patch($paymentId, $patch)
    {
        $requestData[] = [
            'op' => $patch->getOperation(),
            'path' => $patch->getPath(),
            'value' => $patch->getValue(),
        ];

        $this->clientService->sendRequest(
            RequestType::PATCH,
            RequestUri::PAYMENT_RESOURCE . '/' . $paymentId,
            $requestData
        );
    }
}
