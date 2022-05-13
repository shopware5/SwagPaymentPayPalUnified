<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks;

use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;

class AbstractPaypalPaymentControllerMock extends AbstractPaypalPaymentController
{
    const MAXIMUM_RETRIES = 1;
    const SLEEP = 0;
}
