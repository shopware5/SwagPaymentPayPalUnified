<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists(__DIR__ . '/config-original.php')) {
    $defaultConfig = require __DIR__ . '/config-original.php';
} else {
    return;
}

return [
    'db' => $defaultConfig['db'],

    'csrfProtection' => [
        'frontend' => false,
        'backend' => false,
    ],
];
