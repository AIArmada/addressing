<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Support\ReferenceExportContract;
use AIArmada\Addressing\Support\ReferenceExportFile;
use Generator;
use InvalidArgumentException;

final class CompareAddressingReferenceExportsAction
{
    private const EXAMPLE_LIMIT = 100;

    public function __construct(private readonly ReferenceExportFile $files) {}

    /** @return array{counts: array<string, array<string, int>>, examples: list<array<string, mixed>>, scopes: array{before: array<string, mixed>, after: array<string, mixed>}} */
    public function execute(string $beforePath, string $afterPath): array
    {
        $beforeHeader = $this->files->header($beforePath);
        $afterHeader = $this->files->header($afterPath);
        $before = $this->files->records($beforePath);
        $after = $this->files->records($afterPath);
        $before->rewind();
        $after->rewind();

        $counts = $this->emptyCounts();
        $examples = $this->emptyExamples();
        $left = $before->current();
        $right = $after->current();

        while ($left !== null || $right !== null) {
            $leftKey = $left === null ? null : ReferenceExportContract::sortKey($left['type'], $left['key']);
            $rightKey = $right === null ? null : ReferenceExportContract::sortKey($right['type'], $right['key']);
            $targetKey = $leftKey === null ? $rightKey : ($rightKey === null || strcmp($leftKey, $rightKey) <= 0 ? $leftKey : $rightKey);

            if (! is_string($targetKey)) {
                throw new InvalidArgumentException('Reference comparison could not determine the next identity.');
            }

            $leftGroup = $leftKey === $targetKey ? $this->consumeGroup($before, $left, $targetKey) : [];
            $rightGroup = $rightKey === $targetKey ? $this->consumeGroup($after, $right, $targetKey) : [];

            if (count($leftGroup) > 1 || count($rightGroup) > 1) {
                $record = $leftGroup[0] ?? $rightGroup[0];
                $counts['ambiguous'][$record['type']]++;
                $examples = $this->addExample($examples, [
                    'status' => 'ambiguous',
                    'type' => $record['type'],
                    'key' => $record['key'],
                    'before_count' => count($leftGroup),
                    'after_count' => count($rightGroup),
                ]);

                continue;
            }

            if ($leftGroup === []) {
                ['counts' => $counts, 'examples' => $examples] = $this->recordDifference('added', $rightGroup[0], $beforeHeader, $afterHeader, $counts, $examples);

                continue;
            }

            if ($rightGroup === []) {
                ['counts' => $counts, 'examples' => $examples] = $this->recordDifference('missing', $leftGroup[0], $afterHeader, $beforeHeader, $counts, $examples);

                continue;
            }

            $leftRecord = $leftGroup[0];
            $rightRecord = $rightGroup[0];
            $status = hash_equals($leftRecord['sha256'], $rightRecord['sha256']) ? 'unchanged' : 'changed';
            $counts[$status][$leftRecord['type']]++;

            if ($status === 'changed') {
                $examples = $this->addExample($examples, [
                    'status' => $status,
                    'type' => $leftRecord['type'],
                    'key' => $leftRecord['key'],
                    'before_sha256' => $leftRecord['sha256'],
                    'after_sha256' => $rightRecord['sha256'],
                ]);
            }
        }

        return [
            'counts' => $counts,
            'examples' => $examples,
            'scopes' => ['before' => $beforeHeader['scope'], 'after' => $afterHeader['scope']],
        ];
    }

    /** @return array<string, array<string, int>> */
    private function emptyCounts(): array
    {
        $typeCounts = $this->emptyTypeCounts();

        return [
            'added' => $typeCounts,
            'ambiguous' => $typeCounts,
            'changed' => $typeCounts,
            'missing' => $typeCounts,
            'scope_unknown' => $typeCounts,
            'unchanged' => $typeCounts,
        ];
    }

