<?php

return [
    'enabled' => env('SUPERVISOR_ENABLED', false),
    'host' => env('SUPERVISOR_HOST', '127.0.0.1'),
    'port' => env('SUPERVISOR_PORT', 9001),
    'process_name' => env('SUPERVISOR_PROCESS_NAME', 'laravel'),
];
