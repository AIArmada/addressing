<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography;

use AIArmada\Addressing\Contracts\AddressAreaAliasDeleter;
use AIArmada\Addressing\Contracts\AddressAreaAliasWriter;
use AIArmada\Addressing\Contracts\AddressAreaBoundaryWriter;
use AIArmada\Addressing\Contracts\AddressAreaDeletionGuard;
use AIArmada\Addressing\Contracts\AddressAreaImporter;
use AIArmada\Addressing\Contracts\AddressAreaSearch;
use AIArmada\Addressing\Contracts\AddressAreaWriter;
use AIArmada\Addressing\Contracts\AddressCountryDeletionGuard;
use AIArmada\Addressing\Contracts\AddressingDatasetSeeder;
use AIArmada\Addressing\Contracts\CityDatasetSeeder;
use AIArmada\Addressing\Contracts\CountryDatasetSeeder;
use AIArmada\Addressing\Contracts\CountryGeographySeeder;
use AIArmada\Addressing\Contracts\CountryLinkSeeder;
use AIArmada\Addressing\Contracts\CountryStateSeeder;
use AIArmada\Addressing\Contracts\GeographyReferenceEditor;
use AIArmada\Addressing\Contracts\StateDatasetSeeder;
use AIArmada\Addressing\Features\Geography\Actions\ClearGeographyReferencesAction;
use AIArmada\Addressing\Features\Geography\Actions\DeleteAddressAreaAliasAction;
use AIArmada\Addressing\Features\Geography\Actions\EditGeographyReferenceAction;
use AIArmada\Addressing\Features\Geography\Actions\GuardAddressAreaDeletionAction;
use AIArmada\Addressing\Features\Geography\Actions\GuardAddressCountryDeletionAction;
use AIArmada\Addressing\Features\Geography\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Features\Geography\Actions\MatchAreaAliasAction;
use AIArmada\Addressing\Features\Geography\Actions\PruneDuplicateManualAliasesAction;
use AIArmada\Addressing\Features\Geography\Actions\ReadAreaReferencesAction;
use AIArmada\Addressing\Features\Geography\Actions\ResolveAreaStateAction;
use AIArmada\Addressing\Features\Geography\Actions\SaveAddressAreaAction;
use AIArmada\Addressing\Features\Geography\Actions\SaveAddressAreaAliasAction;
use AIArmada\Addressing\Features\Geography\Actions\SearchAddressAreasAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedAddressCitiesAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedAddressCountriesAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedAddressCountryReferencesAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedAddressingAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedAddressStatesAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedCountryGeographiesAction;
use AIArmada\Addressing\Features\Geography\Actions\SeedCountryStatesAction;
use AIArmada\Addressing\Features\Geography\Actions\SetAddressAreaBoundaryAction;
use AIArmada\Addressing\Features\Geography\Actions\ValidateAreaAssignmentsAction;
use AIArmada\Addressing\Features\Geography\Api\AreaAliasMatcher;
use AIArmada\Addressing\Features\Geography\Api\AreaAssignmentValidator;
use AIArmada\Addressing\Features\Geography\Api\AreaReferenceReader;
use AIArmada\Addressing\Features\Geography\Api\AreaStateLookup;
use AIArmada\Addressing\Features\Geography\Api\GeographyReferenceMaintenance;
use AIArmada\Addressing\Features\Geography\Api\PlaceGeographyResolver;
use AIArmada\Addressing\Features\Geography\Api\ResolutionAliasMaintenance;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaName;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaRelationship;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaRole;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaStateLink;
use AIArmada\Addressing\Features\Geography\Models\AddressCountry;
use AIArmada\Addressing\Features\Geography\Models\City;
use AIArmada\Addressing\Features\Geography\Models\State;
use AIArmada\Addressing\Features\Geography\Support\GooglePlaceGeographyMatcher;
use AIArmada\Addressing\Features\Geography\Support\GooglePlaceGeographyResolver;
use AIArmada\Addressing\Features\Geography\Support\GooglePlaceResolutionGapLogger;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->instance('addressing.model-default.area', AddressArea::class);
        $app->instance('addressing.model-default.country', AddressCountry::class);
        $app->instance('addressing.model-default.city', City::class);
        $app->instance('addressing.model-default.state', State::class);
        $app->singleton(AreaStateLookup::class, ResolveAreaStateAction::class);
        $app->instance('addressing.model-default.area_name', AddressAreaName::class);
        $app->instance('addressing.model-default.area_relationship', AddressAreaRelationship::class);
        $app->instance('addressing.model-default.area_role', AddressAreaRole::class);
        $app->singleton(GooglePlaceGeographyMatcher::class);
        $app->singleton(GooglePlaceResolutionGapLogger::class);
        $app->singleton(PlaceGeographyResolver::class, GooglePlaceGeographyResolver::class);
        $app->singleton(AreaAliasMatcher::class, MatchAreaAliasAction::class);
        $app->singleton(AreaAssignmentValidator::class, ValidateAreaAssignmentsAction::class);
        $app->singleton(AreaReferenceReader::class, ReadAreaReferencesAction::class);
        $app->instance('addressing.model-default.area_state_link', AddressAreaStateLink::class);
        $app->singleton(GeographyReferenceMaintenance::class, ClearGeographyReferencesAction::class);
        $app->singleton(ResolutionAliasMaintenance::class, PruneDuplicateManualAliasesAction::class);
        $app->singleton(GeographyReferenceEditor::class, EditGeographyReferenceAction::class);
        $app->singleton(CityDatasetSeeder::class, SeedAddressCitiesAction::class);
        $app->singleton(CountryDatasetSeeder::class, SeedAddressCountriesAction::class);
        $app->singleton(CountryLinkSeeder::class, SeedAddressCountryReferencesAction::class);
        $app->singleton(StateDatasetSeeder::class, SeedAddressStatesAction::class);
        $app->singleton(AddressingDatasetSeeder::class, SeedAddressingAction::class);
        $app->singleton(AddressAreaImporter::class, ImportAddressAreasAction::class);
        $app->singleton(CountryGeographySeeder::class, SeedCountryGeographiesAction::class);
        $app->singleton(AddressAreaDeletionGuard::class, GuardAddressAreaDeletionAction::class);
        $app->singleton(AddressCountryDeletionGuard::class, GuardAddressCountryDeletionAction::class);
        $app->scoped(AddressAreaSearch::class, SearchAddressAreasAction::class);
        $app->singleton(CountryStateSeeder::class, SeedCountryStatesAction::class);
        $app->singleton(AddressAreaWriter::class, SaveAddressAreaAction::class);
        $app->singleton(AddressAreaAliasWriter::class, SaveAddressAreaAliasAction::class);
        $app->singleton(AddressAreaAliasDeleter::class, DeleteAddressAreaAliasAction::class);
        $app->singleton(AddressAreaBoundaryWriter::class, SetAddressAreaBoundaryAction::class);
    }

    public function boot(Application $app): void {}
}
