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
        $tableName = config('addressing.tables.snapshots', 'address_snapshots');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->nullable()->index();
            $table->uuidMorphs('snapshotable');
            $table->string('reason')->nullable()->index();
            $table->string('label')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('line3')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable()->index();
            $table->text('formatted_address')->nullable();
            $table->{$jsonColumnType}('formatted_lines')->nullable();
            $table->{$jsonColumnType}('components')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_place_id')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->text('waze_url')->nullable();
            $table->{$jsonColumnType}('navigation_links')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.snapshots', 'address_snapshots'));
    }
};
