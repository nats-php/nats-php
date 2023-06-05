<?php

declare(strict_types=1);

namespace NatsPhp\Nats;

final class ListenOptions
{
    /**
     * @param int<-1, max> $maxMessages
     */
    public function __construct(
        public readonly int $maxMessages = -1,
    ) {
    }
}
