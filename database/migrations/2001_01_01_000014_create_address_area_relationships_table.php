<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = commerce_json_column_type('addressing', 'jsonb');

        commerce_schema_create_if_missing(config('addressing.tables.area_relationships', 'address_area_relationships'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_address_area_id')->index();
            $table->foreignUuid('child_address_area_id')->index();
            $table->string('relationship_type', 50)->index();
            $table->string('hierarchy_type', 50)->index();
            $table->string('source')->default('manual')->index();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->unique(['parent_address_area_id', 'child_address_area_id', 'relationship_type', 'hierarchy_type', 'source'], 'address_area_relationship_unique');
            $table->index(['parent_address_area_id', 'hierarchy_type'], 'address_area_relationship_parent_hierarchy_index');
            $table->index(['child_address_area_id', 'hierarchy_type'], 'address_area_relationship_child_hierarchy_index');
        });
    }
};
