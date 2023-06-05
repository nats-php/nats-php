<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal;

/**
 * @psalm-type HeadersType = array<non-empty-string, int|float|string|list<int|float|string>>
 */
final class Headers
{
    /**
     * @param non-empty-string $headersString
     *
     * @return HeadersType
     */
    public static function fromString(string $headersString): array
    {
        $headers = [];

        foreach (self::parseHeaders($headersString) as $key => $value) {
            if (isset($headers[$key])) {
                if (\is_array($headers[$key])) {
                    $headers[$key] = array_merge($headers[$key], [$value]);
                } else {
                    $headers[$key] = [$headers[$key], $value];
                }
            } else {
                $headers[$key] = $value;
            }
        }

        /** @var HeadersType */
        return $headers;
    }

    /**
     * @param HeadersType $headers
     *
     * @return non-empty-string
     */
    public static function toString(array $headers): string
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

        /** @var non-empty-string */
        return sprintf(
            "NATS/1.0\r\n%s\r\n\r\n",
            implode("\r\n", $normalizedHeaders),
        );
    }

    /**
     * @param non-empty-string $headersString
     *
     * @return \Generator<non-empty-string, int|float|string>
     */
    private static function parseHeaders(string $headersString): \Generator
    {
        $headers = explode("\r\n", trim($headersString));
        array_shift($headers);

        foreach ($headers as $header) {
            /**
             * @var non-empty-string $key
             * @var int|float|string $value
             */
            [$key, $value] = explode(': ', $header);

            yield $key => $value;
        }
    }
}
