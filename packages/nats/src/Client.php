<?php

declare(strict_types=1);

namespace NatsPhp\Nats;

use NatsPhp\Nats\Internal\Frame;
use NatsPhp\Nats\Internal;
use NatsPhp\Nats\Sid;
use NatsPhp\Nats\Transport\ConnectionOptions;
use NatsPhp\Nats\Transport\LazyTransport;
use NatsPhp\Nats\Transport\RecvOptions;
use NatsPhp\Nats\Transport\TLSCannotBeEstablished;
use NatsPhp\Nats\Transport\TlsTransport;
use NatsPhp\Nats\Transport\Transport;
use NatsPhp\Nats\Transport\TransportException;
use NatsPhp\Nats\Transport\TransportFactory;
use NatsPhp\Nats\Interceptor;

/**
 * @psalm-import-type Listener from Internal\Dispatcher
 * @psalm-import-type TransportInterceptor from Internal\Transport\InterceptTransport
 */
final class Client
{
    private Sid\SidFactory $sids;

    private Internal\Dispatcher $dispatcher;

    private function __construct(
        private readonly Transport $transport,
    ) {
        $this->sids = new Sid\HexadecimalRandomBytesSidFactory();
        $this->dispatcher = new Internal\Dispatcher();
    }

    public function withSidFactory(Sid\SidFactory $sids): Client
    {
        $client = clone $this;
        $client->sids = $sids;

        return $client;
    }

    /**
     * @throws TransportException
     */
    public function publish(OutgoingMessage $message): void
    {
        $this->transport->req(
            Internal\Command::publish($message),
        );
    }

    /**
     * @param non-empty-string      $subject
     * @param Listener              $listener
     * @param null|non-empty-string $group
     *
     * @throws TransportException
     * @throws ClientException
     */
    public function subscribe(string $subject, callable $listener, ?string $group = null): Sid\Sid
    {
        $sid = $this->sids->next();

        $this->transport->req(
            Internal\Command::subscribe($subject, $sid, $group),
        );

        $this->dispatcher->subscribe(
            $sid,
            $listener,
        );

        return $sid;
    }

    /**
     * @param int<0, max> $maxMsgs
     *
     * @throws TransportException
     * @throws ClientException
     */
    public function unsubscribe(Sid\Sid $sid, int $maxMsgs = 0): void
    {
        $this->transport->req(
            Internal\Command::unsubscribe($sid, $maxMsgs),
        );

        if (0 >= $maxMsgs) {
            $this->dispatcher->unsubscribe($sid);
        }
    }

    /**
     * @throws TransportException
     * @throws ClientException
     */
    public function consume(
        ListenOptions $listenOptions = new ListenOptions(),
        RecvOptions $recvOptions = new RecvOptions(),
    ): void {
        if (0 < \count($this->dispatcher)) {
            $receivedMessages = 0;

            while (true) {
                $resp = Frame\identify(
                    $this->transport->recv($recvOptions),
                );

                if ($resp instanceof Frame\Ping) {
                    $this->transport->req(Internal\Command::pong());

                    continue;
                }

                if ($resp instanceof Frame\Message) {
                    $this->dispatcher->dispatch(
                        $resp->create($this->transport),
                    );

                    if ($listenOptions->maxMessages > 0 && $listenOptions->maxMessages <= ++$receivedMessages) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param TransportInterceptor ...$interceptors
     *
     * @throws TransportException
     * @throws ClientException
     */
    public static function fromTransportFactory(
        TransportFactory $transportFactory,
        ConnectionOptions $options = new ConnectionOptions('tcp://127.0.0.1:6222'),
        Interceptor\RequestInterceptor|Interceptor\ReceiveInterceptor ...$interceptors,
    ): self {
        return new self(
            self::withHandshake(
                $transportFactory->connect(...),
                $options,
                $interceptors,
            ),
        );
    }

    /**
     * @param callable(ConnectionOptions): Transport $factory
     * @param TransportInterceptor                ...$interceptors
     */
    public static function lazy(
        callable $factory,
        ConnectionOptions $options = new ConnectionOptions('tcp://127.0.0.1:6222'),
        Interceptor\RequestInterceptor|Interceptor\ReceiveInterceptor ...$interceptors,
    ): self {
        return new self(
            new LazyTransport(
                static fn (): Transport => self::withHandshake(
                    $factory,
                    $options,
                    $interceptors,
                ),
            ),
        );
    }

    /**
     * @param callable(ConnectionOptions): Transport $factory
     * @param TransportInterceptor[]                 $interceptors
     *
     * @throws TransportException
     * @throws ClientException
     */
    private static function withHandshake(
        callable $factory,
        ConnectionOptions $connectionOptions,
        array $interceptors = [],
    ): Transport {
        $transport = $factory($connectionOptions);

        $resp = Frame\expects($transport->recv(), Frame\Info::class);

        if ($resp->tlsRequired) {
            if (false === $transport instanceof TlsTransport) {
                throw TLSCannotBeEstablished::dueToUnsupportedFeature($transport::class);
            }

            $transport->setupTls();
        }

        $transport->req([
            Internal\Command::connect($connectionOptions->jsonSerialize() + ['headers' => $resp->headers]),
            Internal\Command::ping(),
        ]);

        if (0 < \count($interceptors)) {
            $transport = new Internal\Transport\InterceptTransport(
                $transport,
                $interceptors,
            );
        }

        if ($connectionOptions->verbose) {
            Frame\expects($transport->recv(), Frame\Ok::class);

            $transport = new Internal\Transport\VerboseTransport($transport);
        }

        Frame\expects($transport->recv(), Frame\Pong::class);

        return $transport;
    }
}
