<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Shopware\Components\HttpClient\RequestException;

trait RequestExceptionTrait
{
    /**
     * @return RequestException
     */
    public function createPayerActionRequiredRequestException()
    {
        $message = [
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [
                [
                    'issue' => 'PAYER_ACTION_REQUIRED',
                    'description' => 'Payer needs to perform the fol (truncated…)',
                    'payload' => [
                        'name' => 'UNPROCESSABLE_ENTITY',
                        'details' => [
                            'issue' => 'PAYER_ACTION_REQUIRED',
                            'description' => 'Payer needs to perform the following action before proceeding with payment.',
                        ],
                        'message' => 'The requested action could not be performed, semantically incorrect, or failed business validation.',
                        'debug_id' => 'anyDebugId',
                        'links' => [
                            [
                                'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-PAYER_ACTION_REQUIRED',
                                'rel' => 'information_link',
                                'method' => 'GET',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $message = json_encode($message, 1);
        static::assertTrue(\is_string($message));

        return new RequestException(
            'Client error: POST https://api.paypal.com/v2/checkout/orders/7YA003xxxxx05G/capture resulted in a 422 Unprocessable Entity response:',
            99,
            null,
            $message
        );
    }

    /**
     * @return RequestException
     */
    public function createInstrumentDeclinedException()
    {
        $message = [
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [
                [
                    'issue' => 'INSTRUMENT_DECLINED',
                    'description' => "The instrument presented was either declined by the processor or bank, or it can't be used for this payment.",
                ],
            ],
            'message' => 'The requested action could not be performed, semantically incorrect, or failed business validation.',
            'debug_id' => 'anyDebugId',
            'links' => [
                [
                    'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-INSTRUMENT_DECLINED',
                    'rel' => 'information_link',
                    'method' => 'GET',
                ],
            ],
        ];

        $message = json_encode($message, 1);
        static::assertTrue(\is_string($message));

        return new RequestException(
            '"message" => "Client error: `POST https://api.paypal.com/v2/checkout/orders/7YA003xxxxx05G/capture` resulted in a `422 Unprocessable Entity` response:
                    {"name":"UNPROCESSABLE_ENTITY","details":[{"issue":"INSTRUMENT_DECLINED","description":"The instrument presented was ei (truncated...)
                    "',
            99,
            null,
            $message
        );
    }
}
