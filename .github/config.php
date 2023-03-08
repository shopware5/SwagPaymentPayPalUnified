<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'db' => [
        'username' => '__DB_USER__',
        'password' => '__DB_PASSWORD__',
        'dbname' => '__DB_NAME__',
        'host' => '__DB_HOST__',
        'port' => '__DB_PORT__',
    ],
    'errorHandler' => [
        'throwOnRecoverableError' => false,
    ],
    'front' => [
        'noErrorHandler' => true,
        'throwExceptions' => true,
    ],
    'phpsettings' => [
        'display_errors' => 1,
    ],
    'template' => [
        'forceCompile' => true,
    ],
    'httpcache' => [
        'enabled' => false,
        'debug' => true,
    ],
    'cache' => [
        'frontendOptions' => [
            'write_control' => false,
        ],
        'backend' => 'Black-Hole',
        'backendOptions' => [],
    ],
    'model' => [
        'cacheProvider' => 'Array',
    ],
    'csrfProtection' => [
        'backend' => false,
        'frontend' => false,
    ],
    'logger' => [
        'level' => \Shopware\Components\Logger::DEBUG,
    ],
];
