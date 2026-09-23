<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Actions\SeedAddressingAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use AIArmada\Addressing\Support\SeedAddressingSummary;
use Illuminate\Database\Seeder;

class AddressingSeeder extends Seeder
{
    public function run(SeedAddressingAction $action): void
    {
        $result = $action->execute(ConsoleSeedProgress::for($this->command?->getOutput()));

        if ($this->command !== null) {
            $this->command->info(SeedAddressingSummary::line($result));
        }
    }
}
