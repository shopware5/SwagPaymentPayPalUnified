<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Action;
use Enlight_Controller_Request_RequestTestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard;
use Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons;
use sOrder;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilderFactory;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

// This is a workaround, because the controller will otherwise not found in this test
require __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2SmartPaymentButtons.php';

class AbstractPaypalPaymentControllerHandleCommentTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;

    const COMMENT = 'This is a customer comment';

    /**
     * @dataProvider handleCommentTestDataProvider
     *
     * @param bool $setComment
     *
     * @return void
     */
    public function testHandleComment($setComment)
    {
        $comment = '';
        if ($setComment) {
            $comment = self::COMMENT;
        }

        $request = $this->createRequest($comment);

        $orderModule = $this->getOrderModule();

        $dependencyProvider = $this->createDependencyProvider($setComment, $orderModule);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider],
            $request
        );

        $reflectionMethod = $this->getReflectionMethod(Shopware_Controllers_Frontend_PaypalUnifiedV2::class, 'handleComment');

        $reflectionMethod->invoke($controller);

        static::assertSame($comment, $orderModule->sComment);
    }

    /**
     * @return Generator<array<int,bool>>
     */
    public function handleCommentTestDataProvider()
    {
        yield 'Without comment' => [
            false,
        ];

        yield 'With comment' => [
            true,
        ];
    }

    /**
     * @dataProvider controllersCallHandleCommentTestDataProvider
     *
     * @param class-string<Enlight_Controller_Action> $controllerClass
     * @param string                                  $actionName
     *
     * @return void
     */
    public function testControllersCallHandleComment($controllerClass, $actionName)
    {
        $request = $this->createRequest(self::COMMENT);

        $orderModule = $this->getOrderModule();

        $dependencyProvider = $this->createDependencyProvider(true, $orderModule);

        $controller = $this->getController(
            $controllerClass,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory(),
            ],
            $request
        );

        $controller->$actionName();

        static::assertSame(self::COMMENT, $orderModule->sComment);
    }

    /**
     * @return Generator<array<int,class-string<Enlight_Controller_Action>|string>>
     */
    public function controllersCallHandleCommentTestDataProvider()
    {
        yield 'Shopware_Controllers_Frontend_PaypalUnifiedV2' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            'indexAction',
        ];

        yield 'Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard' => [
            Shopware_Controllers_Widgets_PaypalUnifiedV2AdvancedCreditDebitCard::class,
            'createOrderAction',
        ];

        yield 'Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons' => [
            Shopware_Controllers_Widgets_PaypalUnifiedV2SmartPaymentButtons::class,
            'createOrderAction',
        ];
    }

    /**
     * @param bool $setComment
     *
     * @return DependencyProvider&MockObject
     */
    private function createDependencyProvider($setComment, sOrder $orderModule)
    {
        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn(
            $this->createSession($setComment)
        );

        $dependencyProvider->expects(static::once())->method('getModule')->with('order')
            ->willReturn($orderModule);

        return $dependencyProvider;
    }

    /**
     * @param bool $setComment
     *
     * @return Enlight_Components_Session_Namespace&MockObject
     */
    private function createSession($setComment)
    {
        $session = $this->createMock(Enlight_Components_Session_Namespace::class);
        $session->expects(static::once())->method('offsetSet')->with(
            AbstractPaypalPaymentController::COMMENT_KEY,
            $setComment ? self::COMMENT : ''
        );

        return $session;
    }

    /**
     * @return RedirectDataBuilderFactory&MockObject
     */
    private function createRedirectDataBuilderFactory()
    {
        $redirectDataBuilderFactory = $this->createMock(RedirectDataBuilderFactory::class);
        $redirectDataBuilderFactory->expects(static::once())->method('createRedirectDataBuilder')
            ->willReturn($this->createRedirectDataBuilder());

        return $redirectDataBuilderFactory;
    }

    /**
     * @return RedirectDataBuilder&MockObject
     */
    private function createRedirectDataBuilder()
    {
        $redirectDataBuilder = $this->createMock(RedirectDataBuilder::class);
        $redirectDataBuilder->expects(static::once())->method('setCode')->willReturnSelf();

        return $redirectDataBuilder;
    }

    /**
     * @return sOrder
     */
    private function getOrderModule()
    {
        $orderModule = $this->getContainer()->get('modules')->getModule('order');
        static::assertInstanceOf(sOrder::class, $orderModule);

        return $orderModule;
    }

    /**
     * @param string $comment
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest($comment)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam(AbstractPaypalPaymentController::COMMENT_KEY, $comment);

        return $request;
    }
}
