<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Validation\RedirectDataBuilder;

class PaymentControllerHelper
{
    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(DependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
    }

    public function setGrossPriceFallback(array $userData)
    {
        $userData[CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES] = (bool) $this->dependencyProvider->getSession()
            ->get('sUserGroupData', ['tax' => 1])['tax'];

        return $userData;
    }

    public function handleError(\Enlight_Controller_Action $controller, RedirectDataBuilder $redirectDataBuilder)
    {
        if ($controller->Request()->isXmlHttpRequest()) {
            $this->renderJson($controller, $redirectDataBuilder);

            return;
        }

        $controller->redirect($redirectDataBuilder->getRedirectData());
    }

    private function renderJson(\Enlight_Controller_Action $controller, RedirectDataBuilder $redirectDataBuilder)
    {
        $controller->Front()->Plugins()->Json()->setRenderer();

        $view = $controller->View();
        $view->setTemplate();

        $view->assign('errorCode', $redirectDataBuilder->getCode());
        if ($redirectDataBuilder->hasException()) {
            $view->assign($redirectDataBuilder->getRedirectData());
        }
    }
}
