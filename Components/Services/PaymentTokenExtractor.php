<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class PaymentTokenExtractor
{
    /**
     * @return string
     */
    public static function extract(Payment $paymentResource)
    {
        $token = '';

        /** @var Link $link */
        foreach ($paymentResource->getLinks() as $link) {
            if (!($link->getRel() === 'approval_url')) {
                continue;
            }

            \preg_match('/EC-\w+/', $link->getHref(), $matches);

            if (!empty($matches)) {
                $token = $matches[0];
            }
        }

        return $token;
    }
}
