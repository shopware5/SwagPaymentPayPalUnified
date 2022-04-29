<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Enlight_Components_Session_Namespace;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use sBasket;
use Shopware\Models\Attribute\OrderBasket;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\CartRestoreService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\LoggerMock;

class CartRestoreServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;

    const EXPECTED_ORDER_NUMBER_RESULT = [
        'SW10003' => false,
        'SW10006' => false,
        'SW10008' => false,
        'SW10178' => false,
        'SHIPPINGDISCOUNT' => false,
    ];

    const ATTRIBUTE_PREFIX = 'Attr';

    /**
     * @return void
     */
    public function testGetBasketData()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/basket_restore_basket.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $basketRestoreService = $this->createBasketRestoreService();

        $basketData = $basketRestoreService->getCartData();

        static::assertCount(5, $basketData);

        $expectedOrderNumbers = self::EXPECTED_ORDER_NUMBER_RESULT;

        foreach ($expectedOrderNumbers as $expectedOrderNumber => $found) {
            foreach ($basketData as $basketItem) {
                if ($basketItem->getOrderNumber() === $expectedOrderNumber) {
                    $expectedOrderNumbers[$expectedOrderNumber] = true;
                }
            }
        }

        foreach ($expectedOrderNumbers as $expectedOrderNumber => $found) {
            static::assertTrue($found, sprintf('Expected order number "%s" is not in result', $expectedOrderNumber));
        }
    }

    /**
     * @return void
     */
    public function testRestoreBasket()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/basket_restore_basket.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $basketRestoreService = $this->createBasketRestoreService();

        $basketDataToRestore = $basketRestoreService->getCartData();

        /** @var sBasket $sBasket */
        $sBasket = $this->getContainer()->get('modules')->getModule('sBasket');

        $reflectionBasketSessionProperty = (new ReflectionClass(sBasket::class))->getProperty('session');
        $reflectionBasketSessionProperty->setAccessible(true);
        $reflectionBasketSessionProperty->setValue($sBasket, $this->createSessionMock());

        $sBasket->sDeleteBasket();

        $checkBasketResult = $basketRestoreService->getCartData();
        static::assertCount(0, $checkBasketResult);

        $basketRestoreService->restoreCart($basketDataToRestore);

        $resettedBasketData = $basketRestoreService->getCartData();

        $expectedOrderNumbers = self::EXPECTED_ORDER_NUMBER_RESULT;

        static::assertCount(5, $resettedBasketData);

        foreach ($expectedOrderNumbers as $expectedOrderNumber => $found) {
            foreach ($resettedBasketData as $basketItem) {
                if ($basketItem->getOrderNumber() === $expectedOrderNumber) {
                    $expectedOrderNumbers[$expectedOrderNumber] = true;
                }

                $basketAttributes = $this->getContainer()->get('models')->getRepository(OrderBasket::class)
                    ->findOneBy(['orderBasketId' => $basketItem->getId()]);

                if ($basketAttributes === null) {
                    static::assertSame('SHIPPINGDISCOUNT', $basketItem->getOrderNumber());
                } elseif (\method_exists($this, 'assertStringContainsString')) {
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute1());
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute2());
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute3());
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute4());
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute5());
                    static::assertStringContainsString(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute6());
                } else {
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute1());
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute2());
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute3());
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute4());
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute5());
                    static::assertContains(self::ATTRIBUTE_PREFIX, (string) $basketAttributes->getAttribute6());
                }
            }
        }

        foreach ($expectedOrderNumbers as $expectedOrderNumber => $found) {
            static::assertTrue($found, sprintf('Expected order number "%s" is not in result', $expectedOrderNumber));
        }
    }

    /**
     * @return CartRestoreService
     */
    private function createBasketRestoreService()
    {
        return new CartRestoreService(
            $this->createDependencyProviderMock(),
            $this->getContainer()->get('models'),
            new LoggerMock()
        );
    }

    /**
     * @return DependencyProvider
     */
    private function createDependencyProviderMock()
    {
        $sessionMock = $this->createSessionMock();

        $dependencyProviderMock = $this->createMock(DependencyProvider::class);
        $dependencyProviderMock->method('getSession')->willReturn($sessionMock);

        return $dependencyProviderMock;
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    private function createSessionMock()
    {
        $sessionMock = $this->createMock(Enlight_Components_Session_Namespace::class);
        if (method_exists(Enlight_Components_Session_Namespace::class, 'getId')) {
            $sessionMock->method('getId')->willReturn('restoreBasketSessionId');
        }
        $sessionMock->method('get')->willReturn('restoreBasketSessionId');
        $sessionMock->method('offsetGet')->willReturn('restoreBasketSessionId');

        return $sessionMock;
    }
}
