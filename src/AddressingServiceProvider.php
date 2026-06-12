<?php

declare(strict_types=1);

namespace AIArmada\Addressing;

use AIArmada\Addressing\Actions\BuildAddressNavigationLinksAction;
use AIArmada\Addressing\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Actions\FormatAddressAction;
use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Actions\NormalizeAddressDataAction;
use AIArmada\Addressing\Actions\SeedAddressCountriesAction;
use AIArmada\Addressing\Commands\ImportAddressAreasCommand;
use AIArmada\Addressing\Commands\ImportAddressAreasCsvCommand;
use AIArmada\Addressing\Commands\SeedAddressCountriesCommand;
use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Contracts\AddressNormalizer;
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
                ImportAddressAreasCommand::class,
                ImportAddressAreasCsvCommand::class,
            );
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(SeedAddressCountriesAction::class);
        $this->app->singleton(ImportAddressAreasAction::class);
        $this->app->singleton(CreateAddressSnapshotAction::class);
        $this->app->singleton(NormalizeAddressDataAction::class);
        $this->app->singleton(FormatAddressAction::class);
        $this->app->singleton(BuildAddressNavigationLinksAction::class);

        $this->app->bind(AddressNormalizer::class, NormalizeAddressDataAction::class);
        $this->app->bind(AddressFormatter::class, FormatAddressAction::class);
    }
}
