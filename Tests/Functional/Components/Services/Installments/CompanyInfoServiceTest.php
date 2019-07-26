<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Installments;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\Installments\CompanyInfoService;

class CompanyInfoServiceTest extends TestCase
{
    public function test_can_be_constructed()
    {
        $service = new CompanyInfoService(Shopware()->Container()->get('config'));

        static::assertNotNull($service);
    }

    public function test_is_available()
    {
        $service = Shopware()->Container()->get('paypal_unified.installments.company_info_service');

        static::assertSame(CompanyInfoService::class, get_class($service));
    }

    public function test_getCompanyInfo()
    {
        $result = (new CompanyInfoService(new ShopwareAddressConfigMock()))->getCompanyInfo();

        static::assertCount(2, $result);
        static::assertSame('Test address', $result['address']);
        static::assertSame('Test company', $result['name']);
    }
}

class ShopwareAddressConfigMock extends \Shopware_Components_Config
{
    public function __construct()
    {
    }

    public function get($name, $default = null)
    {
        if ($name === 'address') {
            return 'Test address';
        }

        if ($name === 'company') {
            return 'Test company';
        }

        return null;
    }
}
