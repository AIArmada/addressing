<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaName;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use AIArmada\Addressing\Support\AddressAreaStateBridge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class GooglePlaceGeographyMatcher
{
    public function resolveCountry(GooglePlaceAddressComponents $components): ?AddressCountry
    {
        $countryComponent = $components->component(['country']);

        if (! is_array($countryComponent)) {
            return null;
        }

        $shortCode = $this->stringValue($countryComponent['shortText'] ?? null);

        if (is_string($shortCode) && mb_strlen($shortCode) === 2) {
            $country = AddressCountry::query()
                ->where('iso2', Str::upper($shortCode))
                ->first();

            if ($country instanceof AddressCountry) {
                return $country;
            }
        }

        $countryName = $this->firstFilled([
            $countryComponent['longText'] ?? null,
            $countryComponent['shortText'] ?? null,
        ]);

        if (! filled($countryName)) {
            return null;
        }

        /** @var Collection<int, AddressCountry> $matches */
        $matches = AddressCountry::query()
            ->get()
            ->filter(fn (AddressCountry $country): bool => $this->normalizeLocationName((string) $country->name) === $this->normalizeLocationName($countryName))
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    public function resolveCountryCode(?AddressCountry $country, ?string $countryId): ?string
    {
        $iso2 = $country instanceof AddressCountry ? $country->iso2 : null;

        if (is_string($iso2) && $iso2 !== '') {
            return mb_strtoupper($iso2);
        }

        if ($countryId === null) {
            return null;
        }

        $stored = AddressCountry::query()->whereKey($countryId)->value('iso2');

        return is_string($stored) && $stored !== '' ? mb_strtoupper($stored) : null;
    }

    /**
     * @param  list<string>|null  $types
     * @param  list<int>|null  $levels
     */
    public function resolveArea(
        ?string $name,
        ?string $countryId,
        ?string $parentId,
        ?array $types,
        ?array $levels = null,
        ?string $countryCode = null,
    ): ?AddressArea {
        if (! filled($name)) {
            return null;
        }

        $normalized = $this->normalizeLocationName($name, $countryCode);

        /** @var Collection<int, AddressArea> $matches */
        $matches = $this->scopedAreaQuery($countryId, $parentId, $types, $levels)
            ->get()
            ->filter(fn (AddressArea $area): bool => $this->normalizeLocationName($area->name, $countryCode) === $normalized)
            ->values();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        if ($matches->count() !== 0 || $normalized === '') {
            return null;
        }

        $areaIds = $this->scopedAreaQuery($countryId, $parentId, $types, $levels)->pluck('id')->all();

        if ($areaIds === []) {
            return null;
        }

        $matched = AddressAreaName::query()
            ->whereIn('address_area_id', $areaIds)
            ->get()
            ->filter(function (AddressAreaName $alias) use ($normalized, $countryCode): bool {
                $aliasName = $alias->getAttribute('name');

                return is_string($aliasName)
                    && $this->normalizeLocationName($aliasName, $countryCode) === $normalized;
            })
            ->map(static fn (AddressAreaName $alias): string => (string) $alias->getAttribute('address_area_id'))
            ->unique()
            ->values();

        if ($matched->count() !== 1) {
            return null;
        }

        $area = AddressArea::query()->find($matched->first());

        return $area instanceof AddressArea ? $area : null;
    }

    /**
     * @param  list<string>  $types
     * @param  list<int>  $levels
     */
    public function resolveAreaFromPlaceName(
        ?string $placeName,
        ?string $countryId,
        string $parentId,
        ?string $countryCode,
        array $types,
        array $levels,
    ): ?AddressArea {
        if ($countryId === null || ! filled($placeName)) {
            return null;
        }

        $areas = $this->scopedAreaQuery(
            $countryId,
            $parentId,
            $types,
            $levels !== [] ? $levels : null,
        )->get();

        if ($areas->isEmpty()) {
            return null;
        }

        $areaIds = $areas->map(static fn (AddressArea $area): string => (string) $area->getKey())->all();
        $matchedIds = $areas
            ->filter(fn (AddressArea $area): bool => $this->placeNameContainsAreaName($placeName, $area->name, $countryCode))
            ->map(static fn (AddressArea $area): string => (string) $area->getKey())
            ->merge(AddressAreaName::query()
                ->whereIn('address_area_id', $areaIds)
                ->get()
                ->filter(function (AddressAreaName $alias) use ($placeName, $countryCode): bool {
                    $aliasName = $alias->getAttribute('name');

                    return is_string($aliasName)
                        && $this->placeNameContainsAreaName($placeName, $aliasName, $countryCode);
                })
                ->map(static fn (AddressAreaName $alias): string => (string) $alias->getAttribute('address_area_id')))
            ->unique()
            ->values();

        if ($matchedIds->count() !== 1) {
            return null;
        }

        $matchedId = $matchedIds->first();
        $area = $areas->first(static fn (AddressArea $area): bool => (string) $area->getKey() === $matchedId);

        return $area instanceof AddressArea ? $area : null;
    }

    /**
     * @param  list<string>|null  $types
     * @param  list<int>|null  $levels
     * @return Builder<AddressArea>
     */
    public function scopedAreaQuery(?string $countryId, ?string $parentId, ?array $types, ?array $levels): Builder
    {
        $query = AddressArea::query();

        if ($countryId !== null) {
            $query->where('country_id', $countryId);
        }

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }

        if ($types !== null && $types !== []) {
            $query->whereIn('type', $types);
        }

        if ($levels !== null && $levels !== []) {
            $query->whereIn('level', $levels);
        }

        return $query;
    }

    public function resolveStateId(?string $stateName, ?string $countryId, ?AddressArea $areaTreeRoot, ?string $countryCode = null): ?string
    {
        if ($areaTreeRoot instanceof AddressArea) {
            $bridged = AddressAreaStateBridge::stateIdForArea($areaTreeRoot);

            if ($bridged !== null) {
                return $bridged;
            }
        }

        if (! filled($stateName) || $countryId === null) {
            return null;
        }

        /** @var Collection<int, State> $matches */
        $matches = State::query()
            ->where('country_id', $countryId)
            ->get()
            ->filter(function (State $state) use ($stateName, $countryCode): bool {
                $normalized = $this->normalizeLocationName($stateName, $countryCode);

                return $this->normalizeLocationName($state->name, $countryCode) === $normalized;
            })
            ->values();

        return $matches->count() === 1 ? (string) $matches->first()->getKey() : null;
    }

    public function resolveCityId(?string $cityName, ?string $stateId, ?string $countryId, ?string $countryCode = null): ?string
    {
        if (! filled($cityName)) {
            return null;
        }

        $query = City::query();

        if ($stateId !== null) {
            $query->where('state_id', $stateId);
        } elseif ($countryId !== null) {
            $query->where('country_id', $countryId);
        } else {
            return null;
        }

        /** @var Collection<int, City> $matches */
        $matches = $query
            ->get()
            ->filter(fn (City $city): bool => $this->normalizeLocationName($city->name, $countryCode) === $this->normalizeLocationName($cityName, $countryCode))
            ->values();

        return $matches->count() === 1 ? (string) $matches->first()->getKey() : null;
    }

    private function placeNameContainsAreaName(?string $placeName, ?string $areaName, ?string $countryCode): bool
    {
        $normalizedPlaceName = $this->normalizeLocationName($placeName, $countryCode);
        $normalizedAreaName = $this->normalizeLocationName($areaName, $countryCode);

        if ($normalizedPlaceName === '' || $normalizedAreaName === '') {
            return false;
        }

        return str_contains(' ' . $normalizedPlaceName . ' ', ' ' . $normalizedAreaName . ' ');
    }

    public function normalizeLocationName(?string $value, ?string $countryCode = null): string
    {
        if (! is_string($value)) {
            return '';
        }

        $text = Str::lower($value);

        if ($countryCode !== null) {
            $text = $this->stripCountryPrefixes($text, $countryCode);
        }

        return (string) Str::of($text)
            ->replaceMatches('/\b(?:district|daerah)\b/u', ' ')
            ->replaceMatches('/[^\pL\pN]+/u', ' ')
            ->squish();
    }

    private function stripCountryPrefixes(string $lowered, string $countryCode): string
    {
        $prefixes = GooglePlaceComponentMapper::namePrefixesToStrip($countryCode);

        if ($prefixes === []) {
            return $lowered;
        }

        $alternation = implode('|', array_map(
            static fn (string $prefix): string => preg_quote($prefix, '/'),
            $prefixes,
        ));

        $text = mb_ltrim($lowered);

        while (preg_match("/^(?:{$alternation})\\b\\s*/u", $text, $match) === 1) {
            if (! isset($match[0]) || $match[0] === '') {
                break;
            }

            $text = mb_substr($text, mb_strlen($match[0]));
        }

        return $text;
    }

    /**
     * @param  list<?string>  $values
     */
    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $value = $this->stringValue($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
