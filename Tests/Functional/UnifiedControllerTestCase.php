<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Enlight_Controller_Request_RequestTestCase as RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase as ResponseTestCase;

class UnifiedControllerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestTestCase
     */
    private $request;

    /**
     * @var ResponseTestCase
     */
    private $response;

    public function Request()
    {
        if (!$this->request) {
            $this->request = new RequestTestCase();
        }

        return $this->request;
    }

    /**
     * @return ResponseTestCase
     */
    public function Response()
    {
        if (!$this->response) {
            $this->response = new ResponseTestCase();
        }

        return $this->response;
    }
}
