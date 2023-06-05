<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

/**
 * @psalm-type LazyFactory = callable(): Transport
 */
final class LazyTransport implements
    Transport,
    TlsTransport,
    ClosableTransport
{
    private ?Transport $transport = null;

    /**
     * @var LazyFactory
     */
    private $factory;

    /**
     * @param LazyFactory $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function req(iterable|\Stringable|string $command): void
    {
        $this->transport ??= ($this->factory)();

        $this->transport->req($command);
    }

    /**
     * {@inheritdoc}
     */
    public function recv(RecvOptions $options = new RecvOptions()): string
    {
        $this->transport ??= ($this->factory)();

        return $this->transport->recv($options);
    }

    public function close(): void
    {
        if ($this->transport instanceof ClosableTransport) {
            $this->transport->close();
        }
    }

    public function setupTls(): void
    {
        $this->transport ??= ($this->factory)();

        if (false === $this->transport instanceof TlsTransport) {
            throw TLSCannotBeEstablished::dueToUnsupportedFeature($this->transport::class);
        }

        $this->transport->setupTls();
    }
}
