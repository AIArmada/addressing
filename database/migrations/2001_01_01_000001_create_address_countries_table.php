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
        $tableName = config('addressing.tables.countries', 'countries');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->nullable()->index();
            $table->string('numeric_code', 3)->nullable()->index();
            $table->string('entity_type')->default('country')->index();
            $table->boolean('is_independent')->nullable()->index();
            $table->string('name');
            $table->string('official_name')->nullable();
            $table->string('common_name')->nullable();
            $table->string('native_name')->nullable();
            $table->string('emoji')->nullable();
            $table->string('phone_code')->nullable();
            $table->{$jsonColumnType}('calling_codes')->nullable();
            $table->string('capital')->nullable();
            $table->decimal('capital_latitude', 10, 7)->nullable();
            $table->decimal('capital_longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('region')->nullable()->index();
            $table->string('subregion')->nullable()->index();
            $table->{$jsonColumnType}('currency_codes')->nullable();
            $table->string('default_currency_code', 3)->nullable();
            $table->{$jsonColumnType}('language_codes')->nullable();
            $table->{$jsonColumnType}('timezones')->nullable();
            $table->{$jsonColumnType}('top_level_domains')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.countries', 'countries'));
    }
};
