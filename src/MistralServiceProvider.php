<?php

declare(strict_types=1);

namespace Emeq\MistralApi;

use Emeq\MistralApi\Contracts\MistralCredentialResolver;
use Illuminate\Support\ServiceProvider;

final class MistralServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Mistral::class, fn (): Mistral => new Mistral(
            $this->app->make(MistralCredentialResolver::class),
        ));
    }
}
