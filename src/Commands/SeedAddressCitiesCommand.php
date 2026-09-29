<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedAddressCitiesAction;
use Illuminate\Console\Command;

class SeedAddressCitiesCommand extends Command
{
    protected $signature = 'address:seed-cities';

    protected $description = 'Seed cities table with bundled city/town data';

    public function handle(SeedAddressCitiesAction $action): int
    {
        $result = $action->execute();

        $this->info(sprintf(
            'Cities seeded: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
