<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PlaceLookup;

use AIArmada\Addressing\Contracts\GoogleMapsInputNormalizer;
use AIArmada\Addressing\Contracts\GooglePlaceSelectionResolver;
use AIArmada\Addressing\Features\PlaceLookup\Actions\NormalizeGoogleMapsInputAction;
use AIArmada\Addressing\Features\PlaceLookup\Actions\ResolveGooglePlaceSelectionAction;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->singleton(GoogleMapsInputNormalizer::class, NormalizeGoogleMapsInputAction::class);
        $app->singleton(GooglePlaceSelectionResolver::class, ResolveGooglePlaceSelectionAction::class);
    }

    public function boot(Application $app): void {}
}
