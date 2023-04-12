<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Exception;
use PDO;
use Shopware_Controllers_Widgets_PaypalUnifiedOrderNumber;
use SwagPaymentPayPalUnified\Components\NumberRangeIncrementerDecorator;
use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

require_once __DIR__ . '/../../../../Controllers/Widgets/PayPalUnifiedOrderNumber.php';

class PayPalUnifiedOrderNumberTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use DatabaseHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getContainer()->get('session');
        $session->offsetUnset(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY);
    }

    /**
     * @return void
     */
    public function testRestoreOrderNumberAction()
    {
        $orderNumber = 'SW123456';
        $this->getContainer()->get('session')->offsetSet(NumberRangeIncrementerDecorator::ORDERNUMBER_SESSION_KEY, $orderNumber);

        $controller = $this->createController($this->getContainer()->get('paypal_unified.order_number_service'));

        $controller->restoreOrderNumberAction();

        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select('order_number')
            ->from(NumberRangeIncrementerDecorator::POOL_DATABASE_TABLE_NAME)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        static::assertTrue(\is_array($result));
        static::assertTrue(\in_array($orderNumber, $result));
        static::assertTrue($controller->View()->getAssign('success'));
    }

    /**
     * @return void
     */
    public function testRestoreOrderNumberActionOrderNumberServiceThrowsException()
    {
        $orderNumberService = $this->createMock(OrderNumberService::class);
        $orderNumberService->method('restoreOrderNumberToPool')->willThrowException(new Exception('An error occurred'));

        $controller = $this->createController($orderNumberService);

        $controller->restoreOrderNumberAction();

        static::assertFalse($controller->View()->getAssign('success'));
    }

    /**
     * @return Shopware_Controllers_Widgets_PaypalUnifiedOrderNumber
     */
    private function createController(OrderNumberService $orderNumberService)
    {
        return $this->getController(
            Shopware_Controllers_Widgets_PaypalUnifiedOrderNumber::class,
            [
               self::SERVICE_ORDER_NUMBER_SERVICE => $orderNumberService,
            ],
            new Enlight_Controller_Request_RequestTestCase(),
            new Enlight_Controller_Response_ResponseTestCase(),
            new Enlight_View_Default(new Enlight_Template_Manager())
        );
    }
}
