<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\CommerceSupport\Support\ConnectionDriver;
use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection();
        $driver = ConnectionDriver::name($connection);

        foreach ($this->indexDefinitions() as $tableKey => $indexName) {
            $tableName = AddressingTableResolver::resolve($tableKey);

            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'name')) {
                continue;
            }

            if (Schema::hasIndex($tableName, $indexName)) {
                continue;
            }

            if (in_array($driver, ['mysql', 'pgsql', 'sqlite'], true)) {
                $this->createFunctionalIndex($connection, $tableName, $indexName, $driver);

                continue;
            }

            $this->createNormalizedNameIndex($tableName, $indexName);
        }
    }

    /**
     * @return array<string, string>
     */
    private function indexDefinitions(): array
    {
        return [
            'countries' => 'countries_name_lower_index',
            'states' => 'states_name_lower_index',
            'cities' => 'cities_name_lower_index',
            'areas' => 'address_areas_name_lower_index',
            'area_names' => 'address_area_names_name_lower_index',
        ];
    }

    private function createFunctionalIndex(
        Connection $connection,
        string $tableName,
        string $indexName,
        string $driver,
    ): void {
        $grammar = $connection->getQueryGrammar();
        $wrappedName = $grammar->wrap('name');
        $indexedExpression = $driver === 'mysql'
            ? sprintf('((LOWER(%s)))', $wrappedName)
            : sprintf('(LOWER(%s))', $wrappedName);
        $ifNotExists = $driver === 'mysql' ? '' : ' IF NOT EXISTS';

        $connection->statement(sprintf(
            'CREATE INDEX%s %s ON %s %s',
            $ifNotExists,
            $grammar->wrap($indexName),
            $grammar->wrapTable($tableName),
            $indexedExpression,
        ));
    }

    private function createNormalizedNameIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasColumn($tableName, 'name_normalized')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->computed('name_normalized', 'LOWER(name)')->persisted();
            });
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->index('name_normalized', $indexName);
        });
    }
};
