<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components\Patches;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer\PayerInfo;

class PayerInfoPatch implements PatchInterface
{
    const PATH = '/payer/payer_info';

    /**
     * @var PayerInfo
     */
    private $payerInfo;

    /**
     * @param PayerInfo $payerInfo
     */
    public function __construct(PayerInfo $payerInfo)
    {
        $this->payerInfo = $payerInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return self::OPERATION_REPLACE;
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
        return $this->payerInfo->toArray();
    }
}
