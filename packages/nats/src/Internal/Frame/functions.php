<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Frame;

use NatsPhp\Nats\UnexpectedFrameType;

/**
 * @internal
 *
 * @param non-empty-string $req
 */
function identify(
    string $req,
): ?object {
    $fragments = explode(' ', trim($req));
    $cmd = trim($fragments[0]);

    return match ($cmd) {
        'PING' => new Ping(),
        'PONG' => new Pong(),
        'INFO' => Info::fromJson($fragments[1]),
        '+OK' => new Ok(),
        '-ERR' => new Err($fragments[1] ?? ''),
        'MSG', 'HMSG' => new Message($req),
        default => null,
    };
}

/**
 * @internal
 *
 * @template T of object
 *
 * @param non-empty-string   $req
 * @param class-string<T> ...$frameTypes
 *
 * @return T
 */
function expects(string $req, string ...$frameTypes): object
{
    $cmd = namespace\identify($req);

    foreach ($frameTypes as $frameType) {
        if ($cmd instanceof $frameType) {
            return $cmd;
        }
    }

    throw UnexpectedFrameType::expects($req, ...$frameTypes);
}
