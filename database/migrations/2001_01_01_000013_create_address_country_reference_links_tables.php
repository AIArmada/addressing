<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'country_currency_links' => 'currency_id',
            'country_timezone_links' => 'timezone_id',
        ] as $configKey => $referenceColumn) {
            $tableName = config("addressing.tables.{$configKey}", $configKey);

            Schema::create($tableName, function (Blueprint $table) use ($referenceColumn): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('country_id')->index();
                $table->foreignUuid($referenceColumn)->index();
                $table->timestamps();
                $table->unique(['country_id', $referenceColumn]);
            });
        }
    }

    public function down(): void
    {
        foreach (['country_currency_links', 'country_timezone_links'] as $configKey) {
            Schema::dropIfExists(config("addressing.tables.{$configKey}", $configKey));
        }
    }
};
