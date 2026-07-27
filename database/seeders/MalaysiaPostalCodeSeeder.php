<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Models\PostalCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;

class MalaysiaPostalCodeSeeder extends Seeder
{
    private const string POSTAL_CODES_CSV = '/../../resources/geography/malaysia-postal-codes.csv';

    private const string POSTAL_CODE_AREAS_CSV = '/../../resources/geography/malaysia-postal-code-areas.csv';

    private const string SOURCE = 'pos_malaysia_v1';

    /**
     * @var Collection<string, AddressArea>
     */
    private Collection $areasBySourceId;

    public function run(): void
    {
        $this->areasBySourceId = AddressArea::query()
            ->where('country_code', 'MY')
            ->where('is_active', true)
            ->get()
            ->keyBy('source_id');

        $this->importPostalCodes();
        $this->importAreaLinks();
    }

    private function importPostalCodes(): void
    {
        $path = $this->csvPath(self::POSTAL_CODES_CSV);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open postal codes CSV: {$path}");
        }

        fgetcsv($handle, escape: '\\');

        $created = 0;
        $updated = 0;

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $countryCode = mb_strtoupper(mb_trim((string) ($row[0] ?? '')));
            $code = mb_trim((string) ($row[1] ?? ''));

            if ($countryCode === '' || $code === '') {
                continue;
            }

            $postalCode = PostalCode::query()->updateOrCreate(
                ['country_code' => $countryCode, 'code' => $code],
                ['is_active' => true],
            );

            if ($postalCode->wasRecentlyCreated) {
                $created++;
            } elseif ($postalCode->wasChanged()) {
                $updated++;
            }
        }

        fclose($handle);

        if ($this->command !== null) {
            $this->command->info("Postal codes: {$created} created, {$updated} updated.");
        }
    }

    private function importAreaLinks(): void
    {
        $path = $this->csvPath(self::POSTAL_CODE_AREAS_CSV);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open postal code areas CSV: {$path}");
        }

        fgetcsv($handle, escape: '\\');

        $linked = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $postcode = mb_trim((string) ($row[0] ?? ''));
            $areaSourceId = mb_trim((string) ($row[1] ?? ''));
            $relationshipType = mb_trim((string) ($row[2] ?? '')) ?: 'served_by';
            $isPrimary = mb_trim((string) ($row[3] ?? '')) === 'true';

            if ($postcode === '' || $areaSourceId === '') {
                continue;
            }

            $postalCode = PostalCode::query()
                ->where('country_code', 'MY')
                ->where('code', $postcode)
                ->first();

            if (! $postalCode instanceof PostalCode) {
                $skipped++;

                continue;
            }

            $area = $this->areasBySourceId->get($areaSourceId);

            if (! $area instanceof AddressArea) {
                $skipped++;

                continue;
            }

            AddressAreaPostalCode::query()->updateOrCreate(
                [
                    'address_area_id' => $area->getKey(),
                    'postal_code_id' => $postalCode->getKey(),
                    'relationship_type' => $relationshipType,
                    'source' => self::SOURCE,
                ],
                ['is_primary' => $isPrimary],
            );

            $linked++;
        }

        fclose($handle);

        if ($this->command !== null) {
            $this->command->info("Area links: {$linked} linked, {$skipped} skipped.");
        }
    }

    private function csvPath(string $relativePath): string
    {
        $fullPath = __DIR__ . $relativePath;

        if (! File::exists($fullPath)) {
            throw new RuntimeException("CSV file not found: {$fullPath}");
        }

        return $fullPath;
    }
}
