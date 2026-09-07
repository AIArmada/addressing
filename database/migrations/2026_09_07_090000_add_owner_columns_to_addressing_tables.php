<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->instanceTables() as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $ownerTypeExists = Schema::hasColumn($tableName, 'owner_type');
            $ownerIdExists = Schema::hasColumn($tableName, 'owner_id');

            if (! $ownerTypeExists && ! $ownerIdExists) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->nullableUuidMorphs('owner');
                });

                continue;
            }

            if (! $ownerTypeExists || ! $ownerIdExists) {
                Schema::table($tableName, function (Blueprint $table) use ($ownerTypeExists, $ownerIdExists): void {
                    if (! $ownerTypeExists) {
                        $table->string('owner_type')->nullable();
                    }

                    if (! $ownerIdExists) {
                        $table->uuid('owner_id')->nullable();
                    }
                });
            }

            if (! Schema::hasIndex($tableName, ['owner_type', 'owner_id'])) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->index(['owner_type', 'owner_id']);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->instanceTables() as $tableName) {
            if (! Schema::hasTable($tableName)
                || ! Schema::hasColumn($tableName, 'owner_type')
                || ! Schema::hasColumn($tableName, 'owner_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropMorphs('owner');
            });
        }
    }

    /**
     * @return list<string>
     */
    private function instanceTables(): array
    {
        return [
            AddressingTableResolver::resolve('addresses'),
            AddressingTableResolver::resolve('addressables'),
            AddressingTableResolver::resolve('snapshots'),
        ];
    }
};
