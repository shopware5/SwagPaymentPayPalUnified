<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class State
{
    /**
     * The transaction has completed.
     */
    const COMPLETED = 'completed';

    /**
     * The transaction was partially refunded.
     */
    const PARTIALLY_REFUNDED = 'partially_refunded';

    /**
     * The transaction is pending.
     */
    const PENDING = 'pending';

    /**
     * The transaction was fully refunded.
     */
    const REFUNDED = 'refunded';

    /**
     * The transaction was denied.
     */
    const DENIED = 'denied';
}
