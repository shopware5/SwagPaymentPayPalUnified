<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    [
        'id' => '5',
        'name' => 'prepayment',
        'description' => 'Vorkasse',
        'additionaldescription' => 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.',
    ],
    [
        'id' => '3',
        'name' => 'cash',
        'description' => 'Nachnahme',
        'additionaldescription' => '(zzgl. 2,00 Euro Nachnahmegebühren)',
        'swag_paypal_unified_display_in_plus_iframe' => null,
    ],
    [
        'id' => '4',
        'name' => 'invoice',
        'description' => 'Rechnung',
        'additionaldescription' => 'Sie zahlen einfach und bequem auf Rechnung.',
        'swag_paypal_unified_display_in_plus_iframe' => true,
    ],
];
