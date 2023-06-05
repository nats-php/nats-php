<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

final class RequestFailed extends \RuntimeException implements TransportException
{
    public static function dueToUnexpectedlyClosedConnection(): self
    {
        return new self(
            'Request failed due to unexpectedly closed connection',
        );
    }

    public static function dueToDataSubmissionError(): self
    {
        return new self(
            'Request failed due to a data submission error',
        );
    }

    public static function dueToDataReceiveError(): self
    {
        return new self(
            'Request failed due to a data receive error',
        );
    }

    public static function dueToBrokenPipe(): self
    {
        return new self(
            'Request failed due to broken pipe or closed connection'
        );
    }
}
