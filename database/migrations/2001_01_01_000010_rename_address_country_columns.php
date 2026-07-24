<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('addressing.tables.countries', 'countries');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'native_name')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('native_name', 'native'));
        }

        if (Schema::hasColumn($tableName, 'currency_codes')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('currency_codes', 'currencies'));
        }

        if (Schema::hasColumn($tableName, 'default_currency_code')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('default_currency_code', 'currency'));
        }

        if (! Schema::hasColumn($tableName, 'emoji_unicode')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->string('emoji_unicode')->nullable()->after('emoji'));
        }
    }

    public function down(): void
    {
        $tableName = config('addressing.tables.countries', 'countries');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'native')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('native', 'native_name'));
        }

        if (Schema::hasColumn($tableName, 'currencies')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('currencies', 'currency_codes'));
        }

        if (Schema::hasColumn($tableName, 'currency')) {
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn('currency', 'default_currency_code'));
        }

        Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('emoji_unicode'));
    }
};
