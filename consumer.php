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
    ],
    'url' => '',
];

$connection = new AMQPStreamConnection(
    $config['connection']['host'],
    $config['connection']['port'],
    $config['connection']['username'],
    $config['connection']['password']
);

$channel = $connection->channel();

function getUrl($url)
{
    $ch = curl_init();

    $optArray = [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    ];

    curl_setopt_array($ch, $optArray);
    curl_exec($ch);

    $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $response;
}

$callback = function(AMQPMessage $msg) use ($config)
{
    if (200 === getUrl($config['url']))
    {
        echo sprintf(
            "[%s][OK] %s \n",
            date('Y-m-d H:i:s'),
            $msg->body
        );

        $msg->delivery_info['channel']->basic_ack(
            $msg->delivery_info['delivery_tag']
        );
    }
    else
    {
        echo sprintf(
            "[%s][ERROR] %s \n",
            date('Y-m-d H:i:s'),
            $msg->body
        );

        $msg->delivery_info['channel']->basic_nack(
            $msg->delivery_info['delivery_tag'],
            false,
            true
        );
    }

    sleep(1);
};

$channel->basic_consume(
    $config['queue'],
    '',
    false,
    false,
    false,
    false,
    $callback
);

while (count($channel->callbacks))
{
    $channel->wait();
}
