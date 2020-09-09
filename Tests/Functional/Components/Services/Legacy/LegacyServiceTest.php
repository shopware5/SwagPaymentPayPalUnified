<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Legacy;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class LegacyServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_construct()
    {
        $service = new LegacyService(Shopware()->Container()->get('dbal_connection'));

        static::assertInstanceOf(LegacyService::class, $service);
    }

    public function test_getClassicPaymentIds_returns_false_without_legacy_payment_methods()
    {
        $service = Shopware()->Container()->get('paypal_unified.legacy_service');

        static::assertEmpty($service->getClassicPaymentIds());
    }

    public function test_getClassicPaymentIds_returns_correct_id()
    {
        $this->insertClassicPayment();

        $result = Shopware()->Container()->get('paypal_unified.legacy_service')->getClassicPaymentIds();

        static::assertNotFalse($result);
        static::assertCount(2, $result);
    }

    private function insertClassicPayment()
    {
        $sql = "INSERT INTO s_core_paymentmeans(name, description, template, class, `table`, hide, additionaldescription, surchargestring, `position`, esdactive, embediframe, hideprospect)
				VALUES ('paypal', 'PayPal Classic', '', '', 0, 'PayPal Classic legacy', '', 0, 1, 0, 0, 0);
				INSERT INTO s_core_paymentmeans(name, description, template, class, `table`, hide, additionaldescription, surchargestring, `position`, esdactive, embediframe, hideprospect)
				VALUES ('payment_paypal_installments', 'PayPal Installments', '', '', 0, 'PayPal Installments legacy', '', 0, 1, 0, 0, 0);";

        Shopware()->Db()->executeUpdate($sql);
    }
}
