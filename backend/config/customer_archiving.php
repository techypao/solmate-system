<?php

return [
    'inactivity_days' => (int) env('CUSTOMER_ARCHIVE_INACTIVITY_DAYS', 60),
    'warning_days' => (int) env('CUSTOMER_ARCHIVE_WARNING_DAYS', 7),
    'chunk_size' => (int) env('CUSTOMER_ARCHIVE_CHUNK_SIZE', 200),
    'blocked_message' => env(
        'CUSTOMER_ARCHIVE_BLOCKED_MESSAGE',
        'Your account has been archived due to inactivity. Please contact support to reactivate.'
    ),
];