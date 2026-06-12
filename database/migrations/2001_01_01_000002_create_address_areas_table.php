<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = config('addressing.database.json_column_type', 'json');
        $tableName = config('addressing.tables.areas', 'address_areas');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->nullable()->index();
            $table->foreignUuid('parent_id')->nullable()->index();
            $table->string('country_code', 2)->index();
            $table->string('type')->index();
            $table->unsignedSmallInteger('level')->nullable()->index();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('code')->nullable()->index();
            $table->string('slug')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('source')->index();
            $table->string('source_id')->index();
            $table->string('parent_source_id')->nullable()->index();
            $table->{$jsonColumnType}('source_payload')->nullable();
            $table->timestampTz('synced_at')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
            $table->index(['country_code', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.areas', 'address_areas'));
    }
};
