<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PatchInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Backend\PaymentDetailsServiceTest;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\InstallmentsTest;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\CreatePaymentSale;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\GetPaymentAuthorization;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\GetPaymentInstallments;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\GetPaymentOrder;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\GetPaymentSale;

class PaymentResourceMock extends PaymentResource
{
    const THROW_EXCEPTION = 'throwException';

    /**
     * @var PatchInterface[]
     */
    private $patches = [];

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function patch($paymentId, array $patches)
    {
        $this->patches = $patches;
        if ($paymentId === self::THROW_EXCEPTION) {
            throw new RequestException('patch exception');
        }
    }

    /**
     * @return PatchInterface[]
     */
    public function getPatches()
    {
        return $this->patches;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RequestException
     */
    public function get($paymentId)
    {
        if ($paymentId === self::THROW_EXCEPTION) {
            throw new RequestException('get exception');
        }

        if ($paymentId === InstallmentsTest::INSTALLMENTS_PAYMENT_ID) {
            return GetPaymentInstallments::get();
        }

        if ($paymentId === PaymentDetailsServiceTest::ORDER_ID) {
            return GetPaymentOrder::get();
        }

        if ($paymentId === PaymentDetailsServiceTest::AUTHORIZATION_ID) {
            return GetPaymentAuthorization::get();
        }

        if ($paymentId === PaymentDetailsServiceTest::SALE_ID) {
            return GetPaymentSale::get();
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function create(Payment $payment)
    {
        if ($payment->getTransactions()->getAmount()->getCurrency() === 'throwException') {
            throw new RequestException('exception');
        }

        return CreatePaymentSale::get();
    }
}
