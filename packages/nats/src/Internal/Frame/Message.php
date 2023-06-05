<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Frame;

use NatsPhp\Nats\ClientException;
use NatsPhp\Nats\IncomingMessage;
use NatsPhp\Nats\Internal\Headers;
use NatsPhp\Nats\Sid\Sid;
use NatsPhp\Nats\Transport\RecvOptions;
use NatsPhp\Nats\Transport\Transport;
use NatsPhp\Nats\Transport\TransportException;
use NatsPhp\Nats\UnexpectedFrameType;

/**
 * @internal
 */
final class Message
{
    /**
     * @param non-empty-string $req
     */
    public function __construct(
        private readonly string $req,
    ) {
    }

    /**
     * @throws ClientException
     * @throws TransportException
     */
    public function create(Transport $transport): IncomingMessage
    {
        /** @var array{0: non-empty-string} $fragments */
        $fragments = explode(' ', trim($this->req));

        return match ($fragments[0]) {
            'MSG' => $this->fromMSG($transport),
            'HMSG' => $this->fromHMSG($transport),
            default => throw UnexpectedFrameType::expects($this->req, IncomingMessage::class),
        };
    }

    /**
     * @throws TransportException
     */
    private function fromMSG(Transport $transport): IncomingMessage
    {
        /**
         * @var array{
         *     0: non-empty-string,
         *     1: non-empty-string,
         *     2: non-empty-string,
         *     3: non-empty-string|int<0, max>,
         *     4?: non-empty-string|int<0, max>,
         * } $fragments
         */
        $fragments = explode(' ', trim($this->req));

        [$subject, $sid, $reply, $payload] = [$fragments[1], $fragments[2], null, ''];

        if (5 === \count($fragments)) {
            /** @var non-empty-string $reply */
            $reply = $fragments[3];

            /** @var numeric-string $length */
            $length = $fragments[4];
        } else {
            /** @var numeric-string $length */
            $length = $fragments[3];
        }

        if (0 < ($bodySize = (int) $length)) {
            $payload = $transport->recv(new RecvOptions($bodySize));
        }

        return new IncomingMessage(
            $subject,
            new Sid($sid),
            $payload,
            $reply,
        );
    }

    private function fromHMSG(Transport $transport): IncomingMessage
    {
        /**
         * @var array{
         *     0: non-empty-string,
         *     1: non-empty-string,
         *     2: non-empty-string,
         *     3: non-empty-string|int<0, max>,
         *     4: non-empty-string|int<0, max>,
         *     5?: non-empty-string|int<0, max>,
         * } $fragments
         */
        $fragments = explode(' ', trim($this->req));

        [$subject, $sid, $reply, $headers, $payload] = [$fragments[1], $fragments[2], null, [], ''];

        if (6 === \count($fragments)) {
            /** @var non-empty-string $reply */
            $reply = $fragments[3];

            /** @var numeric-string $headersLength */
            $headersLength = $fragments[4];

            /** @var numeric-string $payloadLength */
            $payloadLength = $fragments[5];
        } else {
            /** @var numeric-string $headersLength */
            $headersLength = $fragments[3];

            /** @var numeric-string $payloadLength */
            $payloadLength = $fragments[4];
        }

        if (0 < ($headersSize = (int) $headersLength)) {
            $headers = Headers::fromString($transport->recv(new RecvOptions($headersSize)));
        }

        if (0 < (int) $payloadLength) {
            /** @psalm-var positive-int $payloadSize */
            $payloadSize = (int) $payloadLength - (int) $headersLength;

            $payload = $transport->recv(new RecvOptions($payloadSize));
        }

        return new IncomingMessage(
            $subject,
            new Sid($sid),
            $payload,
            $reply,
            $headers,
        );
    }
}
