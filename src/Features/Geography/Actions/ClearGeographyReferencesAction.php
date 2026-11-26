<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceMaintenance;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceReader;
use AIArmada\Addressing\Features\Geography\Api\GeographyReferenceMaintenance;
use AIArmada\Addressing\Features\Geography\Support\AddressAreaStateBridge;
use AIArmada\Addressing\Features\PostalCodes\Api\AreaCoverageMaintenance;
use AIArmada\Addressing\Support\ModelResolver;
use DomainException;

final readonly class ClearGeographyReferencesAction implements GeographyReferenceMaintenance
{
    public function __construct(
        private AddressReferenceMaintenance $addresses,
        private AddressReferenceReader $references,
        private AreaCoverageMaintenance $coverage,
    ) {}

    public function clearState(string $stateId): void
    {
        $stateClass = ModelResolver::stateClass();

        (new $stateClass)->getConnection()->transaction(function () use ($stateId): void {
            ModelResolver::cityClass()::query()->where('state_id', $stateId)->update(['state_id' => null]);
            ModelResolver::areaStateLinkClass()::query()->where('state_id', $stateId)->delete();
            AddressAreaStateBridge::forgetState($stateId);

            $this->addresses->clearState($stateId);
        });
    }

    public function clearCity(string $cityId): void
    {
        $this->addresses->clearCity($cityId);
    }

    public function clearArea(string $areaId): void
    {
        $areaClass = ModelResolver::areaClass();

        (new $areaClass)->getConnection()->transaction(function () use ($areaClass, $areaId): void {
            $this->addresses->clearArea($areaId);
            $this->coverage->clearArea($areaId);
            ModelResolver::areaNameClass()::query()->where('address_area_id', $areaId)->delete();
            ModelResolver::areaRoleClass()::query()->where('address_area_id', $areaId)->delete();
            ModelResolver::areaStateLinkClass()::query()->where('address_area_id', $areaId)->delete();
            ModelResolver::areaRelationshipClass()::query()
                ->where('parent_address_area_id', $areaId)
                ->orWhere('child_address_area_id', $areaId)
                ->delete();
            $areaClass::query()->where('parent_id', $areaId)->update(['parent_id' => null]);
            AddressAreaStateBridge::forgetAll();
        });
    }

    public function clearCountry(string $countryId): void
    {
        $countryClass = ModelResolver::countryClass();

        (new $countryClass)->getConnection()->transaction(function () use ($countryClass, $countryId): void {
            $country = $countryClass::query()->whereKey($countryId)->lockForUpdate()->firstOrFail();

            $referenced = ModelResolver::stateClass()::query()->where('country_id', $countryId)->exists()
                || ModelResolver::cityClass()::query()->where('country_id', $countryId)->exists()
                || ModelResolver::areaClass()::query()->where('country_id', $countryId)->exists()
                || $this->references->hasCountryReferences($countryId);

            if ($referenced) {
                throw new DomainException('Countries with states, cities, areas, or addresses cannot be deleted.');
            }

            $country->currencies()->detach();
            $country->timezones()->detach();
        });
    }
}
