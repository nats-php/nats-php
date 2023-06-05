<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

interface ClosableTransport
{
    /**
     * @throws TransportException
     */
    public function close(): void;
}
