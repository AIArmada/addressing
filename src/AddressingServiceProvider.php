<?php

declare(strict_types=1);

namespace AIArmada\Addressing;

use AIArmada\Addressing\Actions\AssignAddressAreaAction;
use AIArmada\Addressing\Actions\BuildAddressNavigationLinksAction;
use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Actions\FormatAddressAction;
use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Actions\ImportPostalCodesAction;
use AIArmada\Addressing\Actions\NormalizeAddressDataAction;
use AIArmada\Addressing\Actions\SearchAddressAreasAction;
use AIArmada\Addressing\Actions\SeedAddressCitiesAction;
use AIArmada\Addressing\Actions\SeedAddressCountriesAction;
use AIArmada\Addressing\Actions\SeedAddressCountryReferencesAction;
use AIArmada\Addressing\Actions\SeedAddressStatesAction;
use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;
use AIArmada\Addressing\Actions\SyncAddressAreaAssignmentsAction;
use AIArmada\Addressing\Commands\ImportAddressAreasCommand;
use AIArmada\Addressing\Commands\ImportAddressAreasCsvCommand;
use AIArmada\Addressing\Commands\SeedAddressCitiesCommand;
use AIArmada\Addressing\Commands\SeedAddressCountriesCommand;
use AIArmada\Addressing\Commands\SeedAddressCountryReferencesCommand;
use AIArmada\Addressing\Commands\SeedAddressStatesCommand;
use AIArmada\Addressing\Commands\SeedCountryGeographiesCommand;
use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Support\CountryAddressFormatterResolver;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AddressingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('addressing')
            ->hasConfigFile()
            ->runsMigrations()
            ->discoversMigrations()
            ->hasCommands(
                SeedAddressCountriesCommand::class,
                SeedAddressCountryReferencesCommand::class,
                SeedAddressStatesCommand::class,
                SeedAddressCitiesCommand::class,
                SeedCountryGeographiesCommand::class,
                ImportAddressAreasCommand::class,
                ImportAddressAreasCsvCommand::class,
            );
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SeedAddressCountriesAction::class);
        $this->app->singleton(SeedAddressCountryReferencesAction::class);
        $this->app->singleton(SeedAddressStatesAction::class);
        $this->app->singleton(SeedAddressCitiesAction::class);
        $this->app->singleton(SeedCountryGeographiesAction::class);
        $this->app->singleton(SearchAddressAreasAction::class);
        $this->app->singleton(AssignAddressAreaAction::class);
        $this->app->singleton(SyncAddressAreaAssignmentsAction::class);
        $this->app->singleton(ImportPostalCodesAction::class);
        $this->app->singleton(CountryAddressProfileResolver::class);
        $this->app->singleton(CountryAddressFormatterResolver::class);
        $this->app->singleton(ImportAddressAreasAction::class);
        $this->app->singleton(CreateAddressSnapshotAction::class);
        $this->app->singleton(NormalizeAddressDataAction::class);
        $this->app->singleton(FormatAddressAction::class);
        $this->app->singleton(BuildAddressNavigationLinksAction::class);

        $this->app->bind(AddressNormalizer::class, NormalizeAddressDataAction::class);
        $this->app->bind(AddressFormatter::class, FormatAddressAction::class);
    }

    public function bootingPackage(): void
    {
        foreach (config('addressing.area_sources', []) as $source) {
            if (is_string($source)) {
                $this->app->singleton($source);
            }
        }
    }
}
