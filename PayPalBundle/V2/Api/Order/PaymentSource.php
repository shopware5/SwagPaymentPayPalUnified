<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Bancontact;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Blik;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Eps;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Giropay;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Ideal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Multibanco;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Mybank;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Oxxo;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\P24;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Sofort;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Trustly;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class PaymentSource extends PayPalApiStruct
{
    /**
     * @var PayUponInvoice|null
     */
    protected $payUponInvoice;

    /**
     * @var Bancontact|null
     */
    protected $bancontact;

    /**
     * @var Blik|null
     */
    protected $blik;

    /**
     * @var Eps|null
     */
    protected $eps;

    /**
     * @var Giropay|null
     */
    protected $giropay;

    /**
     * @var Ideal|null
     */
    protected $ideal;

    /**
     * @var Multibanco|null
     */
    protected $multibanco;

    /**
     * @var Mybank|null
     */
    protected $mybank;

    /**
     * @var Oxxo|null
     */
    protected $oxxo;

    /**
     * @var P24|null
     */
    protected $p24;

    /**
     * @var Sofort|null
     */
    protected $sofort;

    /**
     * @var Trustly|null
     */
    protected $trustly;

    /**
     * @var Card|null
     */
    protected $card;

    /**
     * @return PayUponInvoice|null
     */
    public function getPayUponInvoice()
    {
        return $this->payUponInvoice;
    }

    /**
     * @param PayUponInvoice|null $payUponInvoice
     */
    public function setPayUponInvoice($payUponInvoice)
    {
        $this->payUponInvoice = $payUponInvoice;
    }

    /**
     * @return Bancontact|null
     */
    public function getBancontact()
    {
        return $this->bancontact;
    }

    /**
     * @param Bancontact|null $bancontact
     */
    public function setBancontact($bancontact)
    {
        $this->bancontact = $bancontact;
    }

    /**
     * @return Blik|null
     */
    public function getBlik()
    {
        return $this->blik;
    }

    /**
     * @param Blik|null $blik
     */
    public function setBlik($blik)
    {
        $this->blik = $blik;
    }

    /**
     * @return Eps|null
     */
    public function getEps()
    {
        return $this->eps;
    }

    /**
     * @param Eps|null $eps
     */
    public function setEps($eps)
    {
        $this->eps = $eps;
    }

    /**
     * @return Giropay|null
     */
    public function getGiropay()
    {
        return $this->giropay;
    }

    /**
     * @param Giropay|null $giropay
     */
    public function setGiropay($giropay)
    {
        $this->giropay = $giropay;
    }

    /**
     * @return Ideal|null
     */
    public function getIdeal()
    {
        return $this->ideal;
    }

    /**
     * @param Ideal|null $ideal
     */
    public function setIdeal($ideal)
    {
        $this->ideal = $ideal;
    }

    /**
     * @return Multibanco|null
     */
    public function getMultibanco()
    {
        return $this->multibanco;
    }

    /**
     * @param Multibanco|null $multibanco
     */
    public function setMultibanco($multibanco)
    {
        $this->multibanco = $multibanco;
    }

    /**
     * @return Mybank|null
     */
    public function getMybank()
    {
        return $this->mybank;
    }

    /**
     * @param Mybank|null $mybank
     */
    public function setMybank($mybank)
    {
        $this->mybank = $mybank;
    }

    /**
     * @return Oxxo|null
     */
    public function getOxxo()
    {
        return $this->oxxo;
    }

    /**
     * @param Oxxo|null $oxxo
     */
    public function setOxxo($oxxo)
    {
        $this->oxxo = $oxxo;
    }

    /**
     * @return P24|null
     */
    public function getP24()
    {
        return $this->p24;
    }

    /**
     * @param P24|null $p24
     */
    public function setP24($p24)
    {
        $this->p24 = $p24;
    }

    /**
     * @return Sofort|null
     */
    public function getSofort()
    {
        return $this->sofort;
    }

    /**
     * @param Sofort|null $sofort
     */
    public function setSofort($sofort)
    {
        $this->sofort = $sofort;
    }

    /**
     * @return Trustly|null
     */
    public function getTrustly()
    {
        return $this->trustly;
    }

    /**
     * @param Trustly|null $trustly
     */
    public function setTrustly($trustly)
    {
        $this->trustly = $trustly;
    }

    /**
     * @return Card|null
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param Card|null $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }
}
