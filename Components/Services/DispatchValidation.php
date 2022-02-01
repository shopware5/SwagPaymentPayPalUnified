<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware_Components_Config as Config;
use SwagPaymentPayPalUnified\Components\DependencyProvider;

class DispatchValidation
{
    /**
     * @var Config
     */
    private $shopwareConfig;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(Config $shopwareConfig, DependencyProvider $dependencyProvider)
    {
        $this->shopwareConfig = $shopwareConfig;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $session = $this->dependencyProvider->getSession();

        return !empty($this->shopwareConfig->get('premiumShippingNoOrder'))
            && (empty($session->get('sDispatch')) || empty($session->get('sCountry')));
    }
}
