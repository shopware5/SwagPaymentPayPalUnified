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
                'description' => 'Pay upon invoice',
                'additionalDescription' => '',
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME => [
                'description' => 'Direct debit',
                'additionalDescription' => <<<'EOD'
<img src="{link file='frontend/_public/src/img/sepa_payment.png' fullPath}" alt="SEPA Direct debit">
EOD
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Pay in 3',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
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
                'description' => 'Kredit- oder Debitkarte',
                'additionalDescription' => 'Bezahlen Sie einfach, schnell und bequem mit Ihrer Kredit- oder Debitkarte',
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME => [
                'description' => 'Rechnungskauf',
                'additionalDescription' => '',
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME => [
                'description' => 'Lastschrift',
                'additionalDescription' => <<<'EOD'
<img src="{link file='frontend/_public/src/img/sepa_payment.png' fullPath}" alt="SEPA Lastschrift'">
EOD
            ],
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Später Bezahlen',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
        'en_US' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Pay Later',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
        'fr_FR' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Paiement en 4X',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
        'it_IT' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Paga in 3 rate',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
        'es_ES' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Paga en 3 plazos',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
        'en_AU' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                'description' => 'PayPal, Pay in 4',
                'additionalDescription' => <<<'EOD'
<!-- PayPal PayLater Message -->
<div data-pp-message data-pp-style-logo-type="primary" data-pp-placement="payment" data-pp-style-logo-position="left" data-pp-style-text-size="14" data-pp-style-text-color="black" style="margin-top: 5px;"></div>
EOD
            ],
        ],
    ];

    private function __construct()
    {
    }
}
