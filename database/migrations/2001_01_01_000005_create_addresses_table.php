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
        $jsonColumnType = commerce_json_column_type('addressing', 'jsonb');
        $tableName = AddressingTableResolver::resolve('addresses');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->nullableUuidMorphs('owner');
            $table->foreignUuid('country_id')->nullable()->index();
            $table->foreignUuid('state_id')->nullable()->index();
            $table->foreignUuid('city_id')->nullable()->index();
            $table->string('label')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('line3')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('postcode')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable()->index();
            $table->text('formatted_address')->nullable();
            $table->{$jsonColumnType}('formatted_lines')->nullable();
            $table->{$jsonColumnType}('components')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('waze_url')->nullable();
            $table->string('google_place_id')->nullable()->index();
            $table->string('google_feature_id')->nullable()->index();
            $table->string('google_cid')->nullable()->index();
            $table->string('google_entity_id')->nullable()->index();
            $table->{$jsonColumnType}('navigation_links')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->index(['country_code', 'city'], 'addr_country_city_idx');
            $table->index(['country_code', 'postcode'], 'addr_country_postcode_idx');
        });
    }
};
