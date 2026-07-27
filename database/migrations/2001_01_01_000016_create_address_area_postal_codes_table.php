<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('addressing.tables.area_postal_codes', 'address_area_postal_codes'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->foreignUuid('postal_code_id')->index();
            $table->string('source')->default('manual')->index();
            $table->string('source_id')->nullable()->index();
            $table->string('relationship_type', 50)->default('served_by')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'postal_code_id', 'relationship_type', 'source'], 'area_postal_code_unique');
        });
    }
};
