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
    /**
     * @var bool
     */
    private $isCapable;

    /**
     * @var bool
     */
    private $hasLimits = false;

    /**
     * @param bool                     $isCapable
     * @param array<string,mixed>|null $limits
     */
    public function __construct($isCapable, array $limits = null)
    {
        $this->isCapable = $isCapable;

        if ($limits !== null) {
            $this->hasLimits = true;
        }
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
}
