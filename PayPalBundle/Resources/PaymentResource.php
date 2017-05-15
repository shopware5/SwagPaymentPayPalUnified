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

use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PatchInterface;
use SwagPaymentPayPalUnified\PayPalBundle\RequestType;
use SwagPaymentPayPalUnified\PayPalBundle\RequestUri;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class PaymentResource
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param Payment $payment
     *
     * @return array
     */
    public function create(Payment $payment)
    {
        return $this->clientService->sendRequest(RequestType::POST, RequestUri::PAYMENT_RESOURCE, $payment->toArray(), true);
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
