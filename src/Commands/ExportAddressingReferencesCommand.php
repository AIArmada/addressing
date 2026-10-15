<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ExportAddressingReferencesAction;
use Illuminate\Console\Command;
use Throwable;

final class ExportAddressingReferencesCommand extends Command
{
    protected $signature = 'address:reference:export {--output= : Destination NDJSON file} {--country=* : Limit the export to ISO2 country codes} {--seed-revision= : Factual revision of the dataset known to have been seeded}';

    protected $description = 'Export global addressing references for offline review and comparison';

    public function handle(ExportAddressingReferencesAction $action): int
    {
        $output = $this->option('output');

        if (! is_string($output) || mb_trim($output) === '') {
            $this->components->error('Pass a writable destination with --output=.');

            return self::FAILURE;
        }

        $countries = $this->option('country');
        $revision = $this->option('seed-revision');

        try {
            $result = $action->execute($output, is_array($countries) ? $countries : [], is_string($revision) && mb_trim($revision) !== '' ? $revision : null);
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(sprintf('Wrote %s (%s; %d reference rows).', $result['path'], implode(', ', $result['countries']), array_sum($result['counts'])));

        return self::SUCCESS;
    }
}
