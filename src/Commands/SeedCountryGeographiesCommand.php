<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedCountryGeographiesAction;
use Illuminate\Console\Command;

class SeedCountryGeographiesCommand extends Command
{
    protected $signature = 'address:seed-geographies
        {country? : Optional ISO2 country code to seed (e.g. MY). Seeds all configured providers when omitted.}';

    protected $description = 'Seed state/city data from configured geography providers';

    public function handle(SeedCountryGeographiesAction $action): int
    {
        $result = $action->execute($this->argument('country'));

        if ($result['seeded'] === []) {
            $this->warn('No providers matched.');

            return self::FAILURE;
        }

        $this->info('Seeded: ' . implode(', ', $result['seeded']));

        foreach ($result['areas'] as $countryCode => $areaCounts) {
            $this->line(sprintf(
                '  %s areas: %d created, %d updated, %d skipped',
                $countryCode,
                $areaCounts['created'],
                $areaCounts['updated'],
                $areaCounts['skipped'],
            ));
        }

        return self::SUCCESS;
    }
}
