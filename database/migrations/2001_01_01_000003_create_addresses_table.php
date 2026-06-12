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
        $tableName = config('addressing.tables.addresses', 'addresses');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->nullable()->index();
            $table->foreignUuid('admin_area_1_id')->nullable()->index();
            $table->foreignUuid('admin_area_2_id')->nullable()->index();
            $table->foreignUuid('admin_area_3_id')->nullable()->index();
            $table->foreignUuid('admin_area_4_id')->nullable()->index();
            $table->string('label')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('line3')->nullable();
            $table->string('building_name')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('floor')->nullable();
            $table->string('block')->nullable();
            $table->string('street_number')->nullable();
            $table->string('street_name')->nullable();
            $table->string('neighbourhood')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('postcode')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable()->index();
            $table->text('raw_address')->nullable();
            $table->text('formatted_address')->nullable();
            $table->{$jsonColumnType}('formatted_lines')->nullable();
            $table->{$jsonColumnType}('components')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geohash')->nullable()->index();
            $table->string('geo_precision')->nullable();
            $table->string('provider')->nullable()->index();
            $table->string('provider_place_id')->nullable()->index();
            $table->{$jsonColumnType}('provider_payload')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('waze_url')->nullable();
            $table->{$jsonColumnType}('navigation_links')->nullable();
            $table->string('validation_status')->default('unverified')->index();
            $table->timestampTz('validated_at')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.addresses', 'addresses'));
    }
};
