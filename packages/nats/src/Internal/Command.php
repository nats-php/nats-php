<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal;

use NatsPhp\Nats\OutgoingMessage;
use NatsPhp\Nats\Sid\Sid;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Command implements \Stringable
{
    private const CRLF = "\r\n";

    /**
     * @param non-empty-string $cmd
     */
    private function __construct(
        private readonly string $cmd,
    ) {
    }

    /**
     * @pure
     */
    public static function connect(array $options): self
    {
        try {
            return self::withCRLF('CONNECT ' . json_encode($options, \JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid options provided.', previous: $e);
        }
    }

    /**
     * @pure
     */
    public static function ping(): self
    {
        return self::withCRLF('PING');
    }

    /**
     * @pure
     */
    public static function pong(): self
    {
        return self::withCRLF('PONG');
    }

    public static function publish(OutgoingMessage $message): self
    {
        /** @psalm-var non-empty-string $cmd */
        $cmd = sprintf('%s %s', \count($message->headers) > 0 ? 'HPUB' : 'PUB', $message->subject);

        if (null !== $message->replyTo) {
            $cmd .= ' ' . $message->replyTo;
        }

        if (\count($message->headers) > 0) {
            $headers = Headers::toString($message->headers);

            $cmd .= ' ' . mb_strlen($headers);
            $cmd .= ' ' . (mb_strlen($headers) + mb_strlen($message->message));
            $cmd .= self::CRLF;
            $cmd .= $headers;
        } else {
            $cmd .= ' ' . mb_strlen($message->message);
            $cmd .= self::CRLF;
        }

        $cmd .= $message->message;

        return self::withCRLF($cmd);
    }

    /**
     * @param non-empty-string      $subject
     * @param null|non-empty-string $group
     */
    public static function subscribe(string $subject, Sid $sid, ?string $group = null): self
    {
        return self::withCRLF(
            null !== $group ? sprintf('SUB %s %s %s', $subject, $group, (string) $sid) : sprintf('SUB %s %s', $subject, (string) $sid),
        );
    }

    /**
     * @param int<0, max> $maxMsgs
     */
    public static function unsubscribe(Sid $sid, int $maxMsgs = 0): self
    {
        $sid = (string) $sid;

        return self::withCRLF(
            0 < $maxMsgs ? sprintf('UNSUB %s %d', $sid, $maxMsgs) : 'UNSUB ' . $sid,
        );
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->cmd;
    }

    /**
     * @pure
     *
     * @param non-empty-string $cmd
     */
    private static function withCRLF(string $cmd): self
    {
        return new self($cmd . self::CRLF);
    }

    /**
     * @param array<non-empty-string, int|float|string|list<int|float|string>> $headers
     */
    private static function headersToString(array $headers): string
    {
        $normalizedHeaders = [];

        foreach ($headers as $key => $value) {
            if (false === is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $it) {
                $normalizedHeaders[] = sprintf('%s: %s', $key, $it);
            }
        }

        return sprintf(
            "NATS/1.0\r\n%s\r\n\r\n",
            implode(self::CRLF, $normalizedHeaders),
        );
    }
}
