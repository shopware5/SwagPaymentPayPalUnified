<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    /**
     * @var string
     */
    private $status;

    /**
     * @var PayerInfo
     */
    private $payerInfo;

    /**
     * @var string
     */
    private $externalSelectedFundingInstrumentType;

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
     * @return string
     */
    public function getExternalSelectedFundingInstrumentType()
    {
        return $this->externalSelectedFundingInstrumentType;
    }

    /**
     * @param string $externalSelectedFundingInstrumentType
     */
    public function setExternalSelectedFundingInstrumentType($externalSelectedFundingInstrumentType)
    {
        $this->externalSelectedFundingInstrumentType = $externalSelectedFundingInstrumentType;
    }

    /**
     * @return Payer
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setPaymentMethod($data['payment_method']);
        $result->setPayerInfo(PayerInfo::fromArray($data['payer_info']));
        $result->setStatus($data['status']);
        $result->setExternalSelectedFundingInstrumentType($data['external_selected_funding_instrument_type']);

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
            'external_selected_funding_instrument_type' => $this->getExternalSelectedFundingInstrumentType(),
        ];

        if ($this->payerInfo !== null) {
            $result['payer_info'] = $this->payerInfo->toArray();
        }

        return $result;
    }
}
