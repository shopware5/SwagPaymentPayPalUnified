<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use UnexpectedValueException;

class PaymentMethodProvider implements PaymentMethodProviderInterface
{
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
     * {@inheritDoc}
     */
    public function getPaymentMethodModel($paymentMethodName)
    {
        return $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => $paymentMethodName,
        ]);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getPaymentMethodActiveFlag($paymentMethodName)
    {
        $sql = 'SELECT `active` FROM s_core_paymentmeans WHERE `name`=:paymentName';

        return (bool) $this->connection->fetchColumn($sql, [
            ':paymentName' => $paymentMethodName,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentId($paymentMethodName)
    {
        return (int) $this->connection->fetchColumn(self::PAYMENT_ID_QUERY, [
            ':paymentName' => $paymentMethodName,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivePayments(array $paymentMethodNames)
    {
        return $this->createPaymentQueryBuilder($paymentMethodNames)
            ->andWhere('payment.active = 1')
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * {@inheritDoc}
     */
    public function getPayments(array $paymentMethodNames)
    {
        return $this->createPaymentQueryBuilder($paymentMethodNames)
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * {@inheritDoc}
     */
    public function getPayPalMethodNames()
    {
        return [
            self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
            self::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getAlternativePaymentMethodNames()
    {
        return [
            self::BANCONTACT_METHOD_NAME,
            self::BLIK_METHOD_NAME,
            self::EPS_METHOD_NAME,
            self::GIROPAY_METHOD_NAME,
            self::IDEAL_METHOD_NAME,
            self::MULTIBANCO_METHOD_NAME,
            self::MY_BANK_METHOD_NAME,
            self::OXXO_METHOD_NAME,
            self::P24_METHOD_NAME,
            self::SOFORT_METHOD_NAME,
            self::TRUSTLY_METHOD_NAME,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getAllUnifiedNames()
    {
        return array_merge(
            $this->getPayPalMethodNames(),
            $this->getAlternativePaymentMethodNames()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentTypeByName($paymentMethodName)
    {
        switch ($paymentMethodName) {
            case self::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME:
                return PaymentType::PAYPAL_CLASSIC_V2;
            case self::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME:
                return PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;
            case self::BANCONTACT_METHOD_NAME:
                return PaymentType::APM_BANCONTACT;
            case self::BLIK_METHOD_NAME:
                return PaymentType::APM_BLIK;
            case self::EPS_METHOD_NAME:
                return PaymentType::APM_EPS;
            case self::GIROPAY_METHOD_NAME:
                return PaymentType::APM_GIROPAY;
            case self::IDEAL_METHOD_NAME:
                return PaymentType::APM_IDEAL;
            case self::MULTIBANCO_METHOD_NAME:
                return PaymentType::APM_MULTIBANCO;
            case self::MY_BANK_METHOD_NAME:
                return PaymentType::APM_MYBANK;
            case self::OXXO_METHOD_NAME:
                return PaymentType::APM_OXXO;
            case self::P24_METHOD_NAME:
                return PaymentType::APM_P24;
            case self::SOFORT_METHOD_NAME:
                return PaymentType::APM_SOFORT;
            case self::TRUSTLY_METHOD_NAME:
                return PaymentType::APM_TRUSTLY;
        }

        throw new UnexpectedValueException(
            sprintf('Payment type for payment method "%s" not found', $paymentMethodName)
        );
    }

    /**
     * @param array<string> $paymentMethodNames
     *
     * @return QueryBuilder
     */
    private function createPaymentQueryBuilder(array $paymentMethodNames)
    {
        return $this->connection->createQueryBuilder()
            ->select(['payment.name', 'payment.id'])
            ->from('s_core_paymentmeans', 'payment')
            ->andWhere('payment.name IN (:paymentMethodNames)')
            ->setParameter('paymentMethodNames', $paymentMethodNames, Connection::PARAM_STR_ARRAY);
    }
}
