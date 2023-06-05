<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

final class RecvOptions
{
    /**
     * @param null|positive-int $length
     * @param null|positive-int $timeout
     * @param null|positive-int $chunkSize
     */
    public function __construct(
        public readonly ?int $length = null,
        public readonly ?int $timeout = null,
        public readonly ?int $chunkSize = null,
    ) {
    }
}
