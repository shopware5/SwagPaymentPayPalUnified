<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use DateTime;
use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use SwagPaymentPayPalUnified\Components\Exception\TimeoutInfoException;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\CaptureResource;

class TimeoutRefundService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextService
     */
    private $contextService;

    /**
     * @var CaptureResource
     */
    private $captureResource;

    public function __construct(
        Connection $connection,
        ContextService $contextService,
        CaptureResource $captureResource
    ) {
        $this->connection = $connection;
        $this->contextService = $contextService;
        $this->captureResource = $captureResource;
    }

    /**
     * @param string $payPalOrderId
     * @param float  $orderAmount
     *
     * @return void
     */
    public function saveInfo($payPalOrderId, $orderAmount)
    {
        $this->connection->insert('swag_payment_paypal_unified_order_refund_info', [
            'paypal_order_id' => (string) $payPalOrderId,
            'order_amount' => (string) $orderAmount,
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'created_at' => (string) (new DateTime())->getTimestamp(),
        ]);
    }

    /**
     * @param string $payPalOrderId
     *
     * @return void
     */
    public function deleteInfo($payPalOrderId)
    {
        $this->connection->delete('swag_payment_paypal_unified_order_refund_info', [
            'paypal_order_id' => $payPalOrderId,
        ]);
    }

    /**
     * @param string $payPalOrderId
     * @param string $captureId
     *
     * @return void
     */
    public function refund($payPalOrderId, $captureId)
    {
        $info = $this->getInfo($payPalOrderId);
        if (!\is_array($info)) {
            throw new TimeoutInfoException($payPalOrderId);
        }

        $amount = new Amount();
        $amount->setCurrencyCode($info['currency']);
        $amount->setValue($info['order_amount']);

        $refund = new Refund();
        $refund->setAmount($amount);
        $refund->setNoteToPayer('User timeout');

        $this->captureResource->refund($captureId, $refund);
    }

    /**
     * @param string $payPalOrderId
     *
     * @return array<string, string>|null
     */
    private function getInfo($payPalOrderId)
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['*'])
            ->from('swag_payment_paypal_unified_order_refund_info')
            ->where('paypal_order_id = :paypalOrderId')
            ->setParameter('paypalOrderId', $payPalOrderId)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if (!\is_array($result)) {
            return null;
        }

        return $result;
    }
}
