<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Tests\Internal;

use NatsPhp\Nats\Internal\Headers;
use PHPUnit\Framework\TestCase;

/**
 * @covers \NatsPhp\Nats\Internal\Headers
 *
 * @psalm-import-type HeadersType from Headers
 */
final class HeadersTest extends TestCase
{
    /**
     * @return iterable<array-key, array{HeadersType, non-empty-string}>
     */
    public static function fixtures(): iterable
    {
        yield [
            ['Bar' => 'Baz'],
            "NATS/1.0\r\nBar: Baz\r\n\r\n",
        ];
        yield [
            ['Bar' => 'Baz', 'Foo' => 'Bar'],
            "NATS/1.0\r\nBar: Baz\r\nFoo: Bar\r\n\r\n",
        ];
        yield [
            ['Bar' => ['Foo', 'Baz']],
            "NATS/1.0\r\nBar: Foo\r\nBar: Baz\r\n\r\n",
        ];
        yield [
            ['Bar' => ['Foo', 'Baz'], 'Breakfast' => 'Lunch'],
            "NATS/1.0\r\nBar: Foo\r\nBar: Baz\r\nBreakfast: Lunch\r\n\r\n",
        ];
    }

    /**
     * @dataProvider fixtures
     *
     * @psalm-param HeadersType $headers
     * @param non-empty-string  $headersString
     */
    public function testToString(array $headers, string $headersString): void
    {
        self::assertSame($headersString, Headers::toString($headers));
        self::assertSame($headers, Headers::fromString($headersString));
    }
}
