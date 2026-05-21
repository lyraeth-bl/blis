<?php

return [
    'attendance_url' => env('REMOTE_ATTENDANCE_API_URL'),
    'notify_url' => env('NOTIFY_URL'),
    'validate_token_url' => env('VALIDATE_TOKEN_URL'),
    'token' => env('ACCESS_TOKEN'),
    'qr_token_secret' => env('QR_TOKEN_SECRET'),
    'timeout' => (int) env('SPO_TIMEOUT', 10),
    'connect_timeout' => (int) env('SPO_CONNECT_TIMEOUT', 5),
    'retry' => [
        'times' => (int) env('SPO_RETRY_TIMES', 2),
        'sleep' => (int) env('SPO_RETRY_SLEEP', 200),
    ],
];
