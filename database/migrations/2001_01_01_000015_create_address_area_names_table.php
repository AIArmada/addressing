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
        $tableName = config('addressing.tables.area_names', 'address_area_names');

        Schema::create($tableName, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->string('name');
            $table->string('name_type', 32)->default('alternative')->index();
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'name', 'name_type']);
            $table->index('name');
        });

        Schema::create(config('addressing.tables.area_roles', 'address_area_roles'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->string('role', 50)->index();
            $table->string('country_code', 2)->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'role', 'country_code']);
        });

        Schema::create(config('addressing.tables.area_relationships', 'address_area_relationships'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_address_area_id')->index();
            $table->foreignUuid('child_address_area_id')->index();
            $table->string('relationship_type', 50)->index();
            $table->string('hierarchy_type', 50)->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->unique(['parent_address_area_id', 'child_address_area_id', 'relationship_type', 'hierarchy_type'], 'address_area_relationship_unique');
            $table->index(['parent_address_area_id', 'hierarchy_type'], 'address_area_relationship_parent_hierarchy_index');
            $table->index(['child_address_area_id', 'hierarchy_type'], 'address_area_relationship_child_hierarchy_index');
        });

        Schema::create(config('addressing.tables.postal_codes', 'postal_codes'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->string('country_code', 2)->index();
            $table->string('code', 20);
            $table->boolean('is_active')->default(true);
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->unique(['country_code', 'code']);
        });

        Schema::create(config('addressing.tables.area_postal_codes', 'address_area_postal_codes'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->foreignUuid('postal_code_id')->index();
            $table->string('relationship_type', 50)->default('served_by')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'postal_code_id', 'relationship_type'], 'area_postal_code_unique');
        });

        Schema::create(config('addressing.tables.address_area_assignments', 'address_area_assignments'), function (Blueprint $table) use ($jsonColumnType): void {
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
