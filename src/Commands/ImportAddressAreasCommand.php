<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ImportAddressAreasAction;
use AIArmada\Addressing\Contracts\AddressAreaSource;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;

class ImportAddressAreasCommand extends Command
{
    protected $signature = 'address:import-areas {source} {--dry-run}';

    protected $description = 'Import address areas from a configured area source';

    public function handle(ImportAddressAreasAction $action, Application $app): int
    {
        $sourceKey = (string) $this->argument('source');
        $dryRun = (bool) $this->option('dry-run');

        $sourceClasses = config('addressing.area_sources', []);

        $sourceClass = null;
        foreach ($sourceClasses as $class) {
            if (! is_string($class) || ! class_exists($class)) {
                continue;
            }

            $instance = $app->make($class);

            if (! $instance instanceof AddressAreaSource) {
                continue;
            }

            if ($instance->key() === $sourceKey) {
                $sourceClass = $class;

                break;
            }
        }

        if ($sourceClass === null || ! class_exists($sourceClass)) {
            $this->error("No registered area source found with key: {$sourceKey}");

            return self::FAILURE;
        }

        /** @var AddressAreaSource $source */
        $source = $app->make($sourceClass);

        if ($dryRun) {
            $this->info('Running in dry-run mode. No records will be created or updated.');
        }

        $result = $action->execute($source, $dryRun);

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
