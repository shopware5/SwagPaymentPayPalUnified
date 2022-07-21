<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Enlight_Class;
use Enlight_Controller_Request_RequestTestCase as RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase as ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;

class UnifiedControllerTestCase extends TestCase
{
    /**
     * @var RequestTestCase|null
     */
    private $request;

    /**
     * @var ResponseTestCase|null
     */
    private $response;

    /**
     * @return RequestTestCase
     */
    public function Request()
    {
        if ($this->request === null) {
            $this->request = new RequestTestCase();
        }

        return $this->request;
    }

    /**
     * @return ResponseTestCase
     */
    public function Response()
    {
        if ($this->response === null) {
            $this->response = new ResponseTestCase();
        }

        return $this->response;
    }

    /**
     * @template T of \Enlight_Controller_Action
     *
     * @param class-string<T> $controllerClass
     *
     * @return T
     */
    protected function getController($controllerClass, Container $container = null)
    {
        $controller = Enlight_Class::Instance(
            $controllerClass,
            [$this->Request(), $this->Response()]
        );

        if (!$controller instanceof $controllerClass) {
            throw new \UnexpectedValueException(sprintf('Expected instance of %s, got %s.', $controllerClass, \get_class($controller)));
        }

        $controller->setRequest($this->Request());
        $controller->setResponse($this->Response());

        if ($container instanceof Container) {
            $controller->setContainer($container);
        } else {
            $controller->setContainer(Shopware()->Container());
        }

        $controller->preDispatch();

        return $controller;
    }
}
