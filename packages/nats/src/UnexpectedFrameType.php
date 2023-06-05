<?php

declare(strict_types=1);

namespace NatsPhp\Nats;

final class UnexpectedFrameType extends \RuntimeException implements ClientException
{
    /**
     * @param non-empty-string $recv
     * @param class-string     ...$frameTypes
     */
    public static function expects(string $recv, string ...$frameTypes): self
    {
        return new self('Expected one of frame type: ' . implode(', ', $frameTypes) . '. Received: ' . $recv);
    }
}
