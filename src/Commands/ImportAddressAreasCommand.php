<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Support\CsvAddressAreaSource;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class ImportAddressAreasCommand extends Command
{
    protected $signature = 'address:import-areas
        {source? : Registered area source key. Omit when using --csv.}
        {--csv= : Import areas from this CSV file instead of a registered source}
        {--source-key= : Source key recorded for --csv rows. Defaults to the CSV filename.}
        {--dry-run}
        {--reactivate}';

    protected $description = 'Import address areas from a configured area source or a CSV file';

    public function handle(ImportAddressAreasAction $action, Application $app): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            $source = $this->resolveSource($app);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Running in dry-run mode. No records will be created or updated.');
        }

        $result = $action->execute($source, $dryRun, reactivate: (bool) $this->option('reactivate'));

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

    /**
     * @throws InvalidArgumentException
     */
    private function resolveSource(Application $app): AddressAreaSource
    {
        $csvPath = $this->option('csv');

        if (is_string($csvPath) && $csvPath !== '') {
            $sourceKey = $this->option('source-key');
            $sourceKey = is_string($sourceKey) && $sourceKey !== ''
                ? $sourceKey
                : pathinfo($csvPath, PATHINFO_FILENAME);

            return new CsvAddressAreaSource($csvPath, $sourceKey);
        }

        $sourceKey = (string) $this->argument('source');

        if ($sourceKey === '') {
            throw new InvalidArgumentException('Provide a registered source key or the --csv option.');
        }

        foreach (config('addressing.area_sources', []) as $class) {
            if (! is_string($class) || ! class_exists($class)) {
                continue;
            }

            $instance = $app->make($class);

            if ($instance instanceof AddressAreaSource && $instance->key() === $sourceKey) {
                return $instance;
            }
        }

        throw new InvalidArgumentException("No registered area source found with key: {$sourceKey}");
    }
}
