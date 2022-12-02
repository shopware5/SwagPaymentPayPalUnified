<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Enlight_Controller_Response_ResponseHttp;

trait AssertLocationTrait
{
    /**
     * @param string $stringToEndsWith
     *
     * @return void
     */
    public static function assertLocationEndsWith(Enlight_Controller_Response_ResponseHttp $response, $stringToEndsWith)
    {
        $counter = 0;
        foreach ($response->getHeaders() as $header) {
            if (\strtolower($header['name']) === 'location') {
                static::assertStringEndsWith(
                    $stringToEndsWith,
                    $header['value']
                );

                ++$counter;
            }
        }

        static::assertGreaterThan(0, $counter, 'AssertLocation: No location headers found');
    }
}
