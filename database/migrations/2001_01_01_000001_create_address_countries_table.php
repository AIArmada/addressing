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
            $table->string('name');
            $table->string('phone_code', 5)->nullable();
            $table->string('iso3', 3)->nullable()->unique();
            $table->string('numeric_code', 3)->nullable()->unique();
            $table->string('native')->nullable();
            $table->string('capital')->nullable();
            $table->string('region')->nullable()->index();
            $table->string('subregion')->nullable()->index();
            $table->string('tld')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('emoji')->nullable();
            $table->string('emojiU')->nullable();
            $table->{$jsonColumnType}('translations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('addressing.tables.countries', 'countries'));
    }
};
