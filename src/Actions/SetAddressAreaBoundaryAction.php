<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\SpatialLocation;
use InvalidArgumentException;

final class SetAddressAreaBoundaryAction
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
        $area->saveOrFail();
    }
}
