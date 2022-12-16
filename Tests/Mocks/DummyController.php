<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Enlight_Controller_Action;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_View_Default;

class DummyController extends Enlight_Controller_Action
{
    /**
     * @var Enlight_Controller_Request_RequestTestCase
     */
    protected $request;

    /**
     * @var Enlight_View_Default
     */
    protected $view;

    /**
     * @var Enlight_Controller_Response_ResponseTestCase
     */
    protected $response;

    public function __construct(
        Enlight_Controller_Request_RequestTestCase $request,
        Enlight_View_Default $view,
        Enlight_Controller_Response_ResponseTestCase $response
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return Shopware()->Container()->get($key);
    }

    /**
     * @return Enlight_View_Default
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function getRequest()
    {
        return $this->request;
    }
}
