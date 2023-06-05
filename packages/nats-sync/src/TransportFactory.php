<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Sync;

use NatsPhp\Nats\Transport\ConnectionFailed;
use NatsPhp\Nats\Transport\ConnectionOptions;
use NatsPhp\Nats\Transport\Transport as TransportInterface;
use NatsPhp\Nats\Transport\TransportFactory as TransportFactoryInterface;

final class TransportFactory implements TransportFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(ConnectionOptions $options): TransportInterface
    {
        set_error_handler(static function (int $errorCode, string $error): bool {
            if (0 === (error_reporting() & $errorCode)) {
                return false;
            }

            throw new ConnectionFailed(
                sprintf('The connection could not be established due to an error "%s".', $error),
            );
        });

        try {
            $stream = stream_socket_client(
                $options->host,
                $_,
                $errorMessage,
                timeout: $options->timeout / 1_000,
                context: $options->context(),
            );

            if (false === $stream) {
                throw new ConnectionFailed(
                    sprintf('The connection could not be established due to an error "%s".', $errorMessage),
                );
            }

            stream_set_timeout($stream, (int) ($options->timeout / 1_000), $options->timeout * 1_000);

            return new Transport(
                $stream,
            );
        } finally {
            restore_error_handler();
        }
    }
}
