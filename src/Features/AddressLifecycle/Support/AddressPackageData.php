<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Support;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressAreaAssignment;
use AIArmada\Addressing\Geography\Malaysia\MalaysiaAddressComponents;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Builds the canonical AddressData value object used for formatted block and summary rendering.
 */
final class AddressPackageData
{
    /**
     * @return array{code: string, data: AddressData}|null
     */
    public static function for(?Address $address): ?array
    {
        if (! $address instanceof Address) {
            return null;
        }

        $code = self::countryCode($address);

        if ($code === null) {
            return null;
        }

        $attributes = $address->getAttributes();
        $components = array_key_exists('components', $attributes) ? $address->getAttribute('components') : null;
        $components = is_array($components) ? $components : [];

        if ($code === 'MY') {
            $assignmentIds = $address->relationLoaded('areaAssignments')
                ? null
                : AddressAssignments::forAddress($address);

            $subdivision = self::subdivision($address, $assignmentIds);

            $computed = MalaysiaAddressComponents::for(
                $subdivision['name'],
                $subdivision['type'],
                self::areaName($address, AddressAssignments::ADMINISTRATIVE_DISTRICT, $assignmentIds),
                self::areaName($address, AddressAssignments::POSTAL_LOCALITY, $assignmentIds),
                self::clean(self::raw($address, 'city')),
            );

            $components['locality'] = $computed['locality'];
            $components['postcode_partner'] = $computed['postcode_partner'];
        }

        $data = AddressData::from([
            'line1' => self::raw($address, 'line1'),
            'line2' => self::raw($address, 'line2'),
            'line3' => self::raw($address, 'line3'),
            'city' => self::raw($address, 'city'),
            'state' => self::stateName($address),
            'postcode' => self::raw($address, 'postcode'),
            'country' => self::textAttribute($address, 'country'),
            'countryCode' => $code,
            'components' => $components,
        ]);

        return ['code' => $code, 'data' => $data];
    }

    private static function countryCode(Address $address): ?string
    {
        $code = self::clean(self::raw($address, 'country_code'));

        if ($code !== null) {
            return mb_strtoupper($code);
        }

        if ($address->relationLoaded('country')) {
            $code = self::clean($address->getRelation('country')?->getAttribute('iso2'));

            if ($code !== null) {
                return mb_strtoupper($code);
            }
        }

        $countryId = self::idString(self::raw($address, 'country_id'));

        if ($countryId === null) {
            return null;
        }

        $country = ModelResolver::countryClass()::query()->find($countryId);
        $code = $country instanceof (ModelResolver::countryClass()) ? self::clean($country->getAttribute('iso2')) : null;

        return $code === null ? null : mb_strtoupper($code);
    }

    private static function stateName(Address $address): ?string
    {
        $name = self::clean(self::raw($address, 'state'));

        if ($name !== null) {
            return $name;
        }

        $name = self::clean(self::raw($address, 'hierarchy_state_name'));

        if ($name !== null) {
            return $name;
        }

        if ($address->relationLoaded('state')) {
            return self::clean($address->getRelation('state')?->getAttribute('name'));
        }

        $stateId = self::idString(self::raw($address, 'state_id'));

        if ($stateId === null) {
            return null;
        }

        $state = ModelResolver::stateClass()::query()->find($stateId);

        return $state instanceof (ModelResolver::stateClass()) ? self::clean($state->getAttribute('name')) : null;
    }

    /**
     * @param  array<string, string>|null  $assignmentIds
     */
    private static function areaName(Address $address, string $role, ?array $assignmentIds = null): ?string
    {
        $assignment = self::primaryAssignment($address, $role);

        if ($assignment instanceof AddressAreaAssignment) {
            $area = $assignment->relationLoaded('area') ? $assignment->getRelation('area') : null;

            if ($area instanceof (ModelResolver::areaClass())) {
                return self::clean($area->getAttribute('name'));
            }

            $name = self::clean(self::raw($assignment, 'hierarchy_area_name'));

            if ($name !== null) {
                return $name;
            }

            return self::areaNameById(self::idString(self::raw($assignment, 'address_area_id')));
        }

        if ($address->relationLoaded('areaAssignments')) {
            return null;
        }

        $ids = $assignmentIds ?? AddressAssignments::forAddress($address);

        return self::areaNameById(self::idString($ids[$role] ?? null));
    }

    /**
     * @param  array<string, string>|null  $assignmentIds
     * @return array{name: ?string, type: ?string}
     */
    private static function subdivision(Address $address, ?array $assignmentIds = null): array
    {
        $role = AddressAssignments::ADMINISTRATIVE_SUBDIVISION;
        $assignment = self::primaryAssignment($address, $role);

        if ($assignment instanceof AddressAreaAssignment) {
            $area = $assignment->relationLoaded('area') ? $assignment->getRelation('area') : null;

            if ($area instanceof (ModelResolver::areaClass())) {
                return [
                    'name' => self::clean($area->getAttribute('name')),
                    'type' => self::clean($area->getAttribute('type')),
                ];
            }

            $name = self::clean(self::raw($assignment, 'hierarchy_area_name'));
            $type = self::clean(self::raw($assignment, 'hierarchy_area_type'));

            if ($type !== null) {
                return ['name' => $name, 'type' => $type];
            }

            $area = self::findArea(self::idString(self::raw($assignment, 'address_area_id')));

            return [
                'name' => $name ?? self::clean($area?->getAttribute('name')),
                'type' => self::clean($area?->getAttribute('type')),
            ];
        }

        if ($address->relationLoaded('areaAssignments')) {
            return ['name' => null, 'type' => null];
        }

        $ids = $assignmentIds ?? AddressAssignments::forAddress($address);
        $area = self::findArea(self::idString($ids[$role] ?? null));

        return [
            'name' => self::clean($area?->getAttribute('name')),
            'type' => self::clean($area?->getAttribute('type')),
        ];
    }

    private static function primaryAssignment(Address $address, string $role): ?AddressAreaAssignment
    {
        if (! $address->relationLoaded('areaAssignments')) {
            return null;
        }

        $assignment = $address->getRelation('areaAssignments')
            ->where('role', $role)
            ->where('is_primary', true)
            ->first();

        return $assignment instanceof AddressAreaAssignment ? $assignment : null;
    }

    private static function areaNameById(?string $id): ?string
    {
        return self::clean(self::findArea($id)?->getAttribute('name'));
    }

    private static function findArea(?string $id): ?Model
    {
        if ($id === null) {
            return null;
        }

        $area = ModelResolver::areaClass()::query()->find($id);

        return $area instanceof (ModelResolver::areaClass()) ? $area : null;
    }

    private static function idString(mixed $id): ?string
    {
        if (is_int($id)) {
            return (string) $id;
        }

        if (! is_string($id) || mb_trim($id) === '') {
            return null;
        }

        return $id;
    }

    private static function textAttribute(Address $address, string $attribute): ?string
    {
        return self::clean(self::raw($address, $attribute));
    }

    private static function raw(Address | AddressAreaAssignment $model, string $key): mixed
    {
        $attributes = $model->getAttributes();

        return $attributes[$key] ?? null;
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
