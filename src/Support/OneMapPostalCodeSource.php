<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\PostalCodeSource;
use AIArmada\Addressing\Data\PostalCodeData;
use AIArmada\Addressing\Geography\Singapore\SingaporeGeographyProvider;
use Generator;
use Illuminate\Support\LazyCollection;

/**
 * Resolves explicit Singapore postcodes through OneMap.
 *
 * Only postcodes with a OneMap result whose POSTAL matches the query are
 * yielded; unknown postcodes are skipped here and reported as invalid by
 * the resolving action instead.
 */
final class OneMapPostalCodeSource implements PostalCodeSource
{
    public const string SOURCE_KEY = 'onemap_sg_v1';

    /** @param list<string> $postcodes */
    public function __construct(
        private readonly OneMapClient $client,
        private readonly array $postcodes,
    ) {}

    public function key(): string
    {
        return self::SOURCE_KEY;
    }

    /** @return LazyCollection<int, PostalCodeData> */
    public function postalCodes(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            foreach ($this->postcodes as $postcode) {
                $code = mb_strtoupper(mb_trim($postcode));

                if ($code === '') {
                    continue;
                }

                $match = $this->matchingResult($code);

                if ($match === null) {
                    continue;
                }

                yield new PostalCodeData(
                    source: self::SOURCE_KEY,
                    sourceId: 'sg:' . $code,
                    countryCode: 'SG',
                    code: $code,
                    areaSource: SingaporeGeographyProvider::AREA_SOURCE,
                    areaSourceId: 'sg:postal-sector:' . mb_substr($code, 0, 2),
                    relationshipType: 'served_by',
                    isPrimary: true,
                    metadata: $this->metadata($match),
                );
            }
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function matchingResult(string $code): ?array
    {
        foreach ($this->client->search($code) as $row) {
            $postal = $row['POSTAL'] ?? null;

            if (is_string($postal) && mb_strtoupper(mb_trim($postal)) === $code) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function metadata(array $row): array
    {
        return array_filter([
            'address' => self::stringOrNull($row['ADDRESS'] ?? null),
            'block' => self::stringOrNull($row['BLK_NO'] ?? null),
            'road_name' => self::stringOrNull($row['ROAD_NAME'] ?? null),
            'building' => self::stringOrNull($row['BUILDING'] ?? null),
            'latitude' => self::floatOrNull($row['LATITUDE'] ?? null),
            'longitude' => self::floatOrNull($row['LONGITUDE'] ?? null),
        ], static fn (mixed $value): bool => $value !== null);
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
