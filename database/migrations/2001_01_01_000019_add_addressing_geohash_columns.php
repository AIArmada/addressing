<?php

declare(strict_types=1);

use AIArmada\Addressing\Support\SpatialSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function shouldRun(): bool
    {
        return config('addressing.features.spatial.geohash') === true;
    }

    public function up(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        SpatialSchema::geohash(Schema::getConnection());
    }
};
