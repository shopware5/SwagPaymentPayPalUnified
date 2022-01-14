<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\RiskManagement;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class RiskManagementServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;

    public function testIsPayPalNotAllowed()
    {
        $this->getContainer()->get('session')->offsetSet('sUserId', null);

        $this->getContainer()->get('front')->setRequest(new \Enlight_Controller_Request_RequestHttp());
        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertFalse($this->getRiskManagement()->isPayPalNotAllowed());
    }

    public function testIsPayPalNotAllowedTestAttrIsNot()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is_not.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'listing');

        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertFalse($this->getRiskManagement()->isPayPalNotAllowed(178));
        static::assertTrue($this->getRiskManagement()->isPayPalNotAllowed(37));
    }

    public function testIsPayPalNotAllowedTestAttrIsNotCatagory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is_not.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'detail');

        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertFalse($this->getRiskManagement()->isPayPalNotAllowed(null, 6));

        $expectedResult = require __DIR__ . '/_fixtures/testAttrIsNot_category_result.php';
        $result = \json_decode($this->getContainer()->get('template')->getTemplateVars('riskManagementMatchedProducts'), true);

        foreach ($expectedResult as $index => $resultItem) {
            static::assertSame($resultItem, $result[$index]);
        }
    }

    public function testIsPayPalNotAllowedTestAttrIs()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'detail');

        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertTrue($this->getRiskManagement()->isPayPalNotAllowed(178));
        static::assertFalse($this->getRiskManagement()->isPayPalNotAllowed(37));
    }

    public function testIsPayPalNotAllowedTestAttrIsCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'detail');

        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertFalse($this->getRiskManagement()->isPayPalNotAllowed(null, 6));

        $expectedResult = ['SW10178'];
        $result = \json_decode($this->getContainer()->get('template')->getTemplateVars('riskManagementMatchedProducts'), true);

        foreach ($result as $index => $resultItem) {
            static::assertSame($resultItem, $expectedResult[$index]);
        }
    }

    public function testIsPayPalNotAllowedIsProductInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'detail');

        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertTrue($this->getRiskManagement()->isPayPalNotAllowed('178'));
    }

    public function testIsPayPalNotAllowedIsProductInCategoryByCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $request = new \Enlight_Controller_Request_RequestHttp();
        $this->setRequestParameterToFront($request, 'frontend', 'detail');

        $this->getContainer()->get('front')->setRequest($request);
        $this->getContainer()->get('front')->setResponse(new \Enlight_Controller_Response_ResponseHttp());

        static::assertTrue($this->getRiskManagement()->isPayPalNotAllowed(null, '6'));
    }

    private function getRiskManagement()
    {
        return new RiskManagement(
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    private function setRequestParameterToFront(
        \Enlight_Controller_Request_RequestHttp $request,
        $module = 'frontend',
        $controller = 'listing',
        $action = 'index'
    ) {
        $request->setActionName($action);
        $request->setControllerName($controller);
        $request->setModuleName($module);
        $this->getContainer()->get('front')->setRequest($request);
    }
}
