<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Shopware\Components\Model\ModelManager;
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

    public function __construct(PaymentModelFactory $paymentModelFactory, ModelManager $modelManager)
    {
        $this->paymentModelFactory = $paymentModelFactory;
        $this->modelManager = $modelManager;
    }

    /**
     * @return void
     */
    public function update()
    {
        $payLater = $this->paymentModelFactory->getPaymentModel(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)->create();

        $this->modelManager->persist($payLater);
        $this->modelManager->flush($payLater);
    }
}
