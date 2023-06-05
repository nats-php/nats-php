<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Sid;

interface SidFactory
{
    /**
     * @throws \InvalidArgumentException
     */
    public function next(): Sid;
}
