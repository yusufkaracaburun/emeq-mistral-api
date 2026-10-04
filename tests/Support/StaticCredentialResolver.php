<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Tests\Support;

use Emeq\MistralApi\Contracts\MistralCredentialResolver;

final readonly class StaticCredentialResolver implements MistralCredentialResolver
{
    public function __construct(private string $apiKey) {}

    public function resolve(): string
    {
        return $this->apiKey;
    }
}
