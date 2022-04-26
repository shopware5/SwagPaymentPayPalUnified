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

    public function __construct(DependencyProvider $dependencyProvider, ModelManager $modelManager)
    {
        $this->dependencyProvider = $dependencyProvider;
        $this->modelManager = $modelManager;
    }

    /**
     * @return Cart[]
     */
    public function getCartData()
    {
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

        return $cart;
    }

    /**
     * @param array<Cart> $cartData
     *
     * @return void
     */
    public function restoreCart(array $cartData)
    {
        foreach ($cartData as $cartItem) {
            $this->modelManager->persist($cartItem);
        }

        $this->modelManager->flush();
    }
}
