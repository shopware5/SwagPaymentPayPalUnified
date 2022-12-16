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
    const INSTRUMENT_DECLINED = 10;
    const TRANSACTION_REFUSED = 11;

    // In order to provoke this error, the buyer must have the email address: payment_source_info_cannot_be_verified@example.com
    const PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED = 12;

    // In order to provoke this error, the buyer must have the email address: payment_source_declined_by_processor@example.com
    const PAYMENT_SOURCE_DECLINED_BY_PROCESSOR = 13;

    const AUTHORIZATION_DENIED = 14;
    const CAPTURE_FAILED = 15;
    const CAPTURE_DECLINED = 16;

    const THREE_D_SECURE_CHECK_FAILED = 17;

    const APM_PAYMENT_FAILED_CONTACT_MERCHANT = 18;

    private function __construct()
    {
    }
}
