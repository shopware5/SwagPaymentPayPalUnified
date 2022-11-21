<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION
{
    /**
     * @var CrudServiceInterface
     */
    private $crudService;

    public function __construct(
        CrudServiceInterface $crudService
    ) {
        $this->crudService = $crudService;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->createAttributes();
    }

    /**
     * @return void
     */
    private function createAttributes()
    {
        $this->crudService->update('s_order_attributes', 'swag_paypal_unified_carrier_was_sent', 'boolean');

        $this->crudService->update('s_order_attributes', 'swag_paypal_unified_carrier', 'string', [
            'displayInBackend' => true,
            'label' => 'Carrier code',
            'helpText' => 'Enter a PayPal carrier code (e.g. DHL_GLOBAL_ECOMMERCE)...',
            'translatable' => true,
            'supportText' => 'PayPal offers tracking for orders processed through PayPal. To use this, specify a default shipping carrier, which can be overwritten in the orders. Find a list of all shipping providers <a target="_blank" href="https://developer.paypal.com/docs/tracking/reference/carriers/">here</a>',
            'position' => 100,
        ]);

        $this->crudService->update('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier', 'string', [
            'displayInBackend' => true,
            'label' => 'Carrier code',
            'helpText' => 'Enter a PayPal carrier code (e.g. DHL_GLOBAL_ECOMMERCE)...',
            'translatable' => true,
            'supportText' => 'PayPal offers tracking for orders processed through PayPal. To use this, specify a default shipping carrier, which can be overwritten in the orders. Find a list of all shipping providers <a target="_blank" href="https://developer.paypal.com/docs/tracking/reference/carriers/">here</a>',
            'position' => 100,
        ]);
    }
}
