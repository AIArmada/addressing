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

        commerce_schema_create_if_missing(AddressingTableResolver::resolve('postal_codes'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->string('country_code', 2)->index();
            $table->string('code', 20);
            $table->boolean('is_active')->default(true);
            $table->{$jsonColumnType}('metadata')->nullable();
            $table->timestamps();
            $table->unique(['country_code', 'code']);
        });
    }
};
