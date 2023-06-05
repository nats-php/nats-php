<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Sid;

final class Sid implements \Stringable
{
    /** @var non-empty-string */
    private readonly string $value;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $value)
    {
        if ('' === $value) {
            throw new \InvalidArgumentException('Sid value must not be empty.');
        }

        $this->value = $value;
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
