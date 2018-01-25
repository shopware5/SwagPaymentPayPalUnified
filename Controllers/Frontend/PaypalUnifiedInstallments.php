<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Shopware_Controllers_Frontend_PaypalUnifiedInstallments extends \Enlight_Controller_Action
{
    /**
     * To avoid duplicate code, simply trigger the unified controller here.
     */
    public function indexAction()
    {
        $this->redirect([
            'module' => 'frontend',
            'controller' => 'PaypalUnified',
            'action' => 'gateway',
            'forceSecure' => true,
            'installments' => true,
        ]);
    }

    /**
     * Will be triggered when the user returns from the PayPal payment page.
     * To avoid duplicate code, simply trigger the checkout controller here.
     */
    public function returnAction()
    {
        $request = $this->Request();
        $paymentId = $request->getParam('paymentId');
        $payerId = $request->getParam('PayerID');
        $basketId = $request->getParam('basketId');

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'confirm',
            'module' => 'frontend',
            'paymentId' => $paymentId,
            'PayerID' => $payerId,
            'basketId' => $basketId,
            'installments' => true,
            'forceSecure' => 1,
        ]);
    }
}
