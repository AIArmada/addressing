<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedAddressStatesAction;
use Illuminate\Console\Command;

class SeedAddressStatesCommand extends Command
{
    protected $signature = 'address:seed-states';

    protected $description = 'Seed states table with bundled state/province data';

    public function handle(SeedAddressStatesAction $action): int
    {
        $result = $action->execute();

        $this->info(sprintf(
            'States seeded: %d created, %d updated, %d skipped.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
