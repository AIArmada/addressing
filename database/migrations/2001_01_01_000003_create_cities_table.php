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
        $tableName = config('addressing.tables.cities', 'cities');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->foreignUuid('state_id')->index();
            $table->string('name');
            $table->string('postcode')->nullable();
            $table->string('label')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.cities', 'cities'));
    }
};
