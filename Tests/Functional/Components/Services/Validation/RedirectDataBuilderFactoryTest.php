<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Validation;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;

class RedirectDataBuilderFactoryTest extends TestCase
{
    public function testCreateRedirectDataBuilder()
    {
        $result = $this->getFactory()->createRedirectDataBuilder();

        static::assertInstanceOf(RedirectDataBuilder::class, $result);
    }

    private function getFactory()
    {
        return Shopware()->Container()->get('paypal_unified.redirect_data_builder_factory');
    }
}
