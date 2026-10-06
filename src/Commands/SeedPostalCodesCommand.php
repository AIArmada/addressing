<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedPostalCodesAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use Illuminate\Console\Command;

class SeedPostalCodesCommand extends Command
{
    protected $signature = 'address:seed-postal-codes
        {country? : Optional ISO2 country code to seed (e.g. MY). Seeds all bundled postcode datasets when omitted.}';

    protected $description = 'Seed bundled postcode datasets and their area links';

    public function handle(SeedPostalCodesAction $action): int
    {
        $result = $action->execute($this->argument('country'), ConsoleSeedProgress::for($this->output));

        if ($result['seeded'] === []) {
            $this->warn('No postcode datasets matched.');

            return self::FAILURE;
        }

        $this->info('Seeded: ' . implode(', ', $result['seeded']));

        foreach ($result['codes'] as $countryCode => $codeCounts) {
            $this->line(sprintf(
                '  %s postcodes: %d created, %d updated, %d skipped, %d links',
                $countryCode,
                $codeCounts['created'],
                $codeCounts['updated'],
                $codeCounts['skipped'],
                $codeCounts['links'],
            ));
        }

        return self::SUCCESS;
    }
}
