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

use SwagPaymentPayPalUnified\SDK\Resources\PaymentResource;

class Shopware_Controllers_Frontend_PaypalUnified extends \Shopware_Controllers_Frontend_Payment
{
    /**
     * Index action of the payment. The only thing to do here is to forward to the gateway action.
     */
    public function indexAction()
    {
        $this->forward('gateway');
    }

    /**
     * The gateway to PayPal. The payment will be created and the user will be redirected to the
     * PayPal site.
     */
    public function gatewayAction()
    {
        $orderData = $this->get('session')->get('sOrderVariables');

        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        $response = $paymentResource->create($orderData);

        $urls = $response['links'];

        $this->redirect($urls[1]['href']);
    }
}
