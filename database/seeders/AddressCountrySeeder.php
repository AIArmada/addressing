<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Actions\SeedAddressCountriesAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use Illuminate\Database\Seeder;

class AddressCountrySeeder extends Seeder
{
    public function run(SeedAddressCountriesAction $action): void
    {
        $result = $action->execute(ConsoleSeedProgress::for($this->command?->getOutput()));

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Countries: %d created, %d updated, %d skipped.',
                $result['created'],
                $result['updated'],
                $result['skipped'],
            ));
        }
    }
}
