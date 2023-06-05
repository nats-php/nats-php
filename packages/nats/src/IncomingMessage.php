<?php

declare(strict_types=1);

namespace NatsPhp\Nats;

use NatsPhp\Nats\Sid\Sid;

final class IncomingMessage
{
    /**
     * @param non-empty-string                                                 $subject
     * @param null|non-empty-string                                            $replyTo
     * @param array<non-empty-string, int|float|string|list<int|float|string>> $headers
     */
    public function __construct(
        public readonly string $subject,
        public readonly Sid $sid,
        public readonly ?string $message = null,
        public readonly ?string $replyTo = null,
        public readonly array $headers = [],
    ) {
    }
}
