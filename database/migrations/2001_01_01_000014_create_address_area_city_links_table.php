<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            config('addressing.tables.states', 'states'),
            config('addressing.tables.cities', 'cities'),
        ] as $tableName) {
            if (Schema::hasColumn($tableName, 'metadata')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('metadata');
                });
            }
        }

        $tableName = config('addressing.tables.area_city_links', 'address_area_city_links');

        if (Schema::hasTable($tableName)) {
            return;
        }

        $jsonColumnType = commerce_json_column_type('addressing', 'json');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->foreignUuid('city_id')->index();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();

            $table->unique(['address_area_id', 'city_id']);
        });
    }
};
