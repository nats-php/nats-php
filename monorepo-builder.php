<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $config): void {
    $config->defaultBranch('master');

    $config->packageDirectories([
        __DIR__ . '/packages',
    ]);

    $config->dataToAppend([
        'description' => 'Nats PHP',
        'homepage' => 'https://github.com/nats-php',
        'prefer-stable' => true,
        'minimum-stability' => 'dev',
        'type' => 'library',
        'license' => 'MIT',
        'authors' => [
            [
                'name'      => 'v.zanfir',
                'email'     => 'vadimzanfir@gmail.com',
                'role'      => 'maintainer',
            ],
        ],
        'require-dev' => [
            'symplify/monorepo-builder'     => '^11.2',
            'phpunit/phpunit'               => '^10.1',
            'vimeo/psalm'                   => '^5.12',
        ],
    ]);
};
