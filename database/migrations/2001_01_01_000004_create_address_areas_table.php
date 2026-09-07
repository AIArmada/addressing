<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = commerce_json_column_type('addressing', 'jsonb');
        $tableName = AddressingTableResolver::resolve('areas');

        commerce_schema_create_if_missing($tableName, function (Blueprint $table) use ($jsonColumnType): void {
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
            $table->boolean('is_active')->default(true)->index();
            $table->{$jsonColumnType}('source_payload')->nullable();
            $table->timestampTz('synced_at')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
            $table->index(['country_code', 'type', 'name']);
        });
    }
};
