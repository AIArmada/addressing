<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\PostalCodeSource;
use AIArmada\Addressing\Data\PostalCodeData;
use Generator;
use Illuminate\Support\LazyCollection;
use RuntimeException;

/**
 * Reads a bundled per-country postcode dataset.
 *
 * The codes CSV lists postcodes (`country_code,code`); the links CSV maps
 * them to bundled areas (`postcode,area_source_id,relationship_type,is_primary`).
 * Codes without links are still imported so coverage stays complete.
 */
final class CsvPostalCodeSource implements PostalCodeSource
{
    public function __construct(
        private readonly string $countryCode,
        private readonly string $codesPath,
        private readonly string $linksPath,
        private readonly string $areaSource,
        private readonly ?string $linkSource = null,
    ) {}

    public function key(): string
    {
        return $this->linkSource ?? mb_strtolower($this->countryCode) . '_postal_v1';
    }

    /** @return LazyCollection<int, PostalCodeData> */
    public function postalCodes(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            $linked = [];

            foreach ($this->readCsv($this->linksPath) as $row) {
                $code = mb_strtoupper(mb_trim((string) ($row[0] ?? '')));
                $areaSourceId = mb_trim((string) ($row[1] ?? ''));

                if ($code === '' || $areaSourceId === '') {
                    continue;
                }

                $linked[$code] = true;

                yield new PostalCodeData(
                    source: $this->key(),
                    sourceId: $code . ':' . $areaSourceId,
                    countryCode: $this->countryCode,
                    code: $code,
                    areaSource: $this->areaSource,
                    areaSourceId: $areaSourceId,
                    relationshipType: mb_trim((string) ($row[2] ?? '')) ?: 'served_by',
                    isPrimary: mb_trim((string) ($row[3] ?? '')) === 'true',
                );
            }

            foreach ($this->readCsv($this->codesPath) as $row) {
                $code = mb_strtoupper(mb_trim((string) ($row[1] ?? '')));

                if ($code === '' || isset($linked[$code])) {
                    continue;
                }

                yield new PostalCodeData(
                    source: $this->key(),
                    sourceId: $code,
                    countryCode: $this->countryCode,
                    code: $code,
                );
            }
        });
    }

    /**
     * @return Generator<int, list<string>>
     */
    private function readCsv(string $path): Generator
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open postcode CSV: {$path}");
        }

        try {
            fgetcsv($handle, escape: '\\');

            while (($row = fgetcsv($handle, escape: '\\')) !== false) {
                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }
}
