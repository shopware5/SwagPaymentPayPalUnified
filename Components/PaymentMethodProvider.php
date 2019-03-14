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
     * The technical name of the installments payment method.
     */
    const PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME = 'SwagPaymentPayPalUnifiedInstallments';

    /**
     * @var ModelManager
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
     * @see PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME
     *
     * @param string $name
     *
     * @return Payment|null
     */
    public function getPaymentMethodModel($name = self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME)
    {
        if ($name === self::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) {
            return $this->modelManager->getRepository(Payment::class)->findOneBy([
                'name' => self::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME,
            ]);
        }

        return $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     * @see PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME
     *
     * @param string $name
     * @param bool   $active
     */
    public function setPaymentMethodActiveFlag($active, $name = self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME)
    {
        $paymentMethod = $this->getPaymentMethodModel($name);
        if ($paymentMethod) {
            $paymentMethod->setActive($active);

            $this->modelManager->persist($paymentMethod);
            $this->modelManager->flush($paymentMethod);
        }
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     * @see PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME
     *
     * @param string $name
     *
     * @return bool
     */
    public function getPaymentMethodActiveFlag(Connection $connection, $name = self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME)
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool) $connection->fetchColumn($sql, [
            ':paymentName' => $name === self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
                ? self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
                : self::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME,
        ]);
    }

    /**
     * @see PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
     * @see PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME
     *
     * @param string $name
     *
     * @return int
     */
    public function getPaymentId(Connection $connection, $name = self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME)
    {
        $sql = 'SELECT `id` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (int) $connection->fetchColumn($sql, [
            ':paymentName' => $name === self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
                ? self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME
                : self::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME,
        ]);
    }
}
