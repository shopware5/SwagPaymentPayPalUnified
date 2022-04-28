<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\OrderBasket as CartAttributes;
use Shopware\Models\Order\Basket as Cart;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

class CartRestoreService
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function __construct(DependencyProvider $dependencyProvider, ModelManager $modelManager, LoggerServiceInterface $logger)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->modelManager = $modelManager;
        $this->logger = $logger;
    }

    /**
     * @return Cart[]
     */
    public function getCartData()
    {
        $this->logger->debug(sprintf('%s GET DOCTRINE CART OBJECTS', __METHOD__));
        $cart = $this->modelManager->getRepository(Cart::class)
            ->findBy(['sessionId' => $this->dependencyProvider->getSession()->offsetGet('sessionId')]);

        $itemIds = [];
        foreach ($cart as $cartItem) {
            $itemIds[] = $cartItem->getId();
            $this->modelManager->detach($cartItem);
        }

        $basketAttributes = $this->modelManager->getRepository(CartAttributes::class)
            ->findBy(['orderBasketId' => $itemIds]);

        foreach ($basketAttributes as $attribute) {
            $this->modelManager->detach($attribute);
        }

        $this->logger->debug(sprintf('%s RETURNED DOCTRINE CART OBJECTS', __METHOD__));

        return $cart;
    }

    /**
     * @param array<Cart> $cartData
     *
     * @return void
     */
    public function restoreCart(array $cartData)
    {
        $this->logger->debug(sprintf('%s RESTORE_CART', __METHOD__));

        foreach ($cartData as $cartItem) {
            $this->modelManager->persist($cartItem);
        }

        $this->modelManager->flush();

        $this->logger->debug(sprintf('%s RESTORE CART COMPLETED', __METHOD__));
    }
}
