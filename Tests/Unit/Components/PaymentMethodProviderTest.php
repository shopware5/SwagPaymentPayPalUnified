<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components;

use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class PaymentMethodProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test_get_payment_method()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());

        $this->assertNotNull($provider->getPaymentMethodModel(), 'The payment method should not be null');
    }

    public function test_get_payment_method_model_installments()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());

        $this->assertNotNull($provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME));
    }

    public function test_set_payment_inactive()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(false);

        $payment = $provider->getPaymentMethodModel();
        $this->assertFalse($payment->getActive());
    }

    public function test_set_payment_inactive_installments()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $this->assertFalse($payment->getActive());
    }

    public function test_set_payment_active()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(true);

        $payment = $provider->getPaymentMethodModel();
        $this->assertTrue($payment->getActive());
    }

    public function test_set_payment_active_installments()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $provider->setPaymentMethodActiveFlag(true, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $payment = $provider->getPaymentMethodModel(PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $this->assertTrue($payment->getActive());
    }

    public function test_get_payment_id()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $paymentIdQuery = "SELECT pm.id FROM s_core_paymentmeans pm WHERE pm.name='SwagPaymentPayPalUnified'";

        $paymentId = Shopware()->Db()->fetchCol($paymentIdQuery)[0];

        $this->assertEquals($paymentId, $provider->getPaymentId(Shopware()->Container()->get('dbal_connection')));
    }

    public function test_get_payment_id_installments()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $paymentIdQuery = "SELECT pm.id FROM s_core_paymentmeans pm WHERE pm.name='SwagPaymentPayPalUnifiedInstallments'";

        $paymentId = Shopware()->Db()->fetchCol($paymentIdQuery)[0];

        $this->assertEquals($paymentId, $provider->getPaymentId(Shopware()->Container()->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME));
    }

    public function test_get_payment_active()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $activeFlag = $provider->getPaymentMethodActiveFlag(Shopware()->Container()->get('dbal_connection'));

        $this->assertTrue($activeFlag);
    }

    public function test_get_payment_active_installments()
    {
        $provider = new PaymentMethodProvider(Shopware()->Models());
        $activeFlag = $provider->getPaymentMethodActiveFlag(Shopware()->Container()->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $this->assertTrue($activeFlag);
    }
}
