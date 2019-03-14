<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

class DummyController extends \Enlight_Controller_Action
{
    /**
     * @var \Enlight_Controller_Request_RequestTestCase
     */
    protected $request;

    /**
     * @var \Enlight_View_Default
     */
    protected $view;

    /**
     * @var \Enlight_Controller_Response_ResponseTestCase
     */
    protected $response;

    /**
     * @param \Enlight_Controller_Response_ResponseTestCase $response
     */
    public function __construct(
        \Enlight_Controller_Request_RequestTestCase $request,
        \Enlight_View_Default $view,
        \Enlight_Controller_Response_ResponseTestCase $response = null
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
}
