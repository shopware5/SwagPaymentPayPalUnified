<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use ReflectionClass;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\UnifiedControllerTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AbstractPaypalPaymentControllerTest extends UnifiedControllerTestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use ShopRegistrationTrait;

    const TRANSACTION_ID = '9999';

    /**
     * @before
     *
     * @return void
     */
    public function setRequestStack()
    {
        $requestStack = $this->getContainer()->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($requestStack instanceof RequestStack) {
            $requestStack->push($this->Request());
        }
        $this->getContainer()->get('front')->setRequest($this->Request());
    }

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getContainer()->get('session');

        if (method_exists($session, 'clear')) {
            $session->clear();
        } else {
            $session->offsetUnset('sUserId');
            $session->offsetUnset('sOrderVariables');
        }
    }

    /**
     * @dataProvider createShopwareOrderPaymentStatusProvider
     *
     * @param Status::PAYMENT_STATE_* $paymentStatusId
     *
     * @return void
     */
    public function testCreateShopwareOrderSetsPaymentStatus($paymentStatusId)
    {
        $this->importFixture();

        $session = $this->getContainer()->get('session');
        $session->offsetSet('sUserId', 3);
        $session->offsetSet('sOrderVariables', ['sUserData' => ['additional' => ['payment' => ['id' => 1]]]]);

        $orderNumber = $this->getController(TestPaypalPaymentController::class)
            ->createShopwareOrder(
                self::TRANSACTION_ID,
                PaymentType::PAYPAL_CLASSIC_V2,
                $paymentStatusId
            );

        $actualPaymentStatusId = (int) $this->getContainer()->get('dbal_connection')
            ->executeQuery(
                'SELECT cleared FROM s_order WHERE ordernumber = :orderNumber;',
                ['orderNumber' => $orderNumber]
            )
            ->fetchColumn();

        static::assertSame(
            $paymentStatusId,
            $actualPaymentStatusId
        );
    }

    /**
     * @return array<string, array<Status::PAYMENT_STATE_*>>
     */
    public function createShopwareOrderPaymentStatusProvider()
    {
        $info = new ReflectionClass(Status::class);

        $paymentStates = array_filter(
            $info->getConstants(),
            static function ($name) {
                return strpos($name, 'PAYMENT_STATE_') === 0;
            },
            \ARRAY_FILTER_USE_KEY
        );

        return array_map(static function ($value) {
            return [$value];
        }, $paymentStates);
    }

    /**
     * @return void
     */
    private function importFixture()
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $sql = file_get_contents(__DIR__ . '/../../order_fixtures.sql');

        static::assertTrue(\is_string($sql));

        $connection->exec($sql);

        $sql = <<<SQL
UPDATE s_order
SET transactionID = :transactionId, temporaryID = :transactionId
WHERE id = 9999;
SQL;

        $connection->executeQuery($sql, ['transactionId' => self::TRANSACTION_ID]);
    }
}

class TestPaypalPaymentController extends AbstractPaypalPaymentController
{
    /**
     * @param string                       $payPalOrderId
     * @param PaymentType::*               $paymentType
     * @param Status::PAYMENT_STATE_*|null $paymentStatusId
     *
     * @return string
     */
    public function createShopwareOrder($payPalOrderId, $paymentType, $paymentStatusId = null)
    {
        return parent::createShopwareOrder($payPalOrderId, $paymentType, $paymentStatusId);
    }
}
