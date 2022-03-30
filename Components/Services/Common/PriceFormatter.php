<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

class PriceFormatter
{
    /**
     * @param float|string $price
     *
     * @return string
     */
    public function formatPrice($price)
    {
        return \number_format($this->roundPrice($price), 2, '.', '');
    }

    /**
     * @param float|string $price
     *
     * @return float
     */
    public function roundPrice($price)
    {
        $price = (string) $price;

        return \round((float) \str_replace(',', '.', $price), 2);
    }
}
