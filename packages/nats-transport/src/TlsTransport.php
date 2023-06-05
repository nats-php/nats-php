<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

interface TlsTransport
{
    /**
     * @throws TransportException
     */
    public function setupTls(): void;
}
