<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$config = [
    'queue' => 'reports',
    'connection' => [
        'host' => 'localhost',
        'port' => '5672',
        'username' => 'guest',
        'password' => 'guest',
    ]
];

$connection = new AMQPStreamConnection(
    $config['connection']['host'],
    $config['connection']['port'],
    $config['connection']['username'],
    $config['connection']['password']
);

$channel = $connection->channel();

$messageBody = 'Zadanie #1';

$channel->basic_publish(
    new AMQPMessage($messageBody),
    '',
    $config['queue']
);

echo $messageBody . PHP_EOL;
