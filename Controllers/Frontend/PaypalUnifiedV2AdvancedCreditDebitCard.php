<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;

class Shopware_Controllers_Frontend_PaypalUnifiedV2AdvancedCreditDebitCard extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $session = $this->container->get('session');

        $paypalOrderId = $session->offsetGet('paypalOrderId');

        if ($this->isPaymentCompleted($paypalOrderId)) {
            $session->offsetUnset('paypalOrderId');

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $paypalOrderId,
            ]);
        }
    }
}
