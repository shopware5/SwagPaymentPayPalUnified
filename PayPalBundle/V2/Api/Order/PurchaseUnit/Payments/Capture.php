<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;

use LengthException;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\SellerProtection;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\SellerReceivableBreakdown;

class Capture extends Payment
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
     * @var SellerProtection
     */
    protected $sellerProtection;

    /**
     * @var bool|null
     */
    protected $finalCapture;

    /**
     * @var SellerReceivableBreakdown
     */
    protected $sellerReceivableBreakdown;

    /**
     * @var string
     */
    protected $disbursementMode;

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
     * @throws LengthException if given parameter is too long
     *
     * @return void
     */
    public function setInvoiceId($invoiceId)
    {
        if ($invoiceId !== null && \mb_strlen($invoiceId) > self::MAX_LENGTH_INVOICE_ID) {
            throw new LengthException(
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
     * @throws LengthException if given parameter is too long
     *
     * @return void
     */
    public function setNoteToPayer($noteToPayer)
    {
        if ($noteToPayer !== null && \mb_strlen($noteToPayer) > self::MAX_LENGTH_NOTE_TO_PAYER) {
            throw new LengthException(
                \sprintf('%s::$invoiceId must not be longer than %s characters', self::class, self::MAX_LENGTH_NOTE_TO_PAYER)
            );
        }

        $this->noteToPayer = $noteToPayer;
    }

    /**
     * @return SellerProtection
     */
    public function getSellerProtection()
    {
        return $this->sellerProtection;
    }

    /**
     * @return void
     */
    public function setSellerProtection(SellerProtection $sellerProtection)
    {
        $this->sellerProtection = $sellerProtection;
    }

    /**
     * @return bool|null
     */
    public function isFinalCapture()
    {
        return $this->finalCapture;
    }

    /**
     * @param bool|null $finalCapture
     *
     * @return void
     */
    public function setFinalCapture($finalCapture)
    {
        $this->finalCapture = $finalCapture;
    }

    /**
     * @return SellerReceivableBreakdown
     */
    public function getSellerReceivableBreakdown()
    {
        return $this->sellerReceivableBreakdown;
    }

    /**
     * @return void
     */
    public function setSellerReceivableBreakdown(SellerReceivableBreakdown $sellerReceivableBreakdown)
    {
        $this->sellerReceivableBreakdown = $sellerReceivableBreakdown;
    }

    /**
     * @return string
     */
    public function getDisbursementMode()
    {
        return $this->disbursementMode;
    }

    /**
     * @param string $disbursementMode
     *
     * @return void
     */
    public function setDisbursementMode($disbursementMode)
    {
        $this->disbursementMode = $disbursementMode;
    }
}
