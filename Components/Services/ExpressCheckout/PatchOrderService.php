<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use Exception;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacadeInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Components\Services\LoggerService;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping\Address;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Patches\OrderPurchaseUnitShippingPatch;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class PatchOrderService
{
    /**
     * @var PayPalOrderParameterFacadeInterface
     */
    private $orderParameterFacade;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var LoggerService
     */
    private $loggerService;

    public function __construct(
        PayPalOrderParameterFacadeInterface $orderParameterFacade,
        OrderFactory $orderFactory,
        OrderResource $orderResource,
        LoggerService $loggerService
    ) {
        $this->orderParameterFacade = $orderParameterFacade;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->loggerService = $loggerService;
    }

    /**
     * @param string $payPalOrderId
     *
     * @return void
     */
    public function patchPayPalExpressOrder(Patch $patch, $payPalOrderId)
    {
        try {
            $this->orderResource->update([$patch], $payPalOrderId);
        } catch (Exception $exception) {
            $this->loggerService->warning(sprintf('%s CANNOT PATCH EXPRESS ORDER ADDRESS. OrderId: %s', __METHOD__, $payPalOrderId));
        }
    }

    /**
     * @param array<string,mixed> $customerData
     *
     * @return Patch|null
     */
    public function createExpressShippingAddressPatch(array $customerData)
    {
        $patch = new OrderPurchaseUnitShippingPatch();
        $patch->setPath(OrderPurchaseUnitShippingPatch::PATH);
        $patch->setOp(Patch::OPERATION_REPLACE);

        $shopwareOrderData = new ShopwareOrderData($customerData, []);
        $orderParams = $this->orderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2, $shopwareOrderData);
        $order = $this->orderFactory->createOrder($orderParams);

        $purchaseUnit = $order->getPurchaseUnits()[0];
        if (!$purchaseUnit instanceof PurchaseUnit) {
            $this->loggerService->warning(sprintf('%s CANNOT CREATE PATCH. REQUIRED "PurchaseUnit" NOT FOUND', __METHOD__));

            return null;
        }

        $shipping = $purchaseUnit->getShipping();
        if (!$shipping instanceof Shipping) {
            $this->loggerService->warning(sprintf('%s CANNOT CREATE PATCH. REQUIRED "Shipping" NOT FOUND', __METHOD__));

            return null;
        }

        $shippingAddress = $shipping->getAddress();
        if (!$shippingAddress instanceof Address) {
            $this->loggerService->warning(sprintf('%s CANNOT CREATE PATCH. REQUIRED "Address" NOT FOUND', __METHOD__));

            return null;
        }

        $patch->setValue($shippingAddress->toArray());

        $this->loggerService->debug(sprintf('%s PATCH CREATED', __METHOD__));

        return $patch;
    }
}
