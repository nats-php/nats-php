<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal;

use NatsPhp\Nats\IncomingMessage;
use NatsPhp\Nats\Sid\Sid;

/**
 * @internal
 *
 * @psalm-type Listener = callable(IncomingMessage): void
 *
 * @template-implements \IteratorAggregate<non-empty-string, Listener>
 */
final class Dispatcher implements
    \Countable,
    \IteratorAggregate
{
    /**
     * @var array<non-empty-string, Listener>
     */
    private array $subscribers = [];

    /**
     * @param Listener $listener
     */
    public function subscribe(Sid $sid, callable $listener): void
    {
        $this->subscribers[(string) $sid] = $listener;
    }

    public function dispatch(IncomingMessage $message): void
    {
        $sid = (string) $message->sid;

        if (isset($this->subscribers[$sid])) {
            $this->subscribers[$sid]($message);
        }
    }

    public function unsubscribe(Sid $sid): void
    {
        unset($this->subscribers[(string) $sid]);
    }

    public function count(): int
    {
        return \count($this->subscribers);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        yield from $this->subscribers;
    }
}
