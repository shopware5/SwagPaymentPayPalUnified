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
     * The technical name of the unified payment method.
     */
    const PAYPAL_UNIFIED_PAYMENT_METHOD_NAME = 'SwagPaymentPayPalUnified';

    /**
     * @var ModelManager|null
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager = null)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     *
     * @return Payment|null
     */
    public function getPaymentMethodModel()
    {
        if ($this->modelManager === null) {
            throw new \RuntimeException('ModelManager not defined in PaymentMethodProvider');
        }

        /** @var Payment|null $payment */
        $payment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
        ]);

        return $payment;
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     *
     * @param bool $active
     */
    public function setPaymentMethodActiveFlag($active)
    {
        if ($this->modelManager === null) {
            throw new \RuntimeException('ModelManager not defined in PaymentMethodProvider');
        }

        $paymentMethod = $this->getPaymentMethodModel();
        if ($paymentMethod) {
            $paymentMethod->setActive($active);

            $this->modelManager->persist($paymentMethod);
            $this->modelManager->flush();
        }
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     *
     * @return bool
     */
    public function getPaymentMethodActiveFlag(Connection $connection)
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool) $connection->fetchColumn($sql, [
            ':paymentName' => self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     *
     * @return int
     */
    public function getPaymentId(Connection $connection)
    {
        $sql = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName AND active = 1';

        return (int) $connection->fetchColumn($sql, [
            ':paymentName' => self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @deprecated since 3.0.0. Will be removed with 4.0.0. Only used for managing old installment payments in the backend module
     *
     * @return int
     */
    public function getInstallmentPaymentId(Connection $connection)
    {
        $sql = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName AND active = 1';

        return (int) $connection->fetchColumn($sql, [
            ':paymentName' => 'SwagPaymentPayPalUnifiedInstallments',
        ]);
    }
}
