<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaBoundaryWriter;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\SpatialLocation;
use InvalidArgumentException;
use RuntimeException;

final readonly class SetAddressAreaBoundaryAction implements AddressAreaBoundaryWriter
{
    public function execute(string $areaId, ?string $wkt): void
    {
        $area = ModelResolver::areaClass()::query()->findOrFail($areaId);

        $connection = $area->getConnection();
        SpatialLocation::requireEnabled($connection, 'postgis');

        $boundary = null;

        if ($wkt !== null) {
            $boundary = $connection->scalar(<<<'SQL'
                SELECT ST_Multi(shape)
                FROM (SELECT ST_GeomFromText(?, 4326) AS shape) AS input
                WHERE GeometryType(shape) IN ('POLYGON', 'MULTIPOLYGON')
                    AND ST_NDims(shape) = 2
                    AND NOT ST_IsEmpty(shape)
                    AND ST_IsValid(shape)
                    AND ST_Covers(ST_MakeEnvelope(-180, -90, 180, 90, 4326), shape)
                SQL, [$wkt], false);

            if (! is_string($boundary)) {
                throw new InvalidArgumentException('Expected a valid, non-empty WGS 84 polygon or multipolygon.');
            }
        }

        $area->setAttribute('boundary', $boundary);
        if (! $area->saveOrFail()) {
            throw new RuntimeException('The address area boundary could not be saved.');
        }
    }
}
