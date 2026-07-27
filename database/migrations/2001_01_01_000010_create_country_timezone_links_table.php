<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('addressing.tables.country_timezone_links', 'country_timezone_links'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->index();
            $table->foreignUuid('timezone_id')->index();
            $table->timestamps();
            $table->unique(['country_id', 'timezone_id']);
        });
    }
};
