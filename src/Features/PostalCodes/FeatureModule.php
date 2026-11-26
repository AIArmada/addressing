<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes;

use AIArmada\Addressing\Contracts\PostalCodeAdminImporter;
use AIArmada\Addressing\Contracts\PostalCodeImporter;
use AIArmada\Addressing\Contracts\PostalCodeSeeder;
use AIArmada\Addressing\Contracts\PostalCodeWriter;
use AIArmada\Addressing\Contracts\SingaporePostalCodeResolver;
use AIArmada\Addressing\Features\PostalCodes\Actions\ClearAreaCoverageAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\FindPostalAreaCandidatesAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\ImportPostalCodeAdminDataAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\ImportPostalCodesAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\ResolveSingaporePostalCodesAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\SavePostalCodeAction;
use AIArmada\Addressing\Features\PostalCodes\Actions\SeedPostalCodesAction;
use AIArmada\Addressing\Features\PostalCodes\Api\AreaCoverageMaintenance;
use AIArmada\Addressing\Features\PostalCodes\Api\PostalAreaLookup;
use AIArmada\Addressing\Features\PostalCodes\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Features\PostalCodes\Models\PostalCode;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->instance('addressing.model-default.postal_code', PostalCode::class);
        $app->instance('addressing.model-default.area_postal_code', AddressAreaPostalCode::class);
        $app->singleton(PostalAreaLookup::class, FindPostalAreaCandidatesAction::class);
        $app->singleton(AreaCoverageMaintenance::class, ClearAreaCoverageAction::class);
        $app->singleton(PostalCodeSeeder::class, SeedPostalCodesAction::class);
        $app->singleton(PostalCodeWriter::class, SavePostalCodeAction::class);
        $app->singleton(PostalCodeAdminImporter::class, ImportPostalCodeAdminDataAction::class);
        $app->singleton(PostalCodeImporter::class, ImportPostalCodesAction::class);
        $app->singleton(SingaporePostalCodeResolver::class, ResolveSingaporePostalCodesAction::class);
    }

    public function boot(Application $app): void {}
}
