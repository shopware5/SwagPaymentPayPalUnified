<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class Shopware_Controllers_Frontend_PaypalUnifiedInstallments extends \Enlight_Controller_Action
{
    /**
     * To avoid duplicate code, we can simply trigger the unified controller here.
     */
    public function indexAction()
    {
        $this->redirect([
            'module' => 'frontend',
            'controller' => 'PaypalUnified',
            'action' => 'gateway',
            'forceSecure' => true,
        ]);
    }

    /**
     * Will be triggered when the user returns from the paypal payment page.
     * To avoid duplicate code, we can simply trigger the checkout controller here.
     */
    public function returnAction()
    {
        $request = $this->Request();
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $basketId = $request->get('basketId');

        $this->redirect([
            'controller' => 'checkout',
            'action' => 'confirm',
            'module' => 'frontend',
            'paymentId' => $paymentId,
            'PayerID' => $payerId,
            'basketId' => $basketId,
            'forceSecure' => 1,
        ]);
    }
}
