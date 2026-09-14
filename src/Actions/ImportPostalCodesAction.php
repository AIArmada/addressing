<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\PostalCodeSource;
use AIArmada\Addressing\Data\ImportPostalCodeFailureData;
use AIArmada\Addressing\Data\ImportPostalCodesResultData;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\PostalCode;
use Illuminate\Support\Facades\DB;

final class ImportPostalCodesAction
{
    public function execute(PostalCodeSource $source): ImportPostalCodesResultData
    {
        $countryCodes = AddressCountry::query()
            ->pluck('iso2')
            ->map(static fn ($iso2): string => mb_strtoupper((string) $iso2))
            ->flip()
            ->all();

        return DB::transaction(function () use ($source, $countryCodes): ImportPostalCodesResultData {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failures = [];
            $areasByKey = [];

            foreach ($source->postalCodes() as $item) {
                $countryCode = mb_strtoupper(mb_trim($item->countryCode));
                $code = mb_trim($item->code);

                if ($item->source === '' || $item->sourceId === '') {
                    $failures[] = new ImportPostalCodeFailureData($item->sourceId, 'Missing required field: source or sourceId', $code ?: null);

                    continue;
                }

                if (mb_strlen($countryCode) !== 2 || ! isset($countryCodes[$countryCode])) {
                    $failures[] = new ImportPostalCodeFailureData($item->sourceId, "Country not found for countryCode: {$countryCode}", $code ?: null);

                    continue;
                }

                if ($code === '') {
                    $failures[] = new ImportPostalCodeFailureData($item->sourceId, 'Missing required field: code', null);

                    continue;
                }

                $area = null;

                if ($item->areaSourceId !== null) {
                    $areaKey = $countryCode . "\0" . ($item->areaSource ?? $source->key()) . "\0" . $item->areaSourceId;

                    if (! array_key_exists($areaKey, $areasByKey)) {
                        $areasByKey[$areaKey] = AddressArea::query()
                            ->where('country_code', $countryCode)
                            ->where('source', $item->areaSource ?? $source->key())
                            ->where('source_id', $item->areaSourceId)
                            ->first();
                    }

                    $area = $areasByKey[$areaKey];

                    if (! $area instanceof AddressArea) {
                        $failures[] = new ImportPostalCodeFailureData($item->sourceId, "Area not found for areaSourceId: {$item->areaSourceId}", $code);

                        continue;
                    }
                }

                $metadata = array_merge($item->metadata, [
                    'source' => $item->source,
                    'source_id' => $item->sourceId,
                ]);

                $postalCode = PostalCode::query()->updateOrCreate(
                    ['country_code' => $countryCode, 'code' => $code],
                    ['is_active' => true, 'metadata' => $metadata],
                );

                $existingCoverage = AddressAreaPostalCode::query()
                    ->where('source', $item->source)
                    ->where('source_id', $item->sourceId)
                    ->whereHas('postalCode', fn ($query) => $query->where('country_code', $countryCode))
                    ->get(['address_area_id', 'postal_code_id', 'relationship_type', 'is_primary'])
                    ->map(static fn (AddressAreaPostalCode $coverage): string => implode('|', [
                        $coverage->address_area_id,
                        $coverage->postal_code_id,
                        $coverage->relationship_type,
                        (int) $coverage->is_primary,
                    ]))
                    ->sort()
                    ->values()
                    ->all();

                AddressAreaPostalCode::query()
                    ->where('source', $item->source)
                    ->where('source_id', $item->sourceId)
                    ->whereHas('postalCode', fn ($query) => $query->where('country_code', $countryCode))
                    ->delete();

                if ($area instanceof AddressArea) {
                    AddressAreaPostalCode::query()->updateOrCreate(
                        [
                            'address_area_id' => $area->getKey(),
                            'postal_code_id' => $postalCode->getKey(),
                            'relationship_type' => $item->relationshipType,
                            'source' => $item->source,
                        ],
                        [
                            'source_id' => $item->sourceId,
                            'is_primary' => $item->isPrimary,
                        ],
                    );
                }

                $newCoverage = $area instanceof AddressArea
                    ? [implode('|', [
                        $area->getKey(),
                        $postalCode->getKey(),
                        $item->relationshipType,
                        (int) $item->isPrimary,
                    ])]
                    : [];

                $coverageChanged = $existingCoverage !== $newCoverage;

                if ($postalCode->wasRecentlyCreated) {
                    $created++;
                } elseif ($coverageChanged || $postalCode->wasChanged()) {
                    $updated++;
                } else {
                    $skipped++;
                }
            }

            return new ImportPostalCodesResultData($created, $updated, $skipped, $failures);
        });
    }
}
