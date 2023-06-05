<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

/**
 * @psalm-type Command = non-empty-string|\Stringable
 */
interface Transport
{
    /**
     * @psalm-param iterable<array-key, Command>|Command $command
     *
     * @throws TransportException
     */
    public function req(iterable|\Stringable|string $command): void;

    /**
     * @throws TransportException
     *
     * @return non-empty-string
     */
    public function recv(RecvOptions $options = new RecvOptions()): string;
}
