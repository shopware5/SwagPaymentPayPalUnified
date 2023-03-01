<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestTestCase;
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\RequestIdService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use UnexpectedValueException;

class RequestIdServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    const ANY_REQUEST_ID = 'anyRequestId';

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $this->getSession()->offsetUnset(RequestIdService::REQUEST_ID_KEY);
    }

    /**
     * @return void
     */
    public function testGenerateNewRequestId()
    {
        $result = $this->createRequestIdService()->generateNewRequestId();

        static::assertTrue(\is_string($result));

        $pattern = '/^[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/';

        if (!\method_exists($this, 'assertMatchesRegularExpression')) {
            static::assertTrue((bool) \preg_match($pattern, $result));

            return;
        }

        static::assertMatchesRegularExpression($pattern, $result);
    }

    /**
     * @return void
     */
    public function testSaveRequestIdToSession()
    {
        $requestIdService = $this->createRequestIdService();

        $requestId = $requestIdService->generateNewRequestId();

        $requestIdService->saveRequestIdToSession($requestId);

        static::assertSame($requestId, $requestIdService->getRequestIdFromSession());
    }

    /**
     * @return void
     */
    public function testSaveRequestIdToSessionShouldThrowExceptionBecauseRequestIdIsNotAString()
    {
        $requestIdService = $this->createRequestIdService();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provided requestId expect to be of type string got NULL');

        $requestIdService->saveRequestIdToSession(null);
    }

    /**
     * @return void
     */
    public function testSaveRequestIdToSessionShouldThrowExceptionBecauseRequestIdIsEmptyAString()
    {
        $requestIdService = $this->createRequestIdService();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The provided requestId is empty');

        $requestIdService->saveRequestIdToSession('    ');
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromSession()
    {
        $session = $this->getSession();
        $session->offsetSet(RequestIdService::REQUEST_ID_KEY, self::ANY_REQUEST_ID);

        $result = $this->createRequestIdService()->getRequestIdFromSession();

        static::assertSame(self::ANY_REQUEST_ID, $result);
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromSessionShouldThrowExceptionBecauseRequestIdIsNotAString()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provided requestId expect to be of type string got NULL');

        $this->createRequestIdService()->getRequestIdFromSession();
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromSessionShouldThrowExceptionBecauseRequestIdIsEmptyString()
    {
        $this->getSession()->offsetSet(RequestIdService::REQUEST_ID_KEY, ' ');

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The provided requestId is empty');

        $this->createRequestIdService()->getRequestIdFromSession();
    }

    /**
     * @return void
     */
    public function testRemoveRequestIdFromSession()
    {
        $this->getSession()->offsetSet(RequestIdService::REQUEST_ID_KEY, self::ANY_REQUEST_ID);

        $requestIdService = $this->createRequestIdService();

        static::assertSame(self::ANY_REQUEST_ID, $requestIdService->getRequestIdFromSession());

        $requestIdService->removeRequestIdFromSession();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provided requestId expect to be of type string got NULL');

        $requestIdService->getRequestIdFromSession();
    }

    /**
     * @return void
     */
    public function testCheckRequestIdIsAlreadySetToSessionExpectFalse()
    {
        static::assertFalse(
            $this->createRequestIdService()->checkRequestIdIsAlreadySetToSession('anyRequestId')
        );
    }

    /**
     * @return void
     */
    public function testCheckRequestIdIsAlreadySetToSessionExpectAlsoFalse()
    {
        $requestId = 'anyOtherRequestId';

        $this->getSession()->offsetSet(RequestIdService::REQUEST_ID_KEY, $requestId);

        static::assertFalse(
            $this->createRequestIdService()->checkRequestIdIsAlreadySetToSession(self::ANY_REQUEST_ID)
        );
    }

    /**
     * @return void
     */
    public function testCheckRequestIdIsAlreadySetToSessionExpectTrue()
    {
        $this->getSession()->offsetSet(RequestIdService::REQUEST_ID_KEY, self::ANY_REQUEST_ID);

        static::assertTrue(
            $this->createRequestIdService()->checkRequestIdIsAlreadySetToSession(self::ANY_REQUEST_ID)
        );
    }

    /**
     * @return void
     */
    public function testCheckRequestIdIsAlreadySetToSessionExpectException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Provided requestId expect to be of type string got NULL');

        $this->createRequestIdService()->checkRequestIdIsAlreadySetToSession(null);
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromRequest()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam(RequestIdService::REQUEST_ID_KEY, self::ANY_REQUEST_ID);

        $result = $this->createRequestIdService()->getRequestIdFromRequest($request);

        static::assertSame(self::ANY_REQUEST_ID, $result);
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromRequestShouldBeNull()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();

        $result = $this->createRequestIdService()->getRequestIdFromRequest($request);

        static::assertEmpty($result);
    }

    /**
     * @return void
     */
    public function testGetRequestIdFromRequestShouldAlsoBeNull()
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam(RequestIdService::REQUEST_ID_KEY, ' ');

        $result = $this->createRequestIdService()->getRequestIdFromRequest($request);

        static::assertEmpty($result);
    }

    /**
     * @dataProvider isRequestIdRequiredTestDataProvider
     *
     * @param string $paymentType
     * @param bool   $expectedResult
     *
     * @return void
     */
    public function testIsRequestIdRequired($paymentType, $expectedResult)
    {
        static::assertSame($expectedResult, $this->createRequestIdService()->isRequestIdRequired($paymentType));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function isRequestIdRequiredTestDataProvider()
    {
        yield 'PaymentType is PAYPAL_PAY_UPON_INVOICE_V2' => [
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
            true,
        ];

        yield 'PaymentType is APM_BANCONTACT' => [
            PaymentType::APM_BANCONTACT,
            true,
        ];

        yield 'PaymentType is APM_BLIK' => [
            PaymentType::APM_BLIK,
            true,
        ];

        yield 'PaymentType is APM_EPS' => [
            PaymentType::APM_EPS,
            true,
        ];

        yield 'PaymentType is APM_GIROPAY' => [
            PaymentType::APM_GIROPAY,
            true,
        ];

        yield 'PaymentType is APM_IDEAL' => [
            PaymentType::APM_IDEAL,
            true,
        ];

        yield 'PaymentType is APM_MULTIBANCO' => [
            PaymentType::APM_MULTIBANCO,
            true,
        ];

        yield 'PaymentType is APM_MYBANK' => [
            PaymentType::APM_MYBANK,
            true,
        ];

        yield 'PaymentType is APM_P24' => [
            PaymentType::APM_P24,
            true,
        ];

        yield 'PaymentType is APM_SOFORT' => [
            PaymentType::APM_SOFORT,
            true,
        ];
        yield 'PaymentType is APM_TRUSTLY' => [
            PaymentType::APM_TRUSTLY,
            true,
        ];

        yield 'PaymentType is PAYPAL_CLASSIC_V2' => [
            PaymentType::PAYPAL_CLASSIC_V2,
            false,
        ];

        yield 'PaymentType is PAYPAL_PAY_LATER' => [
            PaymentType::PAYPAL_PAY_LATER,
            false,
        ];

        yield 'PaymentType is PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
            false,
        ];

        yield 'PaymentType is PAYPAL_EXPRESS_V2' => [
            PaymentType::PAYPAL_EXPRESS_V2,
            false,
        ];

        yield 'PaymentType is PAYPAL_SMART_PAYMENT_BUTTONS_V2' => [
            PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS_V2,
            false,
        ];

        yield 'PaymentType is PAYPAL_SEPA' => [
            PaymentType::PAYPAL_SEPA,
            false,
        ];
    }

    /**
     * @return RequestIdService
     */
    private function createRequestIdService()
    {
        return new RequestIdService(
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->createMock(LoggerServiceInterface::class)
        );
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    private function getSession()
    {
        $session = $this->getContainer()->get('session');

        static::assertInstanceOf(Enlight_Components_Session_Namespace::class, $session);

        return $session;
    }
}
