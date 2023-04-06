<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\OrderNumberService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;

/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Widgets_PaypalUnifiedOrderNumber extends Enlight_Controller_Action
{
    /**
     * @var OrderNumberService
     */
    private $orderNumberService;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->orderNumberService = $this->container->get('paypal_unified.order_number_service');
        $this->logger = $this->container->get('paypal_unified.logger_service');

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer();
        $this->view->setTemplate();
    }

    /**
     * @return void
     */
    public function restoreOrderNumberAction()
    {
        $this->logger->debug(sprintf('%s START restoring order number', __METHOD__));

        try {
            $this->orderNumberService->restoreOrderNumberToPool();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('%s Cannot restore order number to pool.', __METHOD__), [
                'exceptionMessage' => $exception->getMessage(),
                'exceptionTrace' => $exception->getTrace(),
            ]);

            $this->View()->assign('success', false);

            return;
        }

        $this->logger->debug(sprintf('%s Restoring order number successful', __METHOD__));

        $this->View()->assign('success', true);
    }
}
