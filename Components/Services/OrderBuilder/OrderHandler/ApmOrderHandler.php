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
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceFactory;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\ProcessingInstruction;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;

class ApmOrderHandler extends AbstractOrderHandler
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
        PaymentType::APM_MULTIBANCO,
    ];

    /**
     * @var PaymentSourceFactory
     */
    protected $paymentSourceFactory;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ItemListProvider $itemListProvider,
        AmountProvider $amountProvider,
        ReturnUrlHelper $returnUrlHelper,
        ContextServiceInterface $contextService,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper,
        PaymentSourceFactory $paymentSourceFactory
    ) {
        parent::__construct(
            $settingsService,
            $itemListProvider,
            $amountProvider,
            $returnUrlHelper,
            $contextService,
            $priceFormatter,
            $customerHelper
        );

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

        $applicationContext = $this->createApplicationContext($orderParameter, ['controller' => 'PaypalUnifiedApm']);
        $applicationContext->setLocale(
            str_replace('_', '-', $this->contextService->getShopContext()->getShop()->getLocale()->getLocale())
        );

        $order->setApplicationContext($applicationContext);
        $order->setPaymentSource($this->paymentSourceFactory->createPaymentSource($orderParameter));
        $order->setPurchaseUnits($this->createPurchaseUnits($orderParameter));
        $order->setPayer($this->createPayer($orderParameter));
        $order->setProcessingInstruction(ProcessingInstruction::ORDER_COMPLETE_ON_PAYMENT_APPROVAL);

        return $order;
    }
}
