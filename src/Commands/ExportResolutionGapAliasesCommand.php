<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ExportResolutionGapAliasesAction;
use Illuminate\Console\Command;
use ReflectionClass;

class ExportResolutionGapAliasesCommand extends Command
{
    protected $signature = 'address:export-gap-aliases
        {--country= : Two-letter country code to export (e.g. ID).}
        {--prune : Delete manual aliases exactly duplicated by a provider-sourced pair.}';

    protected $description = 'Export admin-matched gap aliases as provider areaNames() entries';

    public function handle(ExportResolutionGapAliasesAction $export): int
    {
        $country = $this->option('country');

        if (! is_string($country) || mb_trim($country) === '') {
            $this->error('The --country option is required (e.g. --country=ID).');

            return self::FAILURE;
        }

        $result = $export->execute($country, prune: (bool) $this->option('prune'));

        foreach ($result->warnings as $warning) {
            $this->warn($warning);
        }

        if ($result->candidatesByProvider === []) {
            $this->info(sprintf('No gap aliases to promote for %s.', $result->countryCode));
        }

        foreach ($result->candidatesByProvider as $providerClass => $candidates) {
            $this->line('');
            $this->line(sprintf('// %s::areaNames() additions', $providerClass));

            $file = (new ReflectionClass($providerClass))->getFileName();

            if (is_string($file)) {
                $this->line(sprintf('// Provider file: %s', $file));
            }

            $grouped = [];

            foreach ($candidates as $candidate) {
                $grouped[$candidate['source_id']][] = $candidate;
            }

            foreach ($grouped as $sourceId => $entries) {
                $this->line(sprintf('%s => [', var_export($sourceId, true)));

                foreach ($entries as $entry) {
                    $this->line(sprintf(
                        "    ['name' => %s, 'name_type' => %s%s],",
                        var_export($entry['name'], true),
                        var_export($entry['name_type'], true),
                        $entry['is_preferred'] ? ', \'is_preferred\' => true' : '',
                    ));
                }

                $this->line('],');
            }
        }

        $this->line('');
        $this->info(sprintf(
            'Export complete: %d candidates, %d unshipped skipped, %d already-declared skipped, %d missing aliases skipped, %d missing areas skipped, %d duplicates skipped.',
            $result->candidateCount(),
            $result->skippedUnshipped,
            $result->skippedDeclared,
            $result->skippedMissingAlias,
            $result->skippedMissingArea,
            $result->skippedDuplicates,
        ));

        if ((bool) $this->option('prune')) {
            $this->info(sprintf('Pruned %d duplicated manual aliases.', $result->pruned));
        }

        return self::SUCCESS;
    }
}
