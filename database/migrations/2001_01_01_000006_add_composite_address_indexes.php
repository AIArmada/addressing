<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addressesTable = config('addressing.tables.addresses', 'addresses');
        $addressablesTable = config('addressing.tables.addressables', 'addressables');

        if (Schema::hasTable($addressesTable)) {
            Schema::table($addressesTable, function (Blueprint $table): void {
                $table->index(['country_code', 'city'], 'addr_country_city_idx');
                $table->index(['country_code', 'postcode'], 'addr_country_postcode_idx');
            });
        }

        if (Schema::hasTable($addressablesTable)) {
            Schema::table($addressablesTable, function (Blueprint $table): void {
                $table->index(
                    ['addressable_type', 'addressable_id', 'is_primary'],
                    'addrbl_type_id_primary_idx',
                );
            });
        }
    }
};
