<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressFormatting;

use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Contracts\AddressNavigationLinks;
use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Contracts\AddressValidator;
use AIArmada\Addressing\Features\AddressFormatting\Actions\BuildAddressNavigationLinksAction;
use AIArmada\Addressing\Features\AddressFormatting\Actions\FormatAddressAction;
use AIArmada\Addressing\Features\AddressFormatting\Actions\NormalizeAddressDataAction;
use AIArmada\Addressing\Features\AddressFormatting\Actions\ValidateAddressAction;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->singleton(AddressFormatter::class, FormatAddressAction::class);
        $app->singleton(AddressNavigationLinks::class, BuildAddressNavigationLinksAction::class);
        $app->singleton(AddressNormalizer::class, NormalizeAddressDataAction::class);
        $app->singleton(AddressValidator::class, ValidateAddressAction::class);
    }

    public function boot(Application $app): void {}
}
