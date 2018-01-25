<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

class PaymentOrderNumberPatch implements PatchInterface
{
    const PATH = '/transactions/0/invoice_number';

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @param string $orderNumber
     */
    public function __construct($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return self::OPERATION_ADD;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return self::PATH;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->orderNumber;
    }
}
