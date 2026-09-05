<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        commerce_schema_create_if_missing(config('addressing.tables.country_currency_links', 'country_currency_links'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->foreignUuid('currency_id')->index();
            $table->timestamps();
            $table->unique(['country_id', 'currency_id']);
        });
    }
};
