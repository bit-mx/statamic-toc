<?php

declare(strict_types=1);

return [
    'min_level' => 1,
    'max_level' => 6,
    'preserve_existing_ids' => true,
    'default_source' => 'html',
    'default_tree' => true,

    'cache' => [
        'enabled' => false,
        'ttl' => 600,
        'store' => null,
        'prefix' => 'statamic_toc',
    ],
];
