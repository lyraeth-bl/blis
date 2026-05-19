<?php

return [
    'attendance_url' => env('REMOTE_ATTENDANCE_API_URL'),
    'notify_url' => env('NOTIFY_URL'),
    'token' => env('ACCESS_TOKEN'),
    'timeout' => (int) env('SPO_TIMEOUT', 10),
    'connect_timeout' => (int) env('SPO_CONNECT_TIMEOUT', 5),
    'retry' => [
        'times' => (int) env('SPO_RETRY_TIMES', 2),
        'sleep' => (int) env('SPO_RETRY_SLEEP', 200),
    ],
];
