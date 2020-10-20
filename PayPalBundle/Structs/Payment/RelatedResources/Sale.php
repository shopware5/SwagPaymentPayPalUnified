<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;

class Sale extends RelatedResource
{
    /**
     * @var string
     */
    private $paymentMode;

    /**
     * @var string
     */
    private $protectionEligibility;

    /**
     * @var string
     */
    private $protectionEligibilityType;

    /**
     * @var TransactionFee
     */
    private $transactionFee;

    /**
     * @var string
     */
    private $receiptId;

    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @param string $paymentMode
     */
    public function setPaymentMode($paymentMode)
    {
        $this->paymentMode = $paymentMode;
    }

    /**
     * @return string
     */
    public function getProtectionEligibility()
    {
        return $this->protectionEligibility;
    }

    /**
     * @param string $protectionEligibility
     */
    public function setProtectionEligibility($protectionEligibility)
    {
        $this->protectionEligibility = $protectionEligibility;
    }

    /**
     * @return string
     */
    public function getProtectionEligibilityType()
    {
        return $this->protectionEligibilityType;
    }

    /**
     * @param string $protectionEligibilityType
     */
    public function setProtectionEligibilityType($protectionEligibilityType)
    {
        $this->protectionEligibilityType = $protectionEligibilityType;
    }

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
     * @return string
     */
    public function getReceiptId()
    {
        return $this->receiptId;
    }

    /**
     * @param string $receiptId
     */
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return Sale
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->prepare($result, $data, ResourceType::SALE);

        $result->setPaymentMode($data['payment_mode']);
        $result->setProtectionEligibility($data['protection_eligibility']);
        $result->setProtectionEligibilityType($data['protection_eligibility_type']);
        $result->setReceiptId($data['receipt_id']);

        if (\is_array($data['transaction_fee'])) {
            $result->setTransactionFee(TransactionFee::fromArray($data['transaction_fee']));
        }

        $links = [];
        foreach ($data['links'] as $link) {
            $links[] = Link::fromArray($link);
        }

        $result->setLinks($links);

        return $result;
    }
}
