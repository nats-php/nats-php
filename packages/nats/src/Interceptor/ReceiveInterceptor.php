<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Interceptor;

interface ReceiveInterceptor
{
    /**
     * @param non-empty-string $payload
     */
    public function receive(string $payload): void;
}
