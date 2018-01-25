<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class Capture extends RelatedResource
{
    /**
     * @var TransactionFee
     */
    private $transactionFee;

    /**
     * @return TransactionFee
     */
    public function getTransactionFee()
    {
        return $this->transactionFee;
    }

    /**
     * @param TransactionFee $transactionFee
     */
    public function setTransactionFee($transactionFee)
    {
        $this->transactionFee = $transactionFee;
    }

    /**
     * @param array $data
     *
     * @return Capture
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->prepare($result, $data, ResourceType::CAPTURE);

        if (is_array($data['transaction_fee'])) {
            $result->setTransactionFee(TransactionFee::fromArray($data['transaction_fee']));
        }

        return $result;
    }
}
