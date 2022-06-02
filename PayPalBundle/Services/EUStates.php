<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

class EUStates
{
    const EU_STATES = [
        'AT', // Republic of Austria
        'BE', // Kingdom of Belgium
        'BG', // Republic of Bulgaria
        'CY', // Republic of Cyprus
        'CZ', // Czech Republic
        'DE', // Federal Republic of Germany
        'DK', // Kingdom of Denmark
        'EE', // Republic of Estonia
        'EL', // Hellenic Republic (Greece)
        'GR', // Greece's second ISO-Code
        'ES', // Kingdom of Spain
        'FI', // Republic of Finland
        'FR', // French Republic
        'GB', // United Kingdom of Great Britain and Northern Ireland
        'HR', // Republic of Croatia
        'HU', // Hungary
        'IE', // Ireland
        'IT', // Italian Republic
        'LT', // Republic of Lithuania
        'LU', // Grand Duchy of Luxembourg
        'LV', // Republic of Latvia
        'MT', // Republic of Malta
        'NL', // Kingdom of the Netherlands
        'PL', // Republic of Poland
        'PT', // Portuguese Republic
        'RO', // Romania
        'SE', // Kingdom of Sweden
        'SI', // Republic of Slovenia
        'SK', // Slovak Republic
        'SM', // San Marino
        'XI', // Northern Ireland
    ];

    /**
     * A helper function that returns a boolean indicating if a country is in the EU or not
     *
     * @param string $countryIso
     *
     * @return bool
     */
    public function isEUCountry($countryIso)
    {
        return \in_array($countryIso, self::EU_STATES);
    }
}
