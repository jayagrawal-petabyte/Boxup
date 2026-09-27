<?php
declare(strict_types=1);

return [
    'site_name' => 'BrainMint AI Consultancy',
    'admin_email' => '',
    'storage_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
    'rate_limit' => [
        'max_attempts' => 6,
        'window_seconds' => 300,
    ],
];
