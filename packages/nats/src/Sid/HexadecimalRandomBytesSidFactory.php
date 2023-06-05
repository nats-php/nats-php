<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Sid;

final class HexadecimalRandomBytesSidFactory implements SidFactory
{
    private const DEFAULT_LENGTH = 12;

    /**
     * @param positive-int $length
     */
    public function __construct(
        private readonly int $length = self::DEFAULT_LENGTH,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function next(): Sid
    {
        return new Sid(bin2hex(random_bytes($this->length)));
    }
}
