<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedAddressCountriesAction;
use Illuminate\Console\Command;

class SeedAddressCountriesCommand extends Command
{
    protected $signature = 'address:seed-countries';

    protected $description = 'Seed address_countries table with bundled ISO 3166-1 country/territory data';

    public function handle(SeedAddressCountriesAction $action): int
    {
        $result = $action->execute();

        $this->info(sprintf(
            'Countries seeded: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
