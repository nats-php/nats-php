<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Tests\Internal;

use NatsPhp\Nats\Internal\Command;
use NatsPhp\Nats\OutgoingMessage;
use NatsPhp\Nats\Sid\Sid;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    /**
     * @return iterable<array-key, array{Command, non-empty-string}>
     */
    public static function fixtures(): iterable
    {
        yield [
            Command::ping(),
            "PING\r\n",
        ];
        yield [
            Command::pong(),
            "PONG\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('FOO', 'Hello NATS!'),
            ),
            "PUB FOO 11\r\nHello NATS!\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('FRONT.DOOR', 'Knock Knock', replyTo: 'JOKE.22'),
            ),
            "PUB FRONT.DOOR JOKE.22 11\r\nKnock Knock\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('NOTIFY'),
            ),
            "PUB NOTIFY 0\r\n\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('FOO', 'Hello NATS!', ['Bar' => 'Baz']),
            ),
            "HPUB FOO 22 33\r\nNATS/1.0\r\nBar: Baz\r\n\r\nHello NATS!\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('FRONT.DOOR', 'Knock Knock', ['BREAKFAST' => 'donut', 'LUNCH' => 'burger'], 'JOKE.22'),
            ),
            "HPUB FRONT.DOOR JOKE.22 45 56\r\nNATS/1.0\r\nBREAKFAST: donut\r\nLUNCH: burger\r\n\r\nKnock Knock\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('NOTIFY', headers: ['Bar' => 'Baz']),
            ),
            "HPUB NOTIFY 22 22\r\nNATS/1.0\r\nBar: Baz\r\n\r\n\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('NOTIFY', headers: ['Bar' => 'Baz']),
            ),
            "HPUB NOTIFY 22 22\r\nNATS/1.0\r\nBar: Baz\r\n\r\n\r\n",
        ];
        yield [
            Command::publish(
                new OutgoingMessage('MORNING.MENU', 'Yum!', ['BREAKFAST' => ['donut', 'eggs']]),
            ),
            "HPUB MORNING.MENU 47 51\r\nNATS/1.0\r\nBREAKFAST: donut\r\nBREAKFAST: eggs\r\n\r\nYum!\r\n",
        ];
        yield [
            Command::subscribe('FOO', new Sid('1')),
            "SUB FOO 1\r\n",
        ];
        yield [
            Command::subscribe('BAR', new Sid('44'), 'G1'),
            "SUB BAR G1 44\r\n",
        ];
        yield [
            Command::unsubscribe(new Sid('1')),
            "UNSUB 1\r\n",
        ];
        yield [
            Command::unsubscribe(new Sid('1'), 5),
            "UNSUB 1 5\r\n",
        ];
        yield [
            Command::connect(['lang' => 'php', 'version' => 1.0]),
            "CONNECT {\"lang\":\"php\",\"version\":1}\r\n"
        ];
    }

    /**
     * @dataProvider fixtures
     *
     * @param non-empty-string $proto
     */
    public function testCommand(Command $command, string $proto): void
    {
        self::assertSame($proto, (string) $command);
    }
}
