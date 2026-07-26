<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedAddressCountryReferencesAction;
use Illuminate\Console\Command;

class SeedAddressCountryReferencesCommand extends Command
{
    protected $signature = 'address:seed-country-references';

    protected $description = 'Seed country currency and timezone relationships';

    public function handle(SeedAddressCountryReferencesAction $action): int
    {
        $result = $action->execute();

        $this->info(sprintf(
            'Country references seeded: %d currency links, %d timezone links.',
            $result['currency_links'],
            $result['timezone_links'],
        ));

        return self::SUCCESS;
    }
}
