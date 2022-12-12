<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Onboarding;

class IsCapableResult
{
    const PAYMENTS_RECEIVABLE = 'payments_receivable';

    const PRIMARY_EMAIL_CONFIRMED = 'primary_email_confirmed';

    /**
     * @var bool
     */
    private $isCapable;

    /**
     * @var bool
     */
    private $hasLimits = false;

    /**
     * @var bool|null
     */
    private $paymentsReceivable;

    /**
     * @var bool|null
     */
    private $primaryEmailConfirmed;

    /**
     * @param bool                     $isCapable
     * @param array<string,mixed>|null $limits
     * @param bool|null                $paymentsReceivable
     * @param bool|null                $primaryEmailConfirmed
     */
    public function __construct($isCapable, array $limits = null, $paymentsReceivable = null, $primaryEmailConfirmed = null)
    {
        $this->isCapable = $isCapable;

        if ($limits !== null) {
            $this->hasLimits = true;
        }

        $this->paymentsReceivable = $paymentsReceivable;
        $this->primaryEmailConfirmed = $primaryEmailConfirmed;
    }

    /**
     * @return bool
     */
    public function isCapable()
    {
        return $this->isCapable;
    }

    /**
     * @return bool
     */
    public function hasLimits()
    {
        return $this->hasLimits;
    }

    /**
     * @return bool|null
     */
    public function getIsPaymentsReceivable()
    {
        return $this->paymentsReceivable;
    }

    /**
     * @return bool|null
     */
    public function getIsPrimaryEmailConfirmed()
    {
        return $this->primaryEmailConfirmed;
    }
}
