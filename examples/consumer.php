<?php

declare(strict_types=1);

use NatsPhp\Nats\Client;
use NatsPhp\Nats\IncomingMessage;
use NatsPhp\Nats\Sync\TransportFactory;
use NatsPhp\Nats\Transport\ConnectionOptions;

require_once __DIR__.'/../vendor/autoload.php';


$client = Client::fromTransportFactory(
    new TransportFactory(),
    new ConnectionOptions('tcp://127.0.0.1:4222', verbose: false),
);

$client->subscribe('events.payment_completed', function (IncomingMessage $message): void {
    var_dump($message);
});

$client->consume();
