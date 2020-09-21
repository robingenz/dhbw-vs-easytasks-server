<?php
return ['settings' => [
    'displayErrorDetails' => evalBool(getenv('APP_DEBUG')),
    'db' => [
        'driver' => getenv('DB_DRIVER'),
        'hostname' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT'),
        'database' => getenv('DB_NAME'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
    ],
]];
