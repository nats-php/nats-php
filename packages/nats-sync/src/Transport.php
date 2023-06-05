<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Sync;

use NatsPhp\Nats\Transport\RecvOptions;
use NatsPhp\Nats\Transport\ClosableTransport as ClosableTransportInterface;
use NatsPhp\Nats\Transport\TLSCannotBeEstablished;
use NatsPhp\Nats\Transport\Transport as TransportInterface;
use NatsPhp\Nats\Transport\TlsTransport as TlsTransportInterface;
use NatsPhp\Nats\Transport\RequestFailed;
use NatsPhp\Nats\Transport\TransportException;

final class Transport implements
    TransportInterface,
    TlsTransportInterface,
    ClosableTransportInterface
{
    /** @var resource|closed-resource */
    private $stream;

    /**
     * @param resource $stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function req(iterable|\Stringable|string $command): void
    {
        if (false === is_resource($this->stream)) {
            throw RequestFailed::dueToUnexpectedlyClosedConnection();
        }

        if (false === is_iterable($command)) {
            $command = [$command];
        }

        set_error_handler(static function (int $errorCode, string $error): bool {
            if (0 === (error_reporting() & $errorCode)) {
                return false;
            }

            throw new RequestFailed(sprintf('The request failed due to an error "%s".', $error));
        });

        try {
            foreach ($command as $it) {
                self::doReq($this->stream, $it);
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recv(RecvOptions $options = new RecvOptions()): string
    {
        if (false === is_resource($this->stream)) {
            throw RequestFailed::dueToUnexpectedlyClosedConnection();
        }

        set_error_handler(static function (int $errorCode, string $error): bool {
            if (0 === (error_reporting() & $errorCode)) {
                return false;
            }

            throw new RequestFailed(sprintf('The request failed due to an error "%s".', $error));
        });

        try {
            if (null !== $options->length) {
                /** @var non-empty-string */
                return implode(
                    '',
                    iterator_to_array(
                        self::doRecv($this->stream, $options->length, $options->chunkSize ?: $options->length),
                    ),
                );
            }

            /** @var false|non-empty-string $line */
            $line = fgets($this->stream);

            if (false === $line) {
                throw RequestFailed::dueToDataReceiveError();
            }

            return $line;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setupTls(): void
    {
        if (false === is_resource($this->stream)) {
            throw new TLSCannotBeEstablished('Broken pipe or closed connection');
        }

        if (false === \stream_socket_enable_crypto($this->stream, enable: true)) {
            throw new TLSCannotBeEstablished('TLS negotiation failed: Unknown error');
        }
    }

    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * @param resource                     $stream
     * @param non-empty-string|\Stringable $command
     *
     * @throws TransportException
     */
    private static function doReq($stream, string|\Stringable $command): void
    {
        $req = (string) $command;
        $length = mb_strlen($req);

        while ($length > 0) {
            $written = fwrite($stream, substr($req, 0 - $length));

            if (false === $written) {
                throw RequestFailed::dueToDataSubmissionError();
            }

            if (0 === $written) {
                throw RequestFailed::dueToBrokenPipe();
            }

            $length -= $written;
        }
    }

    /**
     * @param resource     $stream
     * @param positive-int $length
     * @param positive-int $chunkSize
     *
     * @throws TransportException
     *
     * @return \Generator<non-empty-string>
     */
    private static function doRecv($stream, int $length, int $chunkSize): \Generator
    {
        $readBytes = 0;

        while ($length > $readBytes) {
            if (($rest = ($length - $readBytes)) < $chunkSize) {
                $chunkSize = $rest;
            }

            /** @var false|non-empty-string $chunk */
            $chunk = fread($stream, $chunkSize);

            if (false === $chunk) {
                throw RequestFailed::dueToDataReceiveError();
            }

            $readBytes += mb_strlen($chunk);

            yield $chunk;
        }
    }
}