    /** @return array{countries: int, states: int, cities: int, areas: int, area_names: int, area_roles: int, area_relationships: int, area_state_links: int, postal_codes: int, area_postal_codes: int, country_currency_links: int, country_timezone_links: int} */
    private function emptyTypeCounts(): array
    {
        return [
            'countries' => 0,
            'states' => 0,
            'cities' => 0,
            'areas' => 0,
            'area_names' => 0,
            'area_roles' => 0,
            'area_relationships' => 0,
            'area_state_links' => 0,
            'postal_codes' => 0,
            'area_postal_codes' => 0,
            'country_currency_links' => 0,
            'country_timezone_links' => 0,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function emptyExamples(): array
    {
        return [];
    }

    /** @param Generator<array<string, mixed>> $records @param array<string, mixed>|null $current @return list<array<string, mixed>> */
    private function consumeGroup(Generator $records, ?array &$current, string $targetKey): array
    {
        $group = [];

        while ($current !== null && ReferenceExportContract::sortKey($current['type'], $current['key']) === $targetKey) {
            $group[] = $current;
            $records->next();
            $current = $records->current();
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $oppositeHeader
     * @param  array<string, mixed>  $recordHeader
     * @param  array<string, array<string, int>>  $counts
     * @param  list<array<string, mixed>>  $examples
     * @return array{counts: array<string, array<string, int>>, examples: list<array<string, mixed>>}
     */
    private function recordDifference(string $status, array $record, array $oppositeHeader, array $recordHeader, array $counts, array $examples): array
    {
        $country = $this->countryOf($record);
        $actualStatus = $this->comparableCountryScope($recordHeader, $oppositeHeader, $country) ? $status : 'scope_unknown';
        $counts[$actualStatus][$record['type']]++;

        if ($actualStatus !== 'unchanged') {
            $examples = $this->addExample($examples, [
                'status' => $actualStatus,
                'direction' => $status,
                'type' => $record['type'],
                'country' => $country,
                'key' => $record['key'],
                'sha256' => $record['sha256'],
                'present_in_scope' => $recordHeader['scope']['countries'],
            ]);
        }

        return ['counts' => $counts, 'examples' => $examples];
    }

    /** @param array<string, mixed> $record */
    private function countryOf(array $record): string
    {
        $key = $record['key'];
        $country = match ($record['type']) {
            'countries', 'states', 'cities', 'areas', 'postal_codes', 'country_currency_links', 'country_timezone_links' => $key['country'] ?? null,
            'area_names', 'area_roles', 'area_state_links', 'area_postal_codes' => $key['area']['country'] ?? null,
            'area_relationships' => $key['parent']['country'] ?? null,
            default => null,
        };

        if (! is_string($country) || $country === '') {
            throw new InvalidArgumentException(sprintf('Reference record [%s] has no country identity.', $record['type']));
        }

        return $country;
    }

    /** @param array<string, mixed> $recordHeader @param array<string, mixed> $oppositeHeader */
    private function comparableCountryScope(array $recordHeader, array $oppositeHeader, string $country): bool
    {
        if (! in_array($country, $recordHeader['scope']['countries'], true) || ! in_array($country, $oppositeHeader['scope']['countries'], true)) {
            return false;
        }

        $providersForCountry = static fn (array $header): array => array_values(array_filter(
            $header['scope']['configured_providers'],
            static fn (array $provider): bool => $provider['country'] === $country,
        ));

        return $providersForCountry($recordHeader) === $providersForCountry($oppositeHeader)
            && $recordHeader['scope']['seed_policy'] === $oppositeHeader['scope']['seed_policy'];
    }

    /** @param list<array<string, mixed>> $examples @param array<string, mixed> $example @return list<array<string, mixed>> */
    private function addExample(array $examples, array $example): array
    {
        if (count($examples) < self::EXAMPLE_LIMIT) {
            $examples[] = $example;
        }

        return $examples;
    }
}
