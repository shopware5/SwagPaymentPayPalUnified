<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

use RuntimeException;
use Shopware\Components\Cart\PaymentTokenService;
use Shopware\Components\Routing\RouterInterface;

class ReturnUrlHelper
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string|null $basketUniqueId
     * @param string|null $paymentToken
     *
     * @return string
     */
    public function getReturnUrl($basketUniqueId, $paymentToken, array $additionalParameter = [])
    {
        return $this->getRedirectUrl('return', $basketUniqueId, $paymentToken, $additionalParameter);
    }

    /**
     * @param string|null $basketUniqueId
     * @param string|null $paymentToken
     *
     * @return string
     */
    public function getCancelUrl($basketUniqueId, $paymentToken, array $additionalParameter = [])
    {
        return $this->getRedirectUrl('cancel', $basketUniqueId, $paymentToken, $additionalParameter);
    }

    /**
     * @param string      $action
     * @param string|null $basketUniqueId
     * @param string|null $paymentToken
     *
     * @return string
     */
    private function getRedirectUrl($action, $basketUniqueId, $paymentToken, array $additionalParameter = [])
    {
        $routingParameters = [
            'controller' => 'PaypalUnifiedV2',
            'action' => $action,
            'forceSecure' => true,
        ];

        $routingParameters = array_merge($routingParameters, $additionalParameter);

        // Shopware 5.3+ supports cart validation.
        if (\is_string($basketUniqueId)) {
            $routingParameters['basketId'] = $basketUniqueId;
        }

        // Shopware 5.6+ supports session restoring
        if (\is_string($paymentToken)) {
            $routingParameters[PaymentTokenService::TYPE_PAYMENT_TOKEN] = $paymentToken;
        }

        $url = $this->router->assemble($routingParameters);
        if (!\is_string($url)) {
            throw new RuntimeException('Could not assemble URL');
        }

        return $url;
    }
}
