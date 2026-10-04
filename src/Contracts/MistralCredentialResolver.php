<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Contracts;

interface MistralCredentialResolver
{
    public function resolve(): string;
}
