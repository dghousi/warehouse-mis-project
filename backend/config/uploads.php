<?php

declare(strict_types=1);

return [
    'allowed_modules' => [
        'user-management' => [
            'directory' => 'user-management/uploads',
        ],
        'settings' => [
            'directory' => 'settings/uploads',
        ],
    ],

    'rate_limit' => [
        'max_attempts' => 10,       // Maximum uploads allowed
        'decay_seconds' => 60,      // Time window in seconds
    ],
];
