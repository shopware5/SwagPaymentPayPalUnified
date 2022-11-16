<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;

class UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSIONTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testUpdate()
    {
        $attributeCrudService = $this->getContainer()->get('shopware_attribute.crud_service');

        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier_was_sent', true);
        $attributeCrudService->delete('s_order_attributes', 'swag_paypal_unified_carrier', true);
        $attributeCrudService->delete('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier', true);

        $updater = new UpdateToREPLACE_GLOBAL_WITH_NEXT_VERSION($attributeCrudService);
        $updater->update();

        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier_was_sent'));
        static::assertNotNull($attributeCrudService->get('s_order_attributes', 'swag_paypal_unified_carrier'));
        static::assertNotNull($attributeCrudService->get('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier'));
    }
}
