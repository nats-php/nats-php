<?php

declare(strict_types=1);

namespace NatsPhp\Nats;

final class OutgoingMessage
{
    /**
     * @param non-empty-string                                                 $subject
     * @param array<non-empty-string, int|float|string|list<int|float|string>> $headers
     * @param null|non-empty-string                                            $replyTo
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $message = '',
        public readonly array $headers = [],
        public readonly ?string $replyTo = null,
    ) {
    }
}
