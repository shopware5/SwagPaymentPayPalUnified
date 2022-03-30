<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService as OriginalClientService;

class ClientService extends OriginalClientService
{
    /**
     * @var array
     */
    private $expectedResult;

    public function __construct()
    {
    }

    public function sendRequest($type, $resourceUri, $data = [], $jsonPayload = true)
    {
        return $this->expectedResult;
    }

    public function setExpectedResult(array $data)
    {
        $this->expectedResult = $data;
    }
}
