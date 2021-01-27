<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementHelperInterface;

class RiskManagement implements SubscriberInterface
{
    /**
     * @var RiskManagementHelperInterface
     */
    private $riskManagementHelper;

    /**
     * @var \Enlight_Template_Manager
     */
    private $template;

    public function __construct(RiskManagementHelperInterface $riskManagementHelper, \Enlight_Template_Manager $template)
    {
        $this->riskManagementHelper = $riskManagementHelper;
        $this->template = $template;
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
    public function onCheckProductCategoryFrom(\Enlight_Event_EventArgs $args)
    {
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
    public function onCheckRiskAttribIsNot(\Enlight_Event_EventArgs $args)
    {
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
    public function onCheckRiskAttribIs(\Enlight_Event_EventArgs $args)
    {
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
}
