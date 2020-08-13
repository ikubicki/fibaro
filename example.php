<?php

$config = [
    'url' => 'http://192.168.0.2',
    'user' => 'api-user',
    'pass' => 'ApiPassWord!23',
    'map' => [ // device ids
        'wind' => [
            'speed' => 1,
            'angle' => 2,
        ],
        'gust' => [
            'speed' => 3,
            'angle' => 4,
        ],
        'rain' => [
            '5m' => 5,
            '1h' => 6,
            '1d' => 7,
        ],
        'pressure' => 8,
        'humidity' => 9,
        'temperature' => 10,
    ],
];

require __DIR__ . '/vendor/autoload.php';

$fibaroClient = new Irekk\Fibaro\Client($config);
$metrics = $fibaroClient->getMetrics();
var_dump($metrics);