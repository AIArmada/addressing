<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Contracts\CountryDatasetSeeder;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use Illuminate\Database\Seeder;

class AddressCountrySeeder extends Seeder
{
    public function run(CountryDatasetSeeder $action): void
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
