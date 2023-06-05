<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Interceptor;

use NatsPhp\Nats\Transport\Transport;

/**
 * @psalm-import-type Command from Transport
 */
interface RequestInterceptor
{
    /**
     * @psalm-param iterable<array-key, Command>|Command $command
     */
    public function request(iterable|\Stringable|string $command): void;
}
