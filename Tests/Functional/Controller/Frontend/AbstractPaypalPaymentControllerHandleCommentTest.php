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
require __DIR__ . '/../../../../Controllers/Widgets/PaypalUnifiedV2AdvancedCreditDebitCard.php';

class AbstractPaypalPaymentControllerHandleCommentTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;
    use ContainerTrait;

    const REQUEST_COMMENT = 'This is a customer comment from request';
    const SESSION_COMMENT = 'This is a customer comment from session';
    const EMPTY_COMMENT = '';

    /**
     * @dataProvider handleCommentTestDataProvider
     *
     * @param string      $expectedComment
     * @param string|null $requestComment
     * @param string|null $sessionComment
     *
     * @return void
     */
    public function testHandleComment($expectedComment, $requestComment = null, $sessionComment = null)
    {
        $request = $this->createRequest($requestComment);

        $orderModule = $this->getOrderModule();

        $dependencyProvider = $this->createDependencyProvider($orderModule, $requestComment, $sessionComment);

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider],
            $request
        );

        $reflectionMethod = $this->getReflectionMethod(Shopware_Controllers_Frontend_PaypalUnifiedV2::class, 'handleComment');

        $reflectionMethod->invoke($controller);

        static::assertSame($expectedComment, $orderModule->sComment);
    }

    /**
     * @return Generator<array<int,string|null>>
     */
    public function handleCommentTestDataProvider()
    {
        yield 'Without comment' => [
            self::EMPTY_COMMENT,
        ];

        yield 'With comment only in Request' => [
            self::REQUEST_COMMENT,
            self::REQUEST_COMMENT,
        ];

        yield 'With comment only in Session' => [
            self::SESSION_COMMENT,
            null,
            self::SESSION_COMMENT,
        ];

        yield 'With comment in Session and Request' => [
            self::REQUEST_COMMENT,
            self::REQUEST_COMMENT,
            self::SESSION_COMMENT,
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
        $request = $this->createRequest(self::REQUEST_COMMENT);

        $orderModule = $this->getOrderModule();

        $dependencyProvider = $this->createDependencyProvider($orderModule, self::REQUEST_COMMENT);

        $controller = $this->getController(
            $controllerClass,
            [
                self::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                self::SERVICE_REDIRECT_DATA_BUILDER_FACTORY => $this->createRedirectDataBuilderFactory(),
            ],
            $request
        );

        $controller->$actionName();

        static::assertSame(self::REQUEST_COMMENT, $orderModule->sComment);
    }

    /**
     * @return Generator<array<int,class-string<Enlight_Controller_Action>|string>>
     */
    public function controllersCallHandleCommentTestDataProvider()
    {
        yield 'Shopware_Controllers_Frontend_PaypalUnifiedV2::indexAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            'indexAction',
        ];

        yield 'Shopware_Controllers_Frontend_PaypalUnifiedV2::returnAction' => [
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            'returnAction',
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
     * @param string|null $requestComment
     * @param string|null $sessionComment
     *
     * @return DependencyProvider&MockObject
     */
    private function createDependencyProvider(sOrder $orderModule, $requestComment = null, $sessionComment = null)
    {
        $dependencyProvider = $this->createMock(DependencyProvider::class);
        $dependencyProvider->method('getSession')->willReturn(
            $this->createSession($requestComment, $sessionComment)
        );

        $dependencyProvider->expects(static::once())->method('getModule')->with('order')
            ->willReturn($orderModule);

        return $dependencyProvider;
    }

    /**
     * @param string|null $requestComment
     * @param string|null $sessionComment
     *
     * @return Enlight_Components_Session_Namespace&MockObject
     */
    private function createSession($requestComment = null, $sessionComment = null)
    {
        $expected = '';

        if ($sessionComment !== null) {
            $expected = $sessionComment;
        }

        if ($requestComment !== null) {
            $expected = $requestComment;
        }

        $session = $this->createMock(Enlight_Components_Session_Namespace::class);
        $session->expects(static::once())->method('offsetSet')->with(
            AbstractPaypalPaymentController::COMMENT_KEY,
            $expected
        );

        if ($sessionComment !== null && $requestComment === null) {
            $session->expects(static::once())->method('offsetExists')->willReturn(true);
            $session->method('offsetGet')->willReturn($sessionComment);
        }

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
     * @param string|null $comment
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest($comment = null)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam(AbstractPaypalPaymentController::COMMENT_KEY, $comment);

        return $request;
    }
}
