<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGeneratorInterface;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class CartPersister
{
    /**
     * @var BasketSignatureGeneratorInterface|null
     */
    private $basketSignatureGenerator;

    /**
     * @var BasketPersister|null
     */
    private $basketPersister;

    public function __construct(
        BasketSignatureGeneratorInterface $basketSignatureGenerator = null,
        BasketPersister $basketPersister = null
    ) {
        $this->basketSignatureGenerator = $basketSignatureGenerator;
        $this->basketPersister = $basketPersister;
    }

    /**
     * @param int|null $customerId
     *
     * @phpstan-param CheckoutBasketArray $cart
     *
     * @return string|null
     */
    public function persist(array $cart, $customerId)
    {
        if (
            $this->basketPersister === null
            || $this->basketSignatureGenerator === null
            || $customerId === null
        ) {
            return null;
        }

        $signature = $this->basketSignatureGenerator->generateSignature($cart, $customerId);

        $this->basketPersister->persist($signature, $cart);

        return $signature;
    }
}
