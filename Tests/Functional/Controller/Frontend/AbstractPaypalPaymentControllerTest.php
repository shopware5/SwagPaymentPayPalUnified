<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Controller_Request_RequestTestCase;
use Generator;
use ReflectionClass;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Bancontact;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Blik;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Eps;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Giropay;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Ideal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Multibanco;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Mybank;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Oxxo;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\P24;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Sofort;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Trustly;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\UnifiedControllerTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use UnexpectedValueException;

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
     * @dataProvider getPaymentTypeTestDataProvider
     *
     * @param PaymentType::* $expectedResult
     * @param bool           $expectException
     *
     * @return void
     */
    public function testGetPaymentType(Order $order, Enlight_Controller_Request_RequestHttp $request, $expectedResult, $expectException = false)
    {
        $controller = $this->getController(TestPaypalPaymentController::class);
        $controller->setRequest($request);

        $reflectionMethod = (new ReflectionClass(TestPaypalPaymentController::class))->getMethod('getPaymentType');
        $reflectionMethod->setAccessible(true);

        if ($expectException) {
            $this->expectException(UnexpectedValueException::class);
            $this->expectExceptionMessage('Payment type not found');
        }

        $result = $reflectionMethod->invoke($controller, $order);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getPaymentTypeTestDataProvider()
    {
        yield 'Expect PAYPAL_SEPA' => [
            new Order(),
            $this->createRequest(['sepaCheckout' => true]),
            PaymentType::PAYPAL_SEPA,
        ];

        yield 'Expect PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            new Order(),
            $this->createRequest(['acdcCheckout' => true]),
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
        ];

        yield 'Expect PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            new Order(),
            $this->createRequest(['spbCheckout' => true]),
            PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
        ];

        yield 'Expect PAYPAL_PAY_LATER' => [
            new Order(),
            $this->createRequest(['paypalUnifiedPayLater' => true]),
            PaymentType::PAYPAL_PAY_LATER,
        ];

        yield 'Expect PAYPAL_CLASSIC_V2' => [
            new Order(),
            $this->createRequest(['inContextCheckout' => true]),
            PaymentType::PAYPAL_CLASSIC_V2,
        ];

        yield 'Expect also PAYPAL_CLASSIC_V2 because the PaymentSource is not set to the order' => [
            new Order(),
            $this->createRequest([]),
            PaymentType::PAYPAL_CLASSIC_V2,
        ];

        yield 'Expect PAYPAL_PAY_UPON_INVOICE_V2' => [
            $this->createOrderWithPaymentSource(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2),
            $this->createRequest(),
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
        ];

        yield 'Expect APM_BANCONTACT' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_BANCONTACT),
            $this->createRequest(),
            PaymentType::APM_BANCONTACT,
        ];

        yield 'Expect APM_BLIK' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_BLIK),
            $this->createRequest(),
            PaymentType::APM_BLIK,
        ];

        yield 'Expect APM_GIROPAY' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_GIROPAY),
            $this->createRequest(),
            PaymentType::APM_GIROPAY,
        ];

        yield 'Expect APM_IDEAL' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_IDEAL),
            $this->createRequest(),
            PaymentType::APM_IDEAL,
        ];

        yield 'Expect APM_MULTIBANCO' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_MULTIBANCO),
            $this->createRequest(),
            PaymentType::APM_MULTIBANCO,
        ];

        yield 'Expect APM_MYBANK' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_MYBANK),
            $this->createRequest(),
            PaymentType::APM_MYBANK,
        ];

        yield 'Expect APM_OXXO' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_OXXO),
            $this->createRequest(),
            PaymentType::APM_OXXO,
        ];

        yield 'Expect APM_P24' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_P24),
            $this->createRequest(),
            PaymentType::APM_P24,
        ];

        yield 'Expect APM_SOFORT' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_SOFORT),
            $this->createRequest(),
            PaymentType::APM_SOFORT,
        ];

        yield 'Expect APM_TRUSTLY' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_TRUSTLY),
            $this->createRequest(),
            PaymentType::APM_TRUSTLY,
        ];

        yield 'Expect APM_EPS' => [
            $this->createOrderWithPaymentSource(PaymentType::APM_EPS),
            $this->createRequest(),
            PaymentType::APM_EPS,
        ];

        yield 'Expect Exception' => [
            $this->createOrderWithPaymentSource('AnyUnknownPaymentSource'),
            $this->createRequest([]),
            PaymentType::PAYPAL_CLASSIC_V2,
            true,
        ];
    }

    /**
     * @param string $paymentType
     *
     * @return Order
     */
    private function createOrderWithPaymentSource($paymentType)
    {
        $order = new Order();

        $paymentSource = new PaymentSource();

        switch ($paymentType) {
            case PaymentType::PAYPAL_PAY_UPON_INVOICE_V2:
                $paymentSource->setPayUponInvoice(new PayUponInvoice());
                // no break
            case PaymentType::APM_BANCONTACT:
                $paymentSource->setBancontact(new Bancontact());
                // no break
            case PaymentType::APM_BLIK:
                $paymentSource->setBlik(new Blik());
                // no break
            case PaymentType::APM_GIROPAY:
                $paymentSource->setGiropay(new Giropay());
                // no break
            case PaymentType::APM_IDEAL:
                $paymentSource->setIdeal(new Ideal());
                // no break
            case PaymentType::APM_MULTIBANCO:
                $paymentSource->setMultibanco(new Multibanco());
                // no break
            case PaymentType::APM_MYBANK:
                $paymentSource->setMybank(new Mybank());
                // no break
            case PaymentType::APM_OXXO:
                $paymentSource->setOxxo(new Oxxo());
                // no break
            case PaymentType::APM_P24:
                $paymentSource->setP24(new P24());
                // no break
            case PaymentType::APM_SOFORT:
                $paymentSource->setSofort(new Sofort());
                // no break
            case PaymentType::APM_TRUSTLY:
                $paymentSource->setTrustly(new Trustly());
                // no break
            case PaymentType::APM_EPS:
                $paymentSource->setEps(new Eps());
        }

        $order->setPaymentSource($paymentSource);

        return $order;
    }

    /**
     * @param array<string,mixed> $params
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function createRequest(array $params = [])
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        if (!empty($params)) {
            $request->setParams($params);
        }

        return $request;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     *
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
