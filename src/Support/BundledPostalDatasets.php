<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

class BundledPostalDatasets
{
    /**
     * Discover bundled per-country postcode datasets.
     *
     * A dataset is a `{slug}-postal-codes.csv` + `{slug}-postal-code-areas.csv`
     * pair with at least one data row; the country code comes from the first
     * data row. Incomplete pairs are skipped: structural validity is enforced
     * by the PostalCodeCsvImport shard tests, not at discovery time.
     *
     * @return array<string, array{string, string, string}> Slug => [countryCode, codesPath, linksPath].
     */
    public static function datasets(?string $directory = null): array
    {
        $dir = $directory ?? __DIR__ . '/../../resources/geography';
        $datasets = [];

        foreach (glob($dir . '/*-postal-codes.csv') ?: [] as $codesPath) {
            $slug = basename((string) $codesPath, '-postal-codes.csv');
            $linksPath = $dir . '/' . $slug . '-postal-code-areas.csv';

            if (! is_file($linksPath)) {
                continue;
            }

            $handle = fopen((string) $codesPath, 'r');
            $header = $handle !== false ? fgetcsv($handle, escape: '\\') : false;
            $first = $handle !== false ? fgetcsv($handle, escape: '\\') : false;

            if ($handle !== false) {
                fclose($handle);
            }

            if ($header === false || $first === false) {
                continue;
            }

            $code = mb_strtoupper(mb_trim((string) ($first[0] ?? '')));

            if ($code === '') {
                continue;
            }

            $datasets[$slug] = [$code, (string) $codesPath, $linksPath];
        }

        ksort($datasets);

        return $datasets;
    }
}
