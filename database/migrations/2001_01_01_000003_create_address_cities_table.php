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
        $tableName = AddressingTableResolver::resolve('cities');

        if (Schema::hasTable($tableName)) {
            return;
        }

        commerce_schema_create_if_missing($tableName, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->foreignUuid('state_id')->nullable()->index();
            $table->string('name');
            $table->string('country_code', 3)->nullable();
            $table->string('state_code', 5)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }
};
