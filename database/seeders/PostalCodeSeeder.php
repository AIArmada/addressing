<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Actions\SeedPostalCodesAction;
use AIArmada\Addressing\Support\ConsoleSeedProgress;
use Illuminate\Database\Seeder;

class PostalCodeSeeder extends Seeder
{
    public function run(SeedPostalCodesAction $action): void
    {
        $result = $action->execute(null, ConsoleSeedProgress::for($this->command?->getOutput()));

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Postal codes: %d created, %d updated, %d skipped, %d links across %d countries.',
                array_sum(array_column($result['codes'], 'created')),
                array_sum(array_column($result['codes'], 'updated')),
                array_sum(array_column($result['codes'], 'skipped')),
                array_sum(array_column($result['codes'], 'links')),
                count($result['seeded']),
            ));
        }
    }
}
