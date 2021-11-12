<?php

require_once __DIR__ . '/vendor/autoload.php';

return
[
    'paths' => [
        'migrations' => __DIR__ . '/tests/migrations',
        'entitities' => __DIR__ . '/tests/Fixtures/Entity'
    ],
    'entityPrefix' => 'Warp\Tests\Fixtures\Entity',
    'environments' => [
        'default_migration_table' => '_phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'mysql',
            'name' => 'warp',
            'user' => 'root',
            'pass' => 'pl4T_0Rm',
            'port' => 3306,
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
