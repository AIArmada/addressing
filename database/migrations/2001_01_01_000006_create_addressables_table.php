<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = commerce_json_column_type('addressing', 'json');
        $tableName = config('addressing.tables.addressables', 'addressables');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->index();
            $table->uuidMorphs('addressable');
            $table->string('type')->default('primary')->index();
            $table->string('label')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestampTz('valid_from')->nullable();
            $table->timestampTz('valid_until')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->index(['addressable_type', 'addressable_id', 'type']);
            $table->index(
                ['addressable_type', 'addressable_id', 'is_primary'],
                'addrbl_type_id_primary_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.addressables', 'addressables'));
    }
};
