<?php

declare(strict_types=1);

namespace AIArmada\Addressing;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\GoogleAddressReferenceSource;
use AIArmada\Addressing\Contracts\GoogleMapsLookup;
use AIArmada\Addressing\Contracts\GooglePlaceDetailsLookup;
use AIArmada\Addressing\Contracts\GooglePlacePhotoLookup;
use AIArmada\Addressing\Contracts\PostalAddressLookup;
use AIArmada\Addressing\Drivers\GoogleAddressReference\GoogleAddressReferenceClient;
use AIArmada\Addressing\Drivers\GooglePlaces\GoogleMapsRemoteLookup;
use AIArmada\Addressing\Drivers\GooglePlaces\GooglePlaceDetailsClient;
use AIArmada\Addressing\Drivers\GooglePlaces\GooglePlacePhotoPreviewsClient;
use AIArmada\Addressing\Drivers\OneMap\OneMapClient;
use AIArmada\Addressing\Features\Geography\EntryPoints\Commands\ImportAddressAreasCommand;
use AIArmada\Addressing\Features\Geography\EntryPoints\Commands\SeedCountryGeographiesCommand;
use AIArmada\Addressing\Features\PostalCodes\EntryPoints\Commands\SeedPostalCodesCommand;
use AIArmada\Addressing\Features\ReferenceIntegrity\EntryPoints\Commands\CompareAddressingReferenceExportsCommand;
use AIArmada\Addressing\Features\ReferenceIntegrity\EntryPoints\Commands\DiffCldrSubdivisionsCommand;
use AIArmada\Addressing\Features\ReferenceIntegrity\EntryPoints\Commands\DiffGoogleAddressReferenceCommand;
use AIArmada\Addressing\Features\ReferenceIntegrity\EntryPoints\Commands\ExportAddressingReferencesCommand;
use AIArmada\Addressing\Features\ResolutionGaps\EntryPoints\Commands\ExportResolutionGapAliasesCommand;
use AIArmada\Addressing\Features\ResolutionGaps\EntryPoints\Commands\ReportResolutionGapsCommand;
use AIArmada\Addressing\GooglePlaces\GoogleMapsUrlParser;
use AIArmada\Addressing\Platform\ExtensionRegistry;
use AIArmada\Addressing\Platform\FeatureCatalog;
use AIArmada\Addressing\Platform\FeatureModule;
use AIArmada\Addressing\Support\CountryAddressFormatterResolver;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use InvalidArgumentException;
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
                SeedCountryGeographiesCommand::class,
                SeedPostalCodesCommand::class,
                ImportAddressAreasCommand::class,
                ReportResolutionGapsCommand::class,
                ExportResolutionGapAliasesCommand::class,
                DiffGoogleAddressReferenceCommand::class,
                DiffCldrSubdivisionsCommand::class,
                ExportAddressingReferencesCommand::class,
                CompareAddressingReferenceExportsCommand::class,
            );
    }

    /** @var list<FeatureModule> */
    private array $modules = [];

    public function packageRegistered(): void
    {
        $catalog = FeatureCatalog::load(dirname(__DIR__), __NAMESPACE__);
        $this->app->singleton(ExtensionRegistry::class, fn ($app): ExtensionRegistry => new ExtensionRegistry($app, $catalog['extensions']));

        foreach (array_keys($catalog['features']) as $feature) {
            $module = $this->app->make(__NAMESPACE__ . '\\Features\\' . $feature . '\\FeatureModule');

            if (! $module instanceof FeatureModule) {
                throw new InvalidArgumentException('Invalid addressing feature module: ' . $feature);
            }

            $module->register($this->app);
            $this->modules[] = $module;
        }

        $this->app->singleton(CountryAddressProfileResolver::class);
        $this->app->singleton(CountryAddressFormatterResolver::class);
        $this->app->singleton(PostalAddressLookup::class, OneMapClient::class);
        $this->app->singleton(GoogleMapsUrlParser::class);
        $this->app->singleton(GooglePlaceDetailsLookup::class, GooglePlaceDetailsClient::class);
        $this->app->singleton(GooglePlacePhotoLookup::class, GooglePlacePhotoPreviewsClient::class);
        $this->app->singleton(GoogleAddressReferenceSource::class, GoogleAddressReferenceClient::class);
        $this->app->singleton(GoogleMapsLookup::class, GoogleMapsRemoteLookup::class);

    }

    public function bootingPackage(): void
    {
        $this->normalizeDefaultCountryCode();
        $this->validateConfiguredClasses(
            config('addressing.geography.providers', []),
            CountryGeographyProvider::class,
            'geography provider',
        );
        $this->validateConfiguredClasses(
            config('addressing.formatters', []),
            CountryAddressFormatter::class,
            'address formatter',
        );

        foreach (config('addressing.area_sources', []) as $source) {
            if (is_string($source)) {
                $this->app->singleton($source);
            }
        }
    }

    public function packageBooted(): void
    {
        foreach ($this->modules as $module) {
            $module->boot($this->app);
        }
    }

    private function normalizeDefaultCountryCode(): void
    {
        $countryCode = config('addressing.defaults.country_code');

        if ($countryCode === null) {
            return;
        }

        if (! is_string($countryCode) || mb_trim($countryCode) === '') {
            config(['addressing.defaults.country_code' => null]);

            return;
        }

        config(['addressing.defaults.country_code' => mb_strtoupper(mb_trim($countryCode))]);
    }

    private function validateConfiguredClasses(mixed $classes, string $contract, string $label): void
    {
        if (! is_array($classes)) {
            throw new InvalidArgumentException(sprintf(
                'Addressing %s configuration must be an array of class strings.',
                $label,
            ));
        }

        foreach ($classes as $class) {
            if (! is_string($class) || ! class_exists($class) || ! is_a($class, $contract, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Addressing %s [%s] must implement [%s].',
                    $label,
                    is_string($class) ? $class : get_debug_type($class),
                    $contract,
                ));
            }
        }
    }
}
