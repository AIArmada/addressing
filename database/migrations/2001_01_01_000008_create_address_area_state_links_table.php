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
        $tableName = AddressingTableResolver::resolve('area_state_links');

        commerce_schema_create_if_missing($tableName, function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->foreignUuid('state_id')->index();
            $table->string('hierarchy_type', 50)->nullable()->index();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();

            $table->unique(['address_area_id', 'state_id', 'hierarchy_type']);
        });
    }
};
