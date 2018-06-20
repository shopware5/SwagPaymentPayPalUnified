<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class Uninstaller
{
    /**
     * @var CrudService
     */
    private $attributeCrudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CrudService  $attributeCrudService
     * @param ModelManager $modelManager
     */
    public function __construct(CrudService $attributeCrudService, ModelManager $modelManager)
    {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
    }

    public function uninstall()
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'swag_paypal_unified_display_in_plus_iframe') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'swag_paypal_unified_display_in_plus_iframe'
            );
        }
        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'swag_paypal_unified_plus_iframe_payment_logo') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'swag_paypal_unified_plus_iframe_payment_logo'
            );
        }
        $this->modelManager->generateAttributeModels(['s_core_paymentmeans_attributes']);
    }
}
