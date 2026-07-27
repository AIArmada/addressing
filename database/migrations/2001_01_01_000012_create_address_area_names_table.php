<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('addressing.tables.area_names', 'address_area_names'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->string('name');
            $table->string('source')->default('manual')->index();
            $table->string('name_type', 32)->default('alternative')->index();
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'name', 'name_type', 'source']);
            $table->index('name');
        });
    }
};
