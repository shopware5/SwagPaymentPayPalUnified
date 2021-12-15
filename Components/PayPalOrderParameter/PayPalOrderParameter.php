<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

class PayPalOrderParameter
{
    /**
     * @var array
     */
    private $customer;

    /**
     * @var array
     */
    private $cart;

    /**
     * @var string
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
     * @param string      $paymentType
     * @param string|null $basketUniqueId
     * @param string|null $paymentToken
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
     * @return array
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return array
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return string
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
