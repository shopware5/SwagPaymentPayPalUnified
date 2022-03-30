<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Shopware\Models\Shop\Shop;
use UnexpectedValueException;

class ButtonLocaleService
{
    const LOCALE_REGEX = '/^[a-z]{2}_[A-Z]{2}$/';

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(DependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @param mixed $default string|null
     *
     * @return string
     */
    public function getButtonLocale($default = null)
    {
        if ($default === null) {
            return $this->getButtonLocaleFromShop();
        }

        if (preg_match(self::LOCALE_REGEX, $default)) {
            return $default;
        }

        return $this->getButtonLocaleFromShop();
    }

    /**
     * @return string
     */
    private function getButtonLocaleFromShop()
    {
        $shop = $this->dependencyProvider->getShop();

        if (!$shop instanceof Shop) {
            throw new UnexpectedValueException(sprintf('Tried to access %s, but it\'s not set in the DIC.', Shop::class));
        }

        return $shop->getLocale()->getLocale();
    }
}
