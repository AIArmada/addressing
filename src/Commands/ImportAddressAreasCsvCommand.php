<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ImportAddressAreasCsvCommand extends Command
{
    protected $signature = 'address:import-areas-csv {path} {--source=} {--dry-run}';

    protected $description = 'Import address areas from a CSV file';

    public function handle(ImportAddressAreasAction $action): int
    {
        $path = (string) $this->argument('path');
        $sourceKey = (string) ($this->option('source') ?? basename($path, '.csv'));
        $dryRun = (bool) $this->option('dry-run');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");

            return self::FAILURE;
        }

        try {
            $csvSource = new CsvAddressAreaSource($path, $sourceKey);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Running in dry-run mode. No records will be created or updated.');
        }

        $result = $action->execute($csvSource, $dryRun);

        $this->info(sprintf(
            'Import complete: %d created, %d updated, %d skipped, %d failures.',
            $result->created,
            $result->updated,
            $result->skipped,
            count($result->failures),
        ));

        foreach ($result->failures as $failure) {
            $this->warn(sprintf(
                '  [%s] %s: %s',
                $failure->sourceId,
                $failure->name ?? 'N/A',
                $failure->reason,
            ));
        }

        return $result->hasFailures() ? self::FAILURE : self::SUCCESS;
    }
}
