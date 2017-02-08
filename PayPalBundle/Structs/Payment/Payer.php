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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;

class Payer
{
    /**
     * The payment of the request that is expected by PayPal
     *
     * @var string
     */
    private $paymentMethod = 'paypal';

    /** @var string $status */
    private $status;

    /** @var PayerInfo $payerInfo */
    private $payerInfo;

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return PayerInfo
     */
    public function getPayerInfo()
    {
        return $this->payerInfo;
    }

    /**
     * @param PayerInfo $payerInfo
     */
    public function setPayerInfo($payerInfo)
    {
        $this->payerInfo = $payerInfo;
    }

    /**
     * @param array $data
     *
     * @return Payer
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setPaymentMethod($data['payment_method']);
        $result->setPayerInfo(PayerInfo::fromArray($data['payer_info']));
        $result->setStatus($data['status']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'payment_method' => $this->getPaymentMethod(),
            'status' => $this->getStatus(),
        ];

        if ($this->payerInfo !== null) {
            $result['payer_info'] = $this->payerInfo->toArray();
        }

        return $result;
    }
}
