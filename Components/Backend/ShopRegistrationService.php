<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Backend;

use InvalidArgumentException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class ShopRegistrationService
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ShopRegistrationServiceInterface|null
     */
    private $shopRegistrationService;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    public function __construct(ModelManager $modelManager, SettingsServiceInterface $settingsService, ShopRegistrationServiceInterface $shopRegistrationService = null)
    {
        $this->settingsService = $settingsService;
        $this->shopRegistrationService = $shopRegistrationService;

        $this->shopRepository = $modelManager->getRepository(Shop::class);
    }

    /**
     * @param int $shopId
     */
    public function registerShopById($shopId)
    {
        if (!\is_int($shopId)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'The parameter "shopId" does not match. Expected int, got: %s, of type %s.',
                    $shopId,
                    \gettype($shopId)
                )
            );
        }

        $shop = $this->shopRepository->getActiveById($shopId);
        if (!$shop instanceof Shop) {
            $shop = $this->shopRepository->getActiveDefault();
        }

        if ($this->shopRegistrationService !== null) {
            $this->shopRegistrationService->registerResources($shop);
        } else {
            $shop->registerResources();
        }

        $this->settingsService->refreshDependencies();
    }
}
