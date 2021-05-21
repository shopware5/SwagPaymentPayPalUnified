<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets\_mocks;

require_once __DIR__ . '/../../../../../Controllers/Widgets/PaypalUnifiedExpressCheckout.php';

class PaypalUnifiedExpressCheckoutControllerMock extends \Shopware_Controllers_Widgets_PaypalUnifiedExpressCheckout
{
    public function __construct()
    {
        // do nothing
    }

    public function preDispatch()
    {
        // do nothing
    }
}
