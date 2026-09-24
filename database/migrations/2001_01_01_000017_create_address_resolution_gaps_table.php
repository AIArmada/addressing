<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $jsonColumnType = commerce_json_column_type('addressing', 'jsonb');

        Schema::create(AddressingTableResolver::resolve('resolution_gaps'), function (Blueprint $table) use ($jsonColumnType): void {
            $table->uuid('id')->primary();
            $table->string('source', 50);
            $table->string('country_code', 2);
            $table->string('role', 50);
            $table->string('value');
            $table->string('normalized');
            $table->string('reason', 20)->default('unmatched');
            $table->unsignedBigInteger('hits')->default(1);
            $table->timestampTz('first_seen_at')->nullable();
            $table->timestampTz('last_seen_at')->nullable();
            $table->{$jsonColumnType}('context')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignUuid('matched_area_id')->nullable();
            $table->string('matched_by')->nullable();
            $table->timestampTz('matched_at')->nullable();
            $table->timestamps();
            $table->unique(['source', 'country_code', 'role', 'normalized'], 'resolution_gaps_upsert_unique');
            $table->index(['country_code', 'status', 'last_seen_at'], 'resolution_gaps_report_index');
            $table->index(['status', 'reason'], 'resolution_gaps_status_reason_index');
        });
    }
};
