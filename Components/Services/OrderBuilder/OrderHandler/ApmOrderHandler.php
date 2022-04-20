<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceFactory;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\ApmAmount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class ApmOrderHandler implements OrderBuilderHandlerInterface
{
    const SUPPORTED_PAYMENT_TYPES = [
        PaymentType::APM_BANCONTACT,
        PaymentType::APM_BLIK,
        PaymentType::APM_EPS,
        PaymentType::APM_GIROPAY,
        PaymentType::APM_IDEAL,
        PaymentType::APM_MYBANK,
        PaymentType::APM_P24,
        PaymentType::APM_SOFORT,
        PaymentType::APM_TRUSTLY,
    ];

    /**
     * @var ContextServiceInterface
     */
    protected $contextService;

    /**
     * @var ReturnUrlHelper
     */
    protected $returnUrlHelper;

    /**
     * @var PriceFormatter
     */
    protected $priceFormatter;

    /**
     * @var PaymentSourceFactory
     */
    protected $paymentSourceFactory;

    public function __construct(
        ContextServiceInterface $contextService,
        ReturnUrlHelper $returnUrlHelper,
        PriceFormatter $priceFormatter,
        PaymentSourceFactory $paymentSourceFactory
    ) {
        $this->contextService = $contextService;
        $this->returnUrlHelper = $returnUrlHelper;
        $this->priceFormatter = $priceFormatter;
        $this->paymentSourceFactory = $paymentSourceFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return \in_array($paymentType, self::SUPPORTED_PAYMENT_TYPES);
    }

    /**
     * {@inheritDoc}
     */
    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        $order = new Order();

        $order->setIntent(PaymentIntentV2::CAPTURE);
        $order->setApplicationContext($this->createApplicationContext($orderParameter));
        $order->setPaymentSource($this->paymentSourceFactory->createPaymentSource($orderParameter));
        $order->setPurchaseUnits($this->createPurchaseUnits($orderParameter));

        return $order;
    }

    /**
     * @return ApplicationContext
     */
    protected function createApplicationContext(PayPalOrderParameter $orderParameter)
    {
        $applicationContext = new ApplicationContext();

        $extraParams = [
            'controller' => 'PaypalUnifiedApm',
        ];

        $applicationContext->setCancelUrl($this->returnUrlHelper->getCancelUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken(), $extraParams));
        $applicationContext->setReturnUrl($this->returnUrlHelper->getReturnUrl($orderParameter->getBasketUniqueId(), $orderParameter->getPaymentToken(), $extraParams));
        $applicationContext->setLocale(
            str_replace('_', '-', $this->contextService->getShopContext()->getShop()->getLocale()->getLocale())
        );

        return $applicationContext;
    }

    /**
     * @return array<PurchaseUnit>
     */
    protected function createPurchaseUnits(PayPalOrderParameter $orderParameter)
    {
        $purchaseUnit = new PurchaseUnit();

        $amount = new ApmAmount();
        $amount->setValue($this->priceFormatter->formatPrice($orderParameter->getCart()['AmountNumeric']));
        $amount->setCurrencyCode($orderParameter->getCart()['sCurrencyName']);

        $purchaseUnit->setAmount($amount);

        return [$purchaseUnit];
    }
}
