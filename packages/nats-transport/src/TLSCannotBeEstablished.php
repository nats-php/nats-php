<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

final class TLSCannotBeEstablished extends \RuntimeException implements TransportException
{
    public static function dueToUnsupportedFeature(string $transportName): self
    {
        return new self(
            "Transport $transportName does not support TLS."
        );
    }
}
