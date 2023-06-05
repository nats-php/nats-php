<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Frame;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Err implements \Stringable
{
    public function __construct(
        public readonly string $message,
    ) {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
