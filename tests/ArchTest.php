<?php

declare(strict_types=1);

arch('de SDK kent de Hub niet')
    ->expect('Emeq\MistralApi')
    ->not->toUse(['App', 'Illuminate\Database\Eloquent']);

arch('geen debug-restanten')
    ->expect(['dd', 'dump', 'var_dump', 'ray'])
    ->not->toBeUsed();

arch('alles is strict')
    ->expect('Emeq\MistralApi')
    ->toUseStrictTypes();
