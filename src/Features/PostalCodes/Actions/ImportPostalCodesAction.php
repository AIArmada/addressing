<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Actions;

use AIArmada\Addressing\Contracts\PostalCodeImporter;
use AIArmada\Addressing\Contracts\PostalCodeSource;
use AIArmada\Addressing\Data\ImportPostalCodeFailureData;
use AIArmada\Addressing\Data\ImportPostalCodesResultData;
use AIArmada\Addressing\Features\Geography\Api\AreaReferenceReader;
use AIArmada\Addressing\Features\PostalCodes\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class ImportPostalCodesAction implements PostalCodeImporter
{
    public function __construct(private AreaReferenceReader $references) {}

    public function execute(PostalCodeSource $source): ImportPostalCodesResultData
    {
        $countryCodes = array_fill_keys($this->references->countryCodes(), true);

        return ModelResolver::postalCodeClass()::query()->getConnection()->transaction(function () use ($source, $countryCodes): ImportPostalCodesResultData {
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

                if ($code === '' || mb_strlen($code) > 20) {
                    $failures[] = new ImportPostalCodeFailureData($item->sourceId, 'Postcode must contain between 1 and 20 characters', null);

                    continue;
                }

                $area = null;

                if ($item->areaSourceId !== null) {
                    $areaKey = $countryCode . "\0" . ($item->areaSource ?? $source->key()) . "\0" . $item->areaSourceId;

                    if (! array_key_exists($areaKey, $areasByKey)) {
                        $areasByKey[$areaKey] = $this->references->areaIdForSource($countryCode, $item->areaSource ?? $source->key(), $item->areaSourceId);
                    }

                    $area = $areasByKey[$areaKey];

                    if ($area === null) {
                        $failures[] = new ImportPostalCodeFailureData($item->sourceId, "Area not found for areaSourceId: {$item->areaSourceId}", $code);

                        continue;
                    }
                }

                $metadata = array_merge($item->metadata, [
                    'source' => $item->source,
                    'source_id' => $item->sourceId,
                ]);

                $postalCode = ModelResolver::postalCodeClass()::query()->updateOrCreate(
                    ['country_code' => $countryCode, 'code' => $code],
                    ['is_active' => true, 'metadata' => $metadata],
                );

                $existingCoverage = ModelResolver::areaPostalCodeClass()::query()
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

                ModelResolver::areaPostalCodeClass()::query()
                    ->where('source', $item->source)
                    ->where('source_id', $item->sourceId)
                    ->whereHas('postalCode', fn ($query) => $query->where('country_code', $countryCode))
                    ->delete();

                if (is_string($area)) {
                    ModelResolver::areaPostalCodeClass()::query()->updateOrCreate(
                        [
                            'address_area_id' => $area,
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

                $newCoverage = is_string($area)
                    ? [implode('|', [
                        $area,
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
