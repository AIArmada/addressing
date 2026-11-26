<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Support;

use AIArmada\Addressing\Contracts\GoogleMapsInputNormalizer;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Canonical address form-state normalization shared across forms and persistence actions.
 */
final class AddressFormState
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeAddressFormState(array $data): array
    {
        if (! self::shouldNormalizeGoogleMaps($data)) {
            return array_merge($data, [
                'google_place_id' => $data['google_place_id'] ?? null,
                'google_feature_id' => $data['google_feature_id'] ?? null,
                'google_cid' => $data['google_cid'] ?? null,
                'google_entity_id' => $data['google_entity_id'] ?? null,
                'google_display_name' => $data['google_display_name'] ?? null,
                'lat' => $data['latitude'] ?? ($data['lat'] ?? null),
                'lng' => $data['longitude'] ?? ($data['lng'] ?? null),
                'google_resolution_source' => null,
                'google_resolution_status' => null,
                'google_resolution_fingerprint' => null,
                'google_resolution_message' => null,
            ]);
        }

        return array_merge($data, app(GoogleMapsInputNormalizer::class)->handle([
            'google_maps_url' => $data['google_maps_url'] ?? null,
            'google_place_id' => $data['google_place_id'] ?? null,
            'google_feature_id' => $data['google_feature_id'] ?? null,
            'google_cid' => $data['google_cid'] ?? null,
            'google_entity_id' => $data['google_entity_id'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'google_display_name' => $data['google_display_name'] ?? null,
            'lat' => $data['latitude'] ?? ($data['lat'] ?? null),
            'lng' => $data['longitude'] ?? ($data['lng'] ?? null),
            'google_maps_remote_lookup_enabled' => $data['google_maps_remote_lookup_enabled'] ?? null,
            'google_resolution_source' => $data['google_resolution_source'] ?? null,
            'google_resolution_status' => $data['google_resolution_status'] ?? null,
            'google_resolution_fingerprint' => $data['google_resolution_fingerprint'] ?? null,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareAddressPersistenceData(array $data): array
    {
        $normalized = self::normalizeAddressFormState($data);
        $normalized = self::normalizeRegionalSelections($normalized, $data);
        $payload = [];

        if (self::countrySelectionProvided($data)) {
            $payload['country_id'] = self::normalizeCountrySelection($normalized);
        }

        foreach (['state_id', 'city_id'] as $field) {
            if (array_key_exists($field, $data) || array_key_exists($field, $normalized)) {
                $payload[$field] = $normalized[$field] ?? null;
            }
        }

        if (array_key_exists('area_assignments', $data) || array_key_exists('area_assignments', $normalized)) {
            $payload['area_assignments'] = AddressAssignments::normalize((array) ($normalized['area_assignments'] ?? $data['area_assignments'] ?? []));
        }

        foreach (['line1', 'line2', 'postcode', 'state', 'city', 'waze_url'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $normalized[$field] ?? null;
            }
        }

        foreach (['formatted_address', 'components', 'metadata', 'navigation_links'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (self::hasGoogleMapsInput($data)) {
            $payload['latitude'] = $normalized['lat'] ?? null;
            $payload['longitude'] = $normalized['lng'] ?? null;
            $payload['google_maps_url'] = $normalized['google_maps_url'] ?? null;
            $payload['google_place_id'] = $normalized['google_place_id'] ?? null;
            $payload['google_feature_id'] = $normalized['google_feature_id'] ?? $data['google_feature_id'] ?? null;
            $payload['google_cid'] = $normalized['google_cid'] ?? $data['google_cid'] ?? null;
            $payload['google_entity_id'] = $normalized['google_entity_id'] ?? $data['google_entity_id'] ?? null;
        }

        return self::hydrateAddressAreaLabels($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function countrySelectionProvided(array $data): bool
    {
        if (! array_key_exists('country_id', $data)) {
            return false;
        }

        $value = $data['country_id'];

        return $value !== null && (! is_string($value) || mb_trim($value) !== '');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function normalizeCountrySelection(array $data): ?string
    {
        $countryId = self::normalizeLocationId($data['country_id'] ?? null);

        if ($countryId !== null && ModelResolver::countryClass()::query()->whereKey($countryId)->exists()) {
            return $countryId;
        }

        return null;
    }

    public static function normalizeLocationId(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        if ($trimmed === '') {
            return null;
        }

        return Str::isUuid($trimmed) ? $trimmed : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function shouldNormalizeGoogleMaps(array $data): bool
    {
        return filter_var($data['google_maps_normalization_enabled'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function hasGoogleMapsInput(array $data): bool
    {
        return array_any([
            'google_maps_url',
            'google_place_id',
            'google_feature_id',
            'google_cid',
            'google_entity_id',
            'google_display_name',
            'latitude',
            'longitude',
            'lat',
            'lng',
        ], static fn (string $field): bool => array_key_exists($field, $data));
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @param  array<string, mixed>  $original
     * @return array<string, mixed>
     */
    private static function normalizeRegionalSelections(array $normalized, array $original): array
    {
        $stateId = self::normalizeLocationId($normalized['state_id'] ?? $original['state_id'] ?? null);
        $cityId = self::normalizeLocationId($normalized['city_id'] ?? $original['city_id'] ?? null);
        $assignments = AddressAssignments::normalize((array) ($normalized['area_assignments'] ?? $original['area_assignments'] ?? []));

        $countryId = self::normalizeCountrySelection($normalized)
            ?? self::normalizeCountrySelection($original);

        if ($cityId !== null) {
            $cityQuery = ModelResolver::cityClass()::query()->whereKey($cityId);

            if ($stateId !== null) {
                $cityQuery->where('state_id', $stateId);
            }

            if ($countryId !== null) {
                $cityQuery->where('country_id', $countryId);
            }

            if (! $cityQuery->exists()) {
                throw ValidationException::withMessages([
                    'address.city_id' => __('The selected city does not belong to the selected state or country.'),
                ]);
            }
        }

        if ($assignments !== []) {
            $areaQuery = ModelResolver::areaClass()::query()
                ->whereIn('id', array_values($assignments));

            if ($countryId !== null) {
                $areaQuery->where('country_id', $countryId);
            }

            $availableIds = $areaQuery->pluck('id')->map(static fn (mixed $id): string => (string) $id)->flip();

            foreach ($assignments as $role => $areaId) {
                if (! $availableIds->has($areaId)) {
                    throw ValidationException::withMessages([
                        'address.area_assignments.' . $role => __('The selected area does not belong to the selected country.'),
                    ]);
                }
            }
        }

        if ($stateId !== null) {
            $stateQuery = ModelResolver::stateClass()::query()->whereKey($stateId);

            if ($countryId !== null) {
                $stateQuery->where('country_id', $countryId);
            }

            if (! $stateQuery->exists()) {
                throw ValidationException::withMessages([
                    'address.state_id' => __('The selected state does not belong to the selected country.'),
                ]);
            }

            $normalized['state_id'] = $stateId;
        }

        if ($cityId !== null) {
            $normalized['city_id'] = $cityId;
        }

        if (array_key_exists('area_assignments', $original) || array_key_exists('area_assignments', $normalized)) {
            $normalized['area_assignments'] = $assignments;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function hydrateAddressAreaLabels(array $payload): array
    {
        $country = isset($payload['country_id']) && is_string($payload['country_id'])
            ? ModelResolver::countryClass()::query()->find($payload['country_id'])
            : null;
        $state = isset($payload['state_id']) && is_string($payload['state_id'])
            ? ModelResolver::stateClass()::query()->find($payload['state_id'])
            : null;
        $city = isset($payload['city_id']) && is_string($payload['city_id'])
            ? ModelResolver::cityClass()::query()->find($payload['city_id'])
            : null;

        if ($country instanceof (ModelResolver::countryClass())) {
            $payload['country'] = $country->name;
            $payload['country_code'] = $country->iso2;
        }

        if ($state instanceof (ModelResolver::stateClass())) {
            $payload['state'] = $state->name;
        }

        if ($city instanceof (ModelResolver::cityClass())) {
            $payload['city'] = $city->name;
        } elseif (empty($payload['city'])) {
            $assignments = AddressAssignments::normalize((array) ($payload['area_assignments'] ?? []));
            $areas = $assignments === []
                ? collect()
                : ModelResolver::areaClass()::query()->whereIn('id', array_values($assignments))->get()->keyBy('id');

            foreach (array_reverse($assignments) as $areaId) {
                $area = $areas->get($areaId);
                if ($area instanceof (ModelResolver::areaClass())) {
                    $payload['city'] = $area->name;

                    break;
                }
            }
        }

        return $payload;
    }
}
