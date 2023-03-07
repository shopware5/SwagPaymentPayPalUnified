<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\Routing\RouterInterface;
use UnexpectedValueException;

class UrlBuilder
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array<string,mixed>
     */
    private $routingParameter;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        $this->routingParameter = [
            'forceSecure' => true,
        ];
    }

    /**
     * @param string $controllerName
     *
     * @return UrlBuilder
     */
    public function setController($controllerName)
    {
        $this->routingParameter['controller'] = $controllerName;

        return $this;
    }

    /**
     * @param string $controllerActionName
     *
     * @return UrlBuilder
     */
    public function setControllerAction($controllerActionName)
    {
        $this->routingParameter['action'] = $controllerActionName;

        return $this;
    }

    /**
     * @param string $basketUniqueId
     *
     * @return UrlBuilder
     */
    public function setBasketUniqueId($basketUniqueId)
    {
        $this->routingParameter['basketId'] = $basketUniqueId;

        return $this;
    }

    /**
     * @param string $paymentToken
     *
     * @return UrlBuilder
     */
    public function setPaymentToken($paymentToken)
    {
        $this->routingParameter[PaymentTokenService::TYPE_PAYMENT_TOKEN] = $paymentToken;

        return $this;
    }

    /**
     * @param array<string,mixed> $additionalParameter
     *
     * @return UrlBuilder
     */
    public function setAdditionalParameter(array $additionalParameter)
    {
        foreach ($additionalParameter as $key => $value) {
            $this->routingParameter[$key] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function buildUrl()
    {
        $url = $this->router->assemble($this->routingParameter);

        if (!\is_string($url)) {
            throw new UnexpectedValueException('Could not assemble URL');
        }

        return $url;
    }
}
