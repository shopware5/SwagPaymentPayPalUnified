<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services\PayPalOrder;

use Enlight_Components_Snippet_Namespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Snippet_Manager;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;

class ItemListProviderTest extends TestCase
{
    /**
     * @var MockObject|LoggerServiceInterface
     */
    private $loggerService;

    /**
     * @var MockObject|Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var MockObject|PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var MockObject|CustomerHelper
     */
    private $customerHelper;

    /**
     * @var MockObject|Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->loggerService = static::createMock(LoggerServiceInterface::class);
        $this->snippetManager = static::createMock(Shopware_Components_Snippet_Manager::class);
        $this->priceFormatter = static::createMock(PriceFormatter::class);
        $this->customerHelper = static::createMock(CustomerHelper::class);
        $this->snippetNamespace = static::createMock(Enlight_Components_Snippet_Namespace::class);

        $this->prepareSnippetManager();
    }

    /**
     * @return void
     */
    public function testItDoesNotAddTaxToTheItemsWhenItShouldnt()
    {
        $this->givenValueAddedTaxShouldNotBeCharged();

        $itemList = $this->getItemListProvider()->getItemList(
            Fixture::CART,
            Fixture::CUSTOMER,
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2
        );

        static::assertEmpty((float) $this->getFirstItem($itemList)->getTax()->getValue());
    }

    /**
     * We currently expect getItemList to round the item unit amount and tax
     * amount, therefore the comparison is done with sprintf('%.2f').
     *
     * This behaviour will lead to rounding errors, but we can anticipate them
     * this way and prepare a breakdown struct, which the PayPal-API will
     * accept.
     *
     * @return void
     */
    public function testItCalculatesItemValuesCorrectly()
    {
        $this->givenValueAddedTaxShouldBeCharged();

        $itemList = $this->getItemListProvider(
            null,
            null,
            new PriceFormatter()
        )->getItemList(
            Fixture::CART,
            Fixture::CUSTOMER,
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2
        );

        $item = $this->getFirstItem($itemList);
        $price = Fixture::getPrice();
        $taxRate = Fixture::getTaxRate();

        static::assertSame(sprintf('%.2f', $price / $taxRate), $item->getUnitAmount()->getValue());
        static::assertSame(sprintf('%.2f', $price - $price / $taxRate), $item->getTax()->getValue());
    }

    /**
     * @param Item[]|null $itemList
     *
     * @return Item
     */
    protected function getFirstItem($itemList)
    {
        if (\method_exists(self::class, 'assertIsArray')) {
            static::assertIsArray($itemList);
        } else {
            static::assertTrue(\is_array($itemList));
        }

        static::assertNotEmpty($itemList);

        $item = array_pop($itemList);

        static::assertInstanceOf(Item::class, $item);

        return $item;
    }

    /**
     * @param LoggerServiceInterface|null              $loggerService
     * @param Shopware_Components_Snippet_Manager|null $snippetManager
     * @param PriceFormatter|null                      $priceFormatter
     * @param CustomerHelper|null                      $customerHelper
     *
     * @return ItemListProvider
     */
    protected function getItemListProvider(
        $loggerService = null,
        $snippetManager = null,
        $priceFormatter = null,
        $customerHelper = null
    ) {
        return new ItemListProvider(
            $loggerService ?: $this->loggerService,
            $snippetManager ?: $this->snippetManager,
            $priceFormatter ?: $this->priceFormatter,
            $customerHelper ?: $this->customerHelper
        );
    }

    /**
     * @return void
     */
    private function prepareSnippetManager()
    {
        $this->snippetNamespace->method('get')
            ->will(static::returnSelf());

        $this->snippetManager->method('getNamespace')
            ->willReturn($this->snippetNamespace);
    }

    /**
     * @return void
     */
    private function givenValueAddedTaxShouldBeCharged()
    {
        $this->customerHelper->method('chargeVat')
            ->willReturn(true);
    }

    /**
     * @return void
     */
    private function givenValueAddedTaxShouldNotBeCharged()
    {
        $this->customerHelper->method('chargeVat')
            ->willReturn(false);
    }
}
