<?php

declare(strict_types=1);

use AIArmada\Addressing\Database\Migrations\SpatialSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function shouldRun(): bool
    {
        return config('addressing.features.spatial.h3') === true;
    }

    public function up(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        SpatialSchema::h3(Schema::getConnection());
    }
};
