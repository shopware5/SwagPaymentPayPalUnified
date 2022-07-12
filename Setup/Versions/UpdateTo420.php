<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Status;
use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModelFactory;

class UpdateTo420
{
    /**
     * @var PaymentModelFactory
     */
    private $paymentModelFactory;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    public function __construct(
        PaymentModelFactory $paymentModelFactory,
        ModelManager $modelManager,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->paymentModelFactory = $paymentModelFactory;
        $this->modelManager = $modelManager;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->installPayLater();
        $this->migrateOrderStatus();
    }

    /**
     * @return void
     */
    private function installPayLater()
    {
        $payment = $this->paymentMethodProvider->getPaymentMethodModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME);
        if ($payment instanceof Payment) {
            // If the payment method already exists, don't add it again.
            return;
        }

        $payLater = $this->paymentModelFactory->getPaymentModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)->create();
        $this->modelManager->persist($payLater);
        $this->modelManager->flush($payLater);
    }

    /**
     * @return void
     */
    private function migrateOrderStatus()
    {
        $this->modelManager->getConnection()->createQueryBuilder()
            ->update('swag_payment_paypal_unified_settings_general')
            ->set('order_status_on_failed_payment', (string) Status::ORDER_STATE_CANCELLED_REJECTED)
            ->where('order_status_on_failed_payment = -1')
            ->execute();
    }
}
