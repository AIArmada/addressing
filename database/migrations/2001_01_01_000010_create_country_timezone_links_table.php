<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        commerce_schema_create_if_missing(AddressingTableResolver::resolve('country_timezone_links'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->foreignUuid('timezone_id')->index();
            $table->timestamps();
            $table->unique(['country_id', 'timezone_id']);
        });
    }
};
