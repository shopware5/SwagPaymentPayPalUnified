<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

class NonceService
{
    /**
     * @param int<0,128> $length
     *
     * @see https://datatracker.ietf.org/doc/html/rfc4648#section-5 on Base64Url encoding
     * @see https://datatracker.ietf.org/doc/html/rfc7636#section-4.1 on client nonce generation for OAuth PKCE
     * @see https://developer.paypal.com/docs/multiparty/seller-onboarding/build-onboarding/#link-modifythecode on PayPals use of PKCE
     *
     * @return string
     */
    public function getBase64UrlEncodedRandomNonce($length = 32)
    {
        $bytes = (string) \openssl_random_pseudo_bytes(\min($length, 128));

        return rtrim(strtr(\base64_encode($bytes), '+/', '-_'), '=');
    }
}
