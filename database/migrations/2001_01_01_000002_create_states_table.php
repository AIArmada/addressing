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
        $tableName = config('addressing.tables.states', 'states');

        Schema::create($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('label')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.states', 'states'));
    }
};
