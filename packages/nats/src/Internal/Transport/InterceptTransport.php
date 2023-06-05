<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Transport;

use NatsPhp\Nats\Interceptor\ReceiveInterceptor;
use NatsPhp\Nats\Interceptor\RequestInterceptor;
use NatsPhp\Nats\Transport\RecvOptions;
use NatsPhp\Nats\Transport\Transport as TransportInterface;
use NatsPhp\Nats\Transport\ClosableTransport as ClosableTransportInterface;

/**
 * @internal
 *
 * @psalm-type TransportInterceptor = RequestInterceptor|ReceiveInterceptor
 */
final class InterceptTransport implements
    TransportInterface,
    ClosableTransportInterface
{
    /** @var RequestInterceptor[] */
    private array $requestInterceptors = [];

    /** @var ReceiveInterceptor[] */
    private array $receiveInterceptors = [];

    /**
     * @param TransportInterceptor[] $interceptors
     */
    public function __construct(
        private readonly TransportInterface $transport,
        array $interceptors,
    ) {
        foreach ($interceptors as $interceptor) {
            if ($interceptor instanceof RequestInterceptor) {
                $this->requestInterceptors[] = $interceptor;
            }

            if ($interceptor instanceof ReceiveInterceptor) {
                $this->receiveInterceptors[] = $interceptor;
            }
        }
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

        foreach ($this->requestInterceptors as $requestInterceptor) {
            $requestInterceptor->request($command);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recv(RecvOptions $options = new RecvOptions()): string
    {
        $resp = $this->transport->recv($options);

        foreach ($this->receiveInterceptors as $receiveInterceptor) {
            $receiveInterceptor->receive($resp);
        }

        return $resp;
    }
}
