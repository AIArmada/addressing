<?php

declare(strict_types=1);

arch('strict types across package source')
    ->expect('AIArmada\Addressing')
    ->toUseStrictTypes();

arch('no debugging helpers, env(), or imperative sleep in package source')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'sleep', 'usleep', 'env'])
    ->each->not->toBeUsedIn('AIArmada\Addressing');

arch('package source never imports host application code')
    ->expect('AIArmada\Addressing')
    ->not->toUse('App');

arch('public Contracts and Data stay decoupled from Actions, Models, and Drivers')
    ->expect(['AIArmada\Addressing\Contracts', 'AIArmada\Addressing\Data'])
    ->not->toUse([
        'AIArmada\Addressing\Actions',
        'AIArmada\Addressing\Models',
        'AIArmada\Addressing\Drivers',
        'AIArmada\Addressing\Features',
        'Illuminate\Database\Eloquent',
        'Illuminate\Http',
    ]);
