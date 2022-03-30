<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class PayPalOrderParameter
{
    /**
     * @var array<string,mixed>
     */
    private $customer;

    /**
     * @var array<string,mixed>
     */
    private $cart;

    /**
     * @var PaymentType::*
     */
    private $paymentType;

    /**
     * @var string|null
     */
    private $basketUniqueId;

    /**
     * @var string|null
     */
    private $paymentToken;

    /**
     * @param array<string,mixed> $customer
     * @param array<string,mixed> $cart
     * @param PaymentType::*      $paymentType
     * @param string|null         $basketUniqueId
     * @param string|null         $paymentToken
     */
    public function __construct(
        array $customer,
        array $cart,
        $paymentType,
        $basketUniqueId,
        $paymentToken
    ) {
        $this->customer = $customer;
        $this->cart = $cart;
        $this->paymentType = $paymentType;
        $this->basketUniqueId = $basketUniqueId;
        $this->paymentToken = $paymentToken;
    }

    /**
     * @return array<string,mixed>
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return array<string,mixed>
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return PaymentType::*
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function getBasketUniqueId()
    {
        return $this->basketUniqueId;
    }

    /**
     * @return string|null
     */
    public function getPaymentToken()
    {
        return $this->paymentToken;
    }
}
