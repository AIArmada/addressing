<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Support;

use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressAreaAssignment;
use AIArmada\Addressing\Support\AddressFormatRenderer;
use AIArmada\Addressing\Support\LocationLevelLabels;
use AIArmada\Addressing\Support\ModelResolver;
use InvalidArgumentException;

final class AddressHierarchyFormatter
{
    /**
     * @return list<string>
     */
    public static function parts(?Address $address): array
    {
        $parts = self::packageParts($address);

        if ($parts !== []) {
            return $parts;
        }

        $countryName = self::countryName($address);

        return $countryName === null ? [] : [$countryName];
    }

    /**
     * @return list<string>
     */
    public static function shortParts(?Address $address): array
    {
        return array_values(array_unique(array_slice(self::parts($address), -2)));
    }

    /**
     * @return array{street: ?string, locality: ?string, regional: ?string}
     */
    public static function displayLines(?Address $address): array
    {
        $empty = ['street' => null, 'locality' => null, 'regional' => null];

        if (! $address instanceof Address) {
            return $empty;
        }

        $render = AddressPackageData::for($address);

        if ($render === null) {
            return ['street' => self::streetLine($address), 'locality' => null, 'regional' => null];
        }

        $data = $render['data'];
        $streetValues = array_values(array_filter(
            [$data->line1, $data->line2, $data->line3],
            static fn (?string $line): bool => $line !== null && $line !== '',
        ));

        try {
            $block = AddressFormatRenderer::format($render['code'], $render['data']);
        } catch (InvalidArgumentException) {
            return [
                'street' => $streetValues === [] ? null : implode(', ', $streetValues),
                'locality' => null,
                'regional' => null,
            ];
        }

        $nonStreet = array_values(array_filter(
            explode("\n", $block),
            static fn (string $line): bool => ! in_array($line, $streetValues, true),
        ));

        try {
            $parts = AddressFormatRenderer::summaryParts($render['code'], $render['data']);
        } catch (InvalidArgumentException) {
            $parts = [];
        }

        $locality = null;

        if ($data->postcode !== null && $data->postcode !== '') {
            foreach ($nonStreet as $line) {
                if (str_contains($line, $data->postcode)) {
                    $locality = $line;

                    break;
                }
            }
        }

        $locality ??= $parts[0] ?? $nonStreet[0] ?? null;

        $regional = count($parts) > 1 ? implode(', ', array_slice($parts, 1)) : null;

        return [
            'street' => $streetValues === [] ? null : implode(', ', $streetValues),
            'locality' => $locality,
            'regional' => $regional,
        ];
    }

    public static function format(?Address $address, string $separator = ', '): string
    {
        return implode($separator, self::parts($address));
    }

    public static function formatShort(?Address $address, string $separator = ', '): string
    {
        return implode($separator, self::shortParts($address));
    }

    public static function formatBlock(?Address $address, string $separator = ', '): string
    {
        $render = AddressPackageData::for($address);

        if ($render === null) {
            return '';
        }

        try {
            $block = AddressFormatRenderer::format($render['code'], $render['data']);
        } catch (InvalidArgumentException) {
            return '';
        }

        return str_replace("\n", $separator, $block);
    }

    public static function roleAreaName(?Address $address, string $role): ?string
    {
        if (! $address instanceof Address) {
            return null;
        }

        return self::areaNamesForAssignments([self::assignmentForRole($address, $role)])[0] ?? null;
    }

    public static function subdivisionOrLocalityName(?Address $address): ?string
    {
        if (! $address instanceof Address) {
            return null;
        }

        $names = self::areaNamesForAssignments([
            self::assignmentForRole($address, AddressAssignments::ADMINISTRATIVE_SUBDIVISION),
            self::assignmentForRole($address, AddressAssignments::POSTAL_LOCALITY),
        ]);

        return $names[0] ?? $names[1] ?? null;
    }

    public static function roleAreaLabel(?Address $address, string $role, string $fallback): string
    {
        if (! $address instanceof Address) {
            return $fallback;
        }

        return LocationLevelLabels::label(
            $address->getAttribute('country_id') ?? $address->getAttribute('country_code'),
            $role,
            $fallback,
            $address->getAttribute('state_id'),
            AddressAssignments::forAddress($address),
        );
    }

