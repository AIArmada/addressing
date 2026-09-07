<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTables = [];

        foreach ($this->instanceTables() as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'owner_type')
                || ! Schema::hasColumn($tableName, 'owner_id')) {
                throw new RuntimeException(sprintf(
                    'Addressing owner cutover cannot run because [%s] is missing owner columns.',
                    $tableName,
                ));
            }

            $hasLegacyRows = DB::table($tableName)
                ->where(function (Builder $query): void {
                    $query->whereNull('owner_type')
                        ->orWhereNull('owner_id');
                })
                ->exists();

            if ($hasLegacyRows) {
                $legacyTables[] = $tableName;
            }
        }

        if ($legacyTables !== []) {
            throw new RuntimeException(sprintf(
                'Addressing owner cutover blocked: [%s] contain ownerless or partially-owned rows. '
                . 'This release has no backfill or legacy compatibility; remove those rows before rerunning migrations.',
                implode('], [', $legacyTables),
            ));
        }
    }

    /**
     * @return list<string>
     */
    private function instanceTables(): array
    {
        return [
            (string) config('addressing.tables.addresses', 'addresses'),
            (string) config('addressing.tables.addressables', 'addressables'),
            (string) config('addressing.tables.snapshots', 'address_snapshots'),
        ];
    }
};
