<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Assets;

use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;

final class Translations
{
    const CONFIG_PAYMENT_TRANSLATIONS = [
        'en_GB' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME => [
                'description' => 'PayPal',
                'additionalDescription' => <<<'EOD'
<!-- PayPal Logo -->
<a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank" rel="noopener">
    <img src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png' fullPath}" alt="Logo 'PayPal recommended'">
</a>
<br>
<!-- PayPal Logo -->
<span>Paying with PayPal - easy, fast and secure.</span>
EOD
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME => [
                'description' => 'Credit or debit card',
                'additionalDescription' => 'Pay easily, quickly and conveniently with your credit or debit card',
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME => [
                'description' => 'Invoice',
                'additionalDescription' => '',
            ],
        ],
        'de_DE' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME => [
                'description' => 'PayPal',
                'additionalDescription' => <<<'EOD'
<!-- PayPal Logo -->
<a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank" rel="noopener">
    <img src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png' fullPath}" alt="Logo 'PayPal empfohlen'">
</a>
<br>
<!-- PayPal Logo -->
<span>Bezahlung per PayPal - einfach, schnell und sicher.</span>
EOD
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME => [
                'description' => 'Kreditkarte oder Debitkarte',
                'additionalDescription' => 'Bezahlen Sie einfach, schnell und bequem mit Ihrer Kredit- oder Debitkarte',
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME => [
                'description' => 'Kauf auf Rechnung',
                'additionalDescription' => '',
            ],
        ],
    ];

    private function __construct()
    {
    }
}
