<?php

declare(strict_types=1);

use NatsPhp\Nats\Client;
use NatsPhp\Nats\OutgoingMessage;
use NatsPhp\Nats\Sync\TransportFactory;
use NatsPhp\Nats\Transport\ConnectionOptions;

require_once __DIR__.'/../vendor/autoload.php';

$client = Client::fromTransportFactory(
    new TransportFactory(),
    new ConnectionOptions('tcp://127.0.0.1:4222', verbose: true),
);

$client->publish(
    new OutgoingMessage('events.payment_completed', '{"id": 1}', ['x-correlation-id' => '1']),
);

$client->publish(
    new OutgoingMessage('events.payment_completed', '{"id": 2}', ['x-correlation-id' => '2']),
);
