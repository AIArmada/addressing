<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        commerce_schema_create_if_missing(AddressingTableResolver::resolve('area_roles'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_area_id')->index();
            $table->string('role', 50)->index();
            $table->string('source')->default('manual')->index();
            $table->string('country_code', 2)->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['address_area_id', 'role', 'country_code', 'source']);
        });
    }
};
