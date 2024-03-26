<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;

class Shopware_Controllers_Widgets_PaypalUnifiedV2PayUponInvoice extends Enlight_Controller_Action
{
    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->orderResource = $this->get('paypal_unified.v2.order_resource');
    }

    /**
     * @return void
     */
    public function pollOrderAction()
    {
        $payPalOrderId = $this->request->get('sUniqueID');

        if (!\is_string($payPalOrderId)) {
            throw new DomainException('The Paypal id must exist in the session');
        }

        $order = $this->orderResource->get($payPalOrderId);

        switch ($order->getStatus()) {
            case 'COMPLETED':
                $this->Response()->setHttpResponseCode(200);

                return;
            case 'VOIDED':
                $this->Response()->setHttpResponseCode(400);

                return;
        }

        $this->Response()->setHttpResponseCode(417);
    }
}
