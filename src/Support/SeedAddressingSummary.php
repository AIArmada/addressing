<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

class SeedAddressingSummary
{
    /**
     * @param  array{countries: array<string, mixed>, references: array<string, mixed>, states: array<string, mixed>, cities: array<string, mixed>, geographies: array<string, mixed>, postal_codes: array{seeded: list<string>, skipped: list<string>, codes: array<string, array{created: int, updated: int, skipped: int, links: int}>}}  $result
     */
    public static function line(array $result): string
    {
        $countries = $result['countries'];
        $references = $result['references'];
        $states = $result['states'];
        $cities = $result['cities'];
        $geographies = $result['geographies'];
        $postalCodes = $result['postal_codes'];

        return sprintf(
            'Addressing seeded: countries %d created / %d updated / %d skipped; country references %d currency links / %d timezone links; states %d created / %d updated / %d skipped; cities %d created / %d updated / %d skipped; country geographies seeded for %s; areas %d created / %d updated / %d skipped; postcodes %d created / %d updated / %d skipped / %d links.',
            $countries['created'],
            $countries['updated'],
            $countries['skipped'],
            $references['currency_links'],
            $references['timezone_links'],
            $states['created'],
            $states['updated'],
            $states['skipped'],
            $cities['created'],
            $cities['updated'],
            $cities['skipped'],
            implode(', ', $geographies['seeded']),
            array_sum(array_column($geographies['areas'], 'created')),
            array_sum(array_column($geographies['areas'], 'updated')),
            array_sum(array_column($geographies['areas'], 'skipped')),
            array_sum(array_column($postalCodes['codes'], 'created')),
            array_sum(array_column($postalCodes['codes'], 'updated')),
            array_sum(array_column($postalCodes['codes'], 'skipped')),
            array_sum(array_column($postalCodes['codes'], 'links')),
        );
    }
}
