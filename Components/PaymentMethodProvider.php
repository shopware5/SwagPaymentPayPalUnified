<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;

class PaymentMethodProvider
{
    /**
     * The technical names of the unified payment methods.
     */
    const PAYPAL_UNIFIED_PAYMENT_METHOD_NAME = 'SwagPaymentPayPalUnified';

    const PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME = 'SwagPaymentPayPalUnifiedPayUponInvoice';

    /**
     * @deprecated
     */
    const PAYPAL_UNIFIED_INSTALLMENTS_METHOD_NAME = 'SwagPaymentPayPalUnifiedInstallments';

    const PAYMENT_ID_QUERY = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName AND active = 1';

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, ModelManager $modelManager)
    {
        $this->connection = $connection;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $paymentMethodName
     *
     * @return Payment|null
     *
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentMethodModel($paymentMethodName)
    {
        return $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => $paymentMethodName,
        ]);
    }

    /**
     * @param bool   $active
     * @param string $paymentMethodName
     *
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function setPaymentMethodActiveFlag($paymentMethodName, $active)
    {
        $paymentMethod = $this->getPaymentMethodModel($paymentMethodName);

        if ($paymentMethod) {
            $paymentMethod->setActive($active);

            $this->modelManager->persist($paymentMethod);
            $this->modelManager->flush();
        }
    }

    /**
     * @param string $paymentMethodName
     *
     * @return bool
     *
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentMethodActiveFlag($paymentMethodName)
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool) $this->connection->fetchColumn($sql, [
            ':paymentName' => $paymentMethodName,
        ]);
    }

    /**
     * @param string $paymentMethodName
     *
     * @return int
     *
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME or PaymentMethodProvider::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME
     */
    public function getPaymentId($paymentMethodName)
    {
        return (int) $this->connection->fetchColumn(self::PAYMENT_ID_QUERY, [
            ':paymentName' => $paymentMethodName,
        ]);
    }
}
