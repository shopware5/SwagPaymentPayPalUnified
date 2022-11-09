<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementHelperInterface;

class RiskManagement implements SubscriberInterface
{
    /**
     * @var RiskManagementHelperInterface
     */
    private $riskManagementHelper;

    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    public function __construct(
        RiskManagementHelperInterface $riskManagementHelper,
        Enlight_Template_Manager $template,
        DependencyProvider $dependencyProvider,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->riskManagementHelper = $riskManagementHelper;
        $this->template = $template;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Execute_Risk_Rule_sRiskARTICLESFROM' => 'onCheckProductCategoryFrom',
            'Shopware_Modules_Admin_Execute_Risk_Rule_sRiskATTRISNOT' => 'onCheckRiskAttribIsNot',
            'Shopware_Modules_Admin_Execute_Risk_Rule_sRiskATTRIS' => 'onCheckRiskAttribIs',
        ];
    }

    /**
     * @return bool|null
     */
    public function onCheckProductCategoryFrom(Enlight_Event_EventArgs $args)
    {
        if (!$this->shouldContinueCheck($args->get('paymentID'))) {
            return null;
        }

        $context = $this->riskManagementHelper->createContext(
            $this->riskManagementHelper->createAttribute(),
            $args->get('value')
        );

        if (!$context->sessionProductIdIsNull()) {
            if ($this->riskManagementHelper->isProductInCategory($context)) {
                $args->setReturn(true);

                return true;
            }

            return null;
        }

        if (!$context->sessionCategoryIdIsNull()
            && !$context->categoryIdsAreTheSame()
            && !$this->riskManagementHelper->isCategoryAmongTheParents($context)
        ) {
            $products = $this->riskManagementHelper->getProductOrderNumbersInCategory($context);

            $this->template->assign('riskManagementMatchedProducts', \json_encode($products));

            return null;
        }

        $args->setReturn(true);

        return true;
    }

    /**
     * @return bool|null
     */
    public function onCheckRiskAttribIsNot(Enlight_Event_EventArgs $args)
    {
        if (!$this->shouldContinueCheck($args->get('paymentID'))) {
            return null;
        }

        $context = $this->riskManagementHelper->createContext(
            $this->riskManagementHelper->createAttribute($args->get('value'))
        );

        if (!$context->sessionProductIdIsNull()) {
            $hasAttribute = $this->riskManagementHelper->hasProductAttributeValue($context);

            if ($hasAttribute) {
                return null;
            }
        }

        if (!$context->sessionCategoryIdIsNull()) {
            $matchedProductOrdernumbers = $this->riskManagementHelper->getProductOrdernumbersNotMatchedAttribute($context);

            if (!empty($matchedProductOrdernumbers)) {
                $this->template->assign('riskManagementMatchedProducts', \json_encode($matchedProductOrdernumbers));

                return null;
            }
        }

        $args->setReturn(true);

        return true;
    }

    /**
     * @return bool|null
     */
    public function onCheckRiskAttribIs(Enlight_Event_EventArgs $args)
    {
        if (!$this->shouldContinueCheck($args->get('paymentID'))) {
            return null;
        }

        $context = $this->riskManagementHelper->createContext(
            $this->riskManagementHelper->createAttribute($args->get('value'))
        );

        if (!$context->sessionProductIdIsNull()) {
            $hasAttribute = $this->riskManagementHelper->hasProductAttributeValue($context);

            if ($hasAttribute) {
                $args->setReturn(true);

                return true;
            }
        }

        if (!$context->sessionCategoryIdIsNull()) {
            $matchedProductOrdernumbers = $this->riskManagementHelper->getProductOrdernumbersMatchedAttribute($context);

            if (!empty($matchedProductOrdernumbers)) {
                $this->template->assign('riskManagementMatchedProducts', \json_encode($matchedProductOrdernumbers));
            }
        }

        return null;
    }

    /**
     * @param int $paymentId
     *
     * @return bool
     */
    private function shouldContinueCheck($paymentId)
    {
        if (!\in_array($this->getControllerAction(), $this->getAcceptedControllerActions())) {
            return false;
        }

        if ((int) $paymentId !== $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    private function getControllerAction()
    {
        $frontendController = $this->dependencyProvider->getFront();
        if ($frontendController === null) {
            return '';
        }

        $request = $frontendController->Request();

        if ($request === null) {
            return '';
        }

        return \sprintf(
            '%s::%s::%s',
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        );
    }

    /**
     * @return array
     */
    private function getAcceptedControllerActions()
    {
        return [
            'frontend::detail::index',
            'frontend::listing::index',
            'widget::listing::listingCount',
        ];
    }
}
