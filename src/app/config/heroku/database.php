<?php

$url = parse_url(getenv('DATABASE_URL'));

return [
    'default' => 'pgsql',
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $url['host'],
            'database' => substr($url['path'], 1),
            'username' => $url['user'],
            'password' => $url['pass'],
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],
    ],
];
