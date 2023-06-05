<?php

declare(strict_types=1);

namespace NatsPhp\Nats\Internal\Frame;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class Info
{
    /**
     * @param non-empty-string         $serverId
     * @param non-empty-string         $serverName
     * @param non-empty-string         $version
     * @param non-empty-string         $go
     * @param non-empty-string         $host
     * @param int<0, max>              $port
     * @param int<0, max>              $maxPayload
     * @param int<0, max>              $proto
     * @param null|positive-int|string $clientId
     * @param string[]                 $connectUrls
     * @param string[]                 $wsConnectUrls
     */
    private function __construct(
        public readonly string $serverId,
        public readonly string $serverName,
        public readonly string $version,
        public readonly string $go,
        public readonly string $host,
        public readonly int $port,
        public readonly bool $headers,
        public readonly int $maxPayload,
        public readonly int $proto,
        public readonly null|int|string $clientId = null,
        public readonly bool $authRequired = false,
        public readonly bool $tlsRequired = false,
        public readonly bool $tlsVerify = false,
        public readonly bool $tlsAvailable = false,
        public readonly array $connectUrls = [],
        public readonly array $wsConnectUrls = [],
        public readonly bool $ldm = false,
        public readonly ?string $gitCommit = null,
        public readonly bool $jetstream = false,
        public readonly ?string $ip = null,
        public readonly ?string $clientIp = null,
        public readonly ?string $nonce = null,
        public readonly ?string $cluster = null,
        public readonly ?string $domain = null,
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromJson(string $json): self
    {
        try {
            /**
             * @var array{
             *     server_id: non-empty-string,
             *     server_name: non-empty-string,
             *     version: non-empty-string,
             *     go: non-empty-string,
             *     host: non-empty-string,
             *     port: int<0, max>,
             *     max_payload: int<0, max>,
             *     proto: int<0, max>,
             *     client_id?: positive-int|string,
             *     headers: bool,
             *     auth_required?: bool,
             *     tls_required?: bool,
             *     tls_verify?: bool,
             *     tls_available?: bool,
             *     connect_urls?: string[],
             *     ws_connect_urls?: string[],
             *     ldm?: bool,
             *     git_commit?: string,
             *     jetstream?: bool,
             *     ip?: string,
             *     client_ip?: string,
             *     nonce?: string,
             *     cluster?: string,
             *     domain?: string,
             * } $payload
             */
            $payload = json_decode($json, true, flags: \JSON_THROW_ON_ERROR);

            return new self(
                $payload['server_id'],
                $payload['server_name'],
                $payload['version'],
                $payload['go'],
                $payload['host'],
                $payload['port'],
                $payload['headers'],
                $payload['max_payload'],
                $payload['proto'],
                $payload['client_id'] ?? null,
                $payload['auth_required'] ?? false,
                $payload['tls_required'] ?? false,
                $payload['tls_verify'] ?? false,
                $payload['tls_available'] ?? false,
                $payload['connect_urls'] ?? [],
                $payload['ws_connect_urls'] ?? [],
                $payload['ldm'] ?? false,
                $payload['git_commit'] ?? null,
                $payload['jetstream'] ?? false,
                $payload['ip'] ?? null,
                $payload['client_ip'] ?? null,
                $payload['nonce'] ?? null,
                $payload['cluster'] ?? null,
                $payload['domain'] ?? null,
            );
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Server replied with invalid json.', previous: $e);
        }
    }
}
