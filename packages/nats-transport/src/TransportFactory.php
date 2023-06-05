<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

interface TransportFactory
{
    /**
     * @throws TransportException
     */
    public function connect(ConnectionOptions $options): Transport;
}
