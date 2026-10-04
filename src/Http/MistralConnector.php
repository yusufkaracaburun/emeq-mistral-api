<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Http;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

class MistralConnector extends Connector
{
    public function __construct(
        #[\SensitiveParameter] private readonly string $apiKey,
        private readonly int $timeoutSeconds = 300,
        private readonly int $connectTimeoutSeconds = 10,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://api.eu.mistral.ai';
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new TokenAuthenticator($this->apiKey);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => $this->timeoutSeconds,
            'connect_timeout' => $this->connectTimeoutSeconds,
        ];
    }
}
