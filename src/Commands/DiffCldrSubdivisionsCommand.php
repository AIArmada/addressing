<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\DiffCldrSubdivisionsAction;
use Illuminate\Console\Command;

class DiffCldrSubdivisionsCommand extends Command
{
    protected $signature = 'address:reference:cldr
        {country? : Two-letter country code to diff (e.g. MY).}
        {--all : Diff every country.}
        {--cldr= : Path to a cldr-json checkout (the directory containing cldr-core/).}';

    protected $description = 'Diff subdivision codes against a CLDR checkout';

    public function handle(DiffCldrSubdivisionsAction $diff): int
    {
        $country = $this->option('all') ? null : $this->argument('country');

        if (! $this->option('all') && (! is_string($country) || mb_trim($country) === '')) {
            $this->error('Pass a country code (address:reference:cldr MY) or --all.');

            return self::FAILURE;
        }

        $cldr = $this->option('cldr');

        $result = $diff->execute(
            $this->option('all') ? null : (string) $country,
            is_string($cldr) && mb_trim($cldr) !== '' ? mb_trim($cldr) : null,
        );

        $rows = [];

        foreach ($result->drifts as $drift) {
            $rows[] = [$drift['country'], $drift['dimension'], $drift['code'], $drift['ours'], $drift['theirs']];
        }

        if ($rows !== []) {
            $this->table(['Country', 'Dimension', 'Code', 'Ours', 'CLDR'], $rows);
        }

        $scope = $result->countryCode ?? 'all countries';

        $this->line(sprintf('CLDR %s; %d states.json rows without a code are uncomparable.', $result->cldrVersion ?? '(unversioned)', $result->nullCodeRows));

        $pinned = config('addressing.reference.cldr_version');

        if (! is_string($pinned) || mb_trim($pinned) === '') {
            $this->line(sprintf('Tip: set addressing.reference.cldr_version to [%s] to pin this checkout.', $result->cldrVersion ?? 'unknown'));
        }

        if ($rows === []) {
            $this->info(sprintf('Checked %s: no drift.', $scope));

            return self::SUCCESS;
        }

        $this->error(sprintf('Checked %s: %d drifted row%s.', $scope, count($rows), count($rows) === 1 ? '' : 's'));

        return self::FAILURE;
    }
}
