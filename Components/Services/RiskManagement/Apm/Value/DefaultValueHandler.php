<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm\Value;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Apm\ValueHandlerInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class DefaultValueHandler implements ValueHandlerInterface
{
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(ContextServiceInterface $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return \in_array($paymentType, PaymentType::getApmPaymentTypes());
    }

    /**
     * {@inheritDoc}
     */
    public function createValue(array $basket, array $user)
    {
        return [
            'country' => $user['additional']['country']['countryiso'],
            'currency' => $this->contextService->getShopContext()->getCurrency()->getCurrency(),
            'amount' => $basket['AmountNumeric'],
        ];
    }
}
