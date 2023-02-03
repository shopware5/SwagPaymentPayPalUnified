<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Common;

use Shopware\Components\Routing\RouterInterface;

class ReturnUrlHelper
{
    const DEFAULT_CONTROLLER = 'PaypalUnifiedV2';

    const ACTION_RETURN = 'return';

    const ACTION_CANCEL = 'cancel';

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
        return $this->createRedirectUrl(self::DEFAULT_CONTROLLER, self::ACTION_RETURN, $basketUniqueId, $paymentToken, $additionalParameter);
    }

    /**
     * @param string|null $basketUniqueId
     * @param string|null $paymentToken
     *
     * @return string
     */
    public function getCancelUrl($basketUniqueId, $paymentToken, array $additionalParameter = [])
    {
        return $this->createRedirectUrl(self::DEFAULT_CONTROLLER, self::ACTION_CANCEL, $basketUniqueId, $paymentToken, $additionalParameter);
    }

    /**
     * @param string              $controller
     * @param string              $action
     * @param string|null         $basketUniqueId
     * @param string|null         $paymentToken
     * @param array<string,mixed> $additionalParameter
     *
     * @return string
     */
    public function createRedirectUrl(
        $controller,
        $action,
        $basketUniqueId,
        $paymentToken,
        array $additionalParameter = []
    ) {
        $urlBuilder = $this->createUrlBuilder()
            ->setController($controller)
            ->setControllerAction($action)
            ->setAdditionalParameter($additionalParameter);

        // Shopware 5.3+ supports cart validation.
        if (\is_string($basketUniqueId)) {
            $urlBuilder->setBasketUniqueId($basketUniqueId);
        }

        // Shopware 5.6+ supports session restoring
        if (\is_string($paymentToken)) {
            $urlBuilder->setPaymentToken($paymentToken);
        }

        return $urlBuilder->buildUrl();
    }

    /**
     * @return UrlBuilder
     */
    private function createUrlBuilder()
    {
        return new UrlBuilder($this->router);
    }
}
