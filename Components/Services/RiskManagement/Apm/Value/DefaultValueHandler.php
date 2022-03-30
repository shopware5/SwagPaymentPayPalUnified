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
    const SUPPORTED_PAYMENT_TYPES = [
        PaymentType::APM_BANCONTACT,
        PaymentType::APM_BLIK,
        PaymentType::APM_EPS,
        PaymentType::APM_GIROPAY,
        PaymentType::APM_IDEAL,
        PaymentType::APM_MULTIBANCO,
        PaymentType::APM_MYBANK,
        PaymentType::APM_OXXO,
        PaymentType::APM_P24,
        PaymentType::APM_SOFORT,
        PaymentType::APM_TRUSTLY,
    ];

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
        return \in_array($paymentType, self::SUPPORTED_PAYMENT_TYPES);
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
