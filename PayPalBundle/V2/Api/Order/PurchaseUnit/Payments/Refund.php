<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund\SellerPayableBreakdown;

class Refund extends Payment
{
    /**
     * @var string|null
     */
    protected $invoiceId;

    /**
     * @var string|null
     */
    protected $noteToPayer;

    /**
     * @var SellerPayableBreakdown
     */
    protected $sellerPayableBreakdown;

    /**
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string|null $invoiceId
     *
     * @throws \LengthException if given parameter is too long
     *
     * @return void
     */
    public function setInvoiceId($invoiceId)
    {
        if ($invoiceId !== null && \mb_strlen($invoiceId) > self::MAX_LENGTH_INVOICE_ID) {
            throw new \LengthException(
                \sprintf('%s::$invoiceId must not be longer than %s characters', self::class, self::MAX_LENGTH_INVOICE_ID)
            );
        }

        $this->invoiceId = $invoiceId;
    }

    /**
     * @return string|null
     */
    public function getNoteToPayer()
    {
        return $this->noteToPayer;
    }

    /**
     * @param string|null $noteToPayer
     *
     * @throws \LengthException if given parameter is too long
     *
     * @return void
     */
    public function setNoteToPayer($noteToPayer)
    {
        if ($noteToPayer !== null && \mb_strlen($noteToPayer) > self::MAX_LENGTH_NOTE_TO_PAYER) {
            throw new \LengthException(
                \sprintf('%s::$invoiceId must not be longer than %s characters', self::class, self::MAX_LENGTH_NOTE_TO_PAYER)
            );
        }

        $this->noteToPayer = $noteToPayer;
    }

    /**
     * @return SellerPayableBreakdown
     */
    public function getSellerPayableBreakdown()
    {
        return $this->sellerPayableBreakdown;
    }

    /**
     * @return void
     */
    public function setSellerPayableBreakdown(SellerPayableBreakdown $sellerPayableBreakdown)
    {
        $this->sellerPayableBreakdown = $sellerPayableBreakdown;
    }
}
