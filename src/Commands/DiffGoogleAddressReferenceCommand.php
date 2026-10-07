<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\DiffGoogleAddressReferenceAction;
use AIArmada\Addressing\Support\AddressValidationProfiles;
use Illuminate\Console\Command;

class DiffGoogleAddressReferenceCommand extends Command
{
    protected $signature = 'address:reference:google
        {country? : Two-letter country code to diff (e.g. MY).}
        {--all : Diff every profiled country.}';

    protected $description = 'Diff validation profiles against Google address data';

    public function handle(DiffGoogleAddressReferenceAction $diff): int
    {
        $country = $this->option('all') ? null : $this->argument('country');

        if (! $this->option('all') && (! is_string($country) || mb_trim($country) === '')) {
            $this->error('Pass a country code (address:reference:google MY) or --all.');

            return self::FAILURE;
        }

        $codes = $this->option('all')
            ? array_keys(AddressValidationProfiles::all())
            : [mb_strtoupper(mb_trim((string) $country))];

        $rows = [];
        $silent = [];
        $checked = 0;

        foreach ($codes as $code) {
            $result = $diff->execute((string) $code);
            $checked++;

            if ($result->googleSilent) {
                $silent[] = $result->countryCode;

                continue;
            }

            foreach ($result->drifts as $drift) {
                $rows[] = [$result->countryCode, $drift['dimension'], $drift['ours'], $drift['theirs']];
            }
        }

        if ($rows !== []) {
            $this->table(['Country', 'Dimension', 'Ours', 'Google'], $rows);
        }

        if ($silent !== []) {
            $this->line(sprintf('No Google data for: %s.', implode(', ', $silent)));
        }

        if ($rows === []) {
            $this->info(sprintf('Checked %d countr%s: no drift.', $checked, $checked === 1 ? 'y' : 'ies'));

            return self::SUCCESS;
        }

        $this->error(sprintf('Checked %d countr%s: %d drifted row%s.', $checked, $checked === 1 ? 'y' : 'ies', count($rows), count($rows) === 1 ? '' : 's'));

        return self::FAILURE;
    }
}
