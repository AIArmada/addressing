<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\SeedAddressingAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use AIArmada\Addressing\Support\SeedAddressingSummary;
use Illuminate\Console\Command;

class SeedAddressingCommand extends Command
{
    protected $signature = 'address:seed';

    protected $description = 'Seed the full addressing reference dataset (countries, states, cities, geographies)';

    public function handle(SeedAddressingAction $action): int
    {
        $result = $action->execute(ConsoleSeedProgress::for($this->output));

        $this->info(SeedAddressingSummary::line($result));

        return self::SUCCESS;
    }
}
