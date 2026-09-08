<?php

return [
    'driver' => env('SMS_DRIVER', 'log'),
    'from' => env('SMS_FROM'),
    'log_channel' => env('SMS_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
    'pretend' => (bool) env('SMS_PRETEND', false),
];