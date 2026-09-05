<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = commerce_json_column_type('addressing', 'jsonb');

        commerce_schema_create_if_missing(config('addressing.tables.address_area_assignments', 'address_area_assignments'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->index();
            $table->foreignUuid('address_area_id')->index();
            $table->string('role', 50)->index();
            $table->boolean('is_primary')->default(false);
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->unique(['address_id', 'address_area_id', 'role'], 'address_area_assignment_unique');
        });
    }
};
