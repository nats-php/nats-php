<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Transport;

use NatsPhp\Nats\Transport\RecvOptions;
use NatsPhp\Nats\Transport\Transport as TransportInterface;
use NatsPhp\Nats\Transport\ClosableTransport as ClosableTransportInterface;
use NatsPhp\Nats\Internal\Frame;

/**
 * @internal
 */
final class VerboseTransport implements
    TransportInterface,
    ClosableTransportInterface
{
    public function __construct(
        private readonly TransportInterface $transport,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if ($this->transport instanceof ClosableTransportInterface) {
            $this->transport->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function req(iterable|\Stringable|string $command): void
    {
        $this->transport->req($command);

        Frame\expects(
            $this->transport->recv(),
            Frame\Ok::class,
            Frame\Message::class,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function recv(RecvOptions $options = new RecvOptions()): string
    {
        return $this->transport->recv($options);
    }
}