    public static function subdivisionOrLocalityLabel(?Address $address, string $fallback = 'Subdivision / Locality'): string
    {
        if (self::roleAreaName($address, AddressAssignments::ADMINISTRATIVE_SUBDIVISION) !== null) {
            return self::roleAreaLabel($address, AddressAssignments::ADMINISTRATIVE_SUBDIVISION, $fallback);
        }

        return self::roleAreaLabel($address, AddressAssignments::POSTAL_LOCALITY, $fallback);
    }

    private static function assignmentForRole(Address $address, string $role): ?AddressAreaAssignment
    {
        $preloaded = $address->relationLoaded('areaAssignments');

        if (! $preloaded) {
            $address->load(['areaAssignments' => static fn ($query) => $query->with('area')]);
        }

        $assignments = $address->getRelation('areaAssignments');
        $assignment = $assignments->first(
            static fn (AddressAreaAssignment $candidate): bool => (string) $candidate->getAttribute('role') === $role,
        );

        if ($assignment instanceof AddressAreaAssignment || ! $preloaded) {
            return $assignment instanceof AddressAreaAssignment ? $assignment : null;
        }

        $found = $address->areaAssignments()
            ->with('area')
            ->where('role', $role)
            ->first();

        return $found instanceof AddressAreaAssignment ? $found : null;
    }

    /**
     * @param  list<AddressAreaAssignment|null>  $assignments
     * @return list<string|null>
     */
    private static function areaNamesForAssignments(array $assignments): array
    {
        $names = [];
        $missing = [];

        foreach ($assignments as $index => $assignment) {
            if (! $assignment instanceof AddressAreaAssignment) {
                $names[$index] = null;

                continue;
            }

            if ($assignment->relationLoaded('area')) {
                $names[$index] = self::clean($assignment->getRelation('area')?->getAttribute('name'));

                continue;
            }

            $missing[$index] = (string) $assignment->getAttribute('address_area_id');
        }

        if ($missing !== []) {
            $areaClass = ModelResolver::areaClass();
            $areaNames = $areaClass::query()
                ->whereKey(array_values(array_unique($missing)))
                ->pluck('name', 'id')
                ->all();

            foreach ($missing as $index => $areaId) {
                $name = $areaNames[$areaId] ?? null;
                $names[$index] = is_string($name) ? self::clean($name) : null;
            }
        }

        ksort($names);

        return array_values($names);
    }

    /**
     * @return list<string>
     */
    private static function packageParts(?Address $address): array
    {
        $render = AddressPackageData::for($address);

        if ($render === null) {
            return [];
        }

        try {
            return AddressFormatRenderer::summaryParts($render['code'], $render['data']);
        } catch (InvalidArgumentException) {
            return [];
        }
    }

    private static function streetLine(Address $address): ?string
    {
        $attributes = $address->getAttributes();
        $lines = [];

        foreach (['line1', 'line2', 'line3'] as $key) {
            $line = self::clean($attributes[$key] ?? null);

            if ($line !== null) {
                $lines[] = $line;
            }
        }

        return $lines === [] ? null : implode(', ', $lines);
    }

    private static function countryName(?Address $address): ?string
    {
        if (! $address instanceof Address) {
            return null;
        }

        $attributes = $address->getAttributes();
        $name = self::clean($attributes['country'] ?? null);

        if ($name !== null) {
            return $name;
        }

        if ($address->relationLoaded('country')) {
            $name = self::clean($address->getRelation('country')?->getAttribute('name'));

            if ($name !== null) {
                return $name;
            }
        }

        $countryId = $attributes['country_id'] ?? null;

        if (is_int($countryId)) {
            $countryId = (string) $countryId;
        }

        if (! is_string($countryId) || mb_trim($countryId) === '') {
            return null;
        }

        $country = ModelResolver::countryClass()::query()->find($countryId);

        return $country instanceof (ModelResolver::countryClass()) ? self::clean($country->getAttribute('name')) : null;
    }

    private static function clean(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
