<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\PaymentModels;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;

class PaymentInstaller
{
    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var PaymentModelFactory
     */
    private $paymentModelFactory;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(
        PaymentMethodProviderInterface $paymentMethodProvider,
        PaymentModelFactory $paymentModelFactory,
        ModelManager $modelManager
    ) {
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentModelFactory = $paymentModelFactory;
        $this->modelManager = $modelManager;
    }

    public function installPayments()
    {
        $paymentMethods = $this->getPaymentMethods();

        foreach ($paymentMethods as $paymentMethodName) {
            $payment = $this->paymentMethodProvider->getPaymentMethodModel($paymentMethodName);

            if ($payment instanceof Payment) {
                // If the payment method already exists, don't add it again.
                continue;
            }

            $payment = $this->paymentModelFactory->getPaymentModel($paymentMethodName)->create();

            $this->modelManager->persist($payment);
            $this->modelManager->flush($payment);
        }
    }

    /**
     * @return list<PaymentMethodProvider::*>
     */
    private function getPaymentMethods()
    {
        $deactivatedPaymentMethods = PaymentMethodProvider::getDeactivatedPaymentMethods();
        $paymentMethods = \array_filter(
            PaymentMethodProvider::getAllUnifiedNames(),
            function ($paymentMethodName) use ($deactivatedPaymentMethods) {
                return !\in_array($paymentMethodName, $deactivatedPaymentMethods, true);
            },
            \ARRAY_FILTER_USE_BOTH
        );

        return $paymentMethods;
    }
}
