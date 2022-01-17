<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

final class ErrorCodes
{
    const CANCELED_BY_USER = 1;
    const COMMUNICATION_FAILURE = 2;
    const NO_ORDER_TO_PROCESS = 3;
    const UNKNOWN = 4;
    const COMMUNICATION_FAILURE_FINISH = 5;
    const BASKET_VALIDATION_ERROR = 6;
    const ADDRESS_VALIDATION_ERROR = 7;
    const NO_DISPATCH_FOR_ORDER = 8;
    const UNKNOWN_EXPRESS_ERROR = 9;

    private function __construct()
    {
    }
}
