<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Transport;

use Composer\InstalledVersions;

final class ConnectionOptions implements \JsonSerializable
{
    public const DEFAULT_CONNECTION_TIMEOUT = 10_000;

    /**
     * @var resource
     */
    private $streamContext;

    /**
     * @param non-empty-string      $host
     * @param positive-int          $timeout in milliseconds
     * @param null|non-empty-string $user
     * @param null|non-empty-string $password
     * @param null|non-empty-string $token
     * @param null|resource         $streamContext
     */
    public function __construct(
        public readonly string $host,
        public readonly int $timeout = self::DEFAULT_CONNECTION_TIMEOUT,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
        private readonly ?string $token = null,
        public readonly bool $verbose = false,
        public readonly bool $pedantic = false,
        $streamContext = null,
    ) {
        $this->streamContext = $streamContext ?: stream_context_get_default();
    }

    /**
     * @return resource
     */
    public function context()
    {
        return $this->streamContext;
    }

    /**
     * @return array{
     *     lang: non-empty-string,
     *     version: string,
     *     verbose: bool,
     *     pedantic: bool,
     *     user?: non-empty-string,
     *     pass?: non-empty-string,
     *     auth_token?: non-empty-string
     * }
     */
    public function jsonSerialize(): array
    {
        $options = [
            'lang' => 'php',
            'version' => InstalledVersions::getVersion('nats-php/nats-transport') ?: '1.0',
            'verbose' => $this->verbose,
            'pedantic' => $this->pedantic,
        ];

        if (null !== $this->user) {
            $options['user'] = $this->user;
        }

        if (null !== $this->password) {
            $options['pass'] = $this->password;
        }

        if (null !== $this->token) {
            $options['auth_token'] = $this->token;
        }

        return $options;
    }
}
