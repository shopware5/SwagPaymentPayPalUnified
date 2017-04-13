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
     * @param \Enlight_Controller_Request_RequestTestCase   $request
     * @param \Enlight_View_Default                         $view
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
