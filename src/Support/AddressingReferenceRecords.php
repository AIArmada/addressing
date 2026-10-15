<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\CommerceSupport\Models\Currency;
use AIArmada\CommerceSupport\Models\Timezone;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/** @internal */
final class AddressingReferenceRecords
{
    public function connection(): Connection
    {
        $countryClass = ModelResolver::countryClass();
        $connection = (new $countryClass)->getConnection();
        $modelClasses = [
            ModelResolver::stateClass(),
            ModelResolver::cityClass(),
            ModelResolver::areaClass(),
            ModelResolver::areaNameClass(),
            ModelResolver::areaRoleClass(),
            ModelResolver::areaRelationshipClass(),
            ModelResolver::areaStateLinkClass(),
            ModelResolver::postalCodeClass(),
            ModelResolver::areaPostalCodeClass(),
            Currency::class,
            Timezone::class,
        ];

        foreach ($modelClasses as $modelClass) {
            if ((new $modelClass)->getConnection()->getName() !== $connection->getName()) {
                throw new InvalidArgumentException(sprintf('Addressing reference model [%s] must share the country database connection.', $modelClass));
            }
        }

        return $connection;
    }

    /**
     * @param  list<string>  $countries
     * @return Generator<array<string, mixed>>
     */
    public function records(array $countries): Generator
    {
        foreach (ReferenceExportContract::TYPES as $type) {
            foreach ($this->rows($type) as $row) {
                $record = ReferenceExportContract::record($type, $row['key'], $row['data']);

                if ($countries !== [] && ! in_array($this->countryOf($record), $countries, true)) {
                    continue;
                }

                yield $record;
            }
        }
    }

    /** @return Generator<array{key: array<string, mixed>, data: array<string, mixed>}> */
    private function rows(string $type): Generator
    {
        switch ($type) {
            case 'countries':
                $query = $this->connection()->table(AddressingTableResolver::resolve('countries') . ' as c')
                    ->select(['c.iso2', 'c.iso3', 'c.numeric_code', 'c.name', 'c.phone_code', 'c.native', 'c.capital', 'c.region', 'c.subregion', 'c.tld', 'c.latitude', 'c.longitude', 'c.emoji', 'c.emojiU', 'c.translations']);
                foreach ($this->chunked($query, 'c.id') as $row) {
                    $code = (string) $row->iso2;
                    yield ['key' => ['country' => $code], 'data' => [
                        'iso2' => $code,
                        'iso3' => $row->iso3,
                        'numeric_code' => $row->numeric_code,
                        'name' => (string) $row->name,
                        'phone_code' => $row->phone_code,
                        'native' => $row->native,
                        'capital' => $row->capital,
                        'region' => $row->region,
                        'subregion' => $row->subregion,
                        'tld' => $row->tld,
                        'latitude' => $this->decimal($row->latitude),
                        'longitude' => $this->decimal($row->longitude),
                        'emoji' => $row->emoji,
                        'emojiU' => $row->emojiU,
                        'translations' => $this->translations($row->translations),
                    ]];
                }

                return;

            case 'states':
                $query = $this->connection()->table(AddressingTableResolver::resolve('states') . ' as s')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 's.country_id')
                    ->select(['s.country_id', 'c.id as resolved_country_id', 'c.iso2 as country_code', 's.code', 's.name', 's.latitude', 's.longitude']);
                foreach ($this->chunked($query, 's.id') as $row) {
                    $this->assertReference($row->country_id ?? null, $row->resolved_country_id ?? null, 'state country');
                    $country = (string) $row->country_code;
                    $key = ReferenceExportContract::stateKey($country, $row->code === null ? null : (string) $row->code, (string) $row->name);
                    yield ['key' => $key, 'data' => [
                        'country' => $country,
                        'code' => $row->code,
                        'name' => (string) $row->name,
                        'latitude' => $this->decimal($row->latitude),
                        'longitude' => $this->decimal($row->longitude),
                    ]];
                }

                return;

            case 'cities':
                $query = $this->connection()->table(AddressingTableResolver::resolve('cities') . ' as ci')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 'ci.country_id')
                    ->leftJoin(AddressingTableResolver::resolve('states') . ' as s', 's.id', '=', 'ci.state_id')
                    ->select(['c.iso2 as country_code', 'c.id as resolved_country_id', 'ci.country_id', 'ci.state_id', 's.id as resolved_state_id', 's.code as state_code', 's.name as state_name', 'ci.name', 'ci.latitude', 'ci.longitude']);
                foreach ($this->chunked($query, 'ci.id') as $row) {
                    $this->assertReference($row->country_id, $row->resolved_country_id, 'city country');
                    $this->assertOptionalReference($row->state_id, $row->resolved_state_id, 'city state');
                    $country = (string) $row->country_code;
                    $state = $row->state_code === null ? ($row->state_name === null ? null : ['name' => (string) $row->state_name]) : ['code' => (string) $row->state_code];
                    yield ['key' => ['country' => $country, 'state' => $state, 'name' => (string) $row->name], 'data' => [
                        'country' => $country,
                        'state' => $state,
                        'name' => (string) $row->name,
                        'latitude' => $this->decimal($row->latitude),
                        'longitude' => $this->decimal($row->longitude),
                    ]];
                }

                return;

            case 'areas':
                $query = $this->connection()->table(AddressingTableResolver::resolve('areas') . ' as a')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 'a.country_id')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as p', 'p.id', '=', 'a.parent_id')
                    ->select(['a.parent_id', 'p.id as resolved_parent_id', 'a.country_id', 'c.id as resolved_country_id', 'c.iso2 as resolved_country_code', 'a.country_code', 'a.source', 'a.source_id', 'a.type', 'a.level', 'a.name', 'a.native_name', 'a.code', 'a.is_active', 'a.latitude', 'a.longitude', 'p.country_code as parent_country_code', 'p.source as parent_source', 'p.source_id as parent_source_id']);
                foreach ($this->chunked($query, 'a.id') as $row) {
                    $this->assertOptionalReference($row->parent_id, $row->resolved_parent_id, 'area parent');
                    $this->assertOptionalReference($row->country_id, $row->resolved_country_id, 'area country');

                    if ($row->resolved_country_code !== null && $row->resolved_country_code !== $row->country_code) {
                        throw new InvalidArgumentException('Address area country identity disagrees with its country reference.');
                    }

                    $key = $this->areaKey($row->country_code, $row->source, $row->source_id);
                    $parent = $row->parent_source_id === null ? null : $this->areaKey($row->parent_country_code, $row->parent_source, $row->parent_source_id);
                    yield ['key' => $key, 'data' => [
                        'country' => (string) $row->country_code,
                        'source' => (string) $row->source,
                        'source_id' => (string) $row->source_id,
                        'type' => (string) $row->type,
                        'level' => $row->level === null ? null : (int) $row->level,
                        'name' => (string) $row->name,
                        'native_name' => $row->native_name,
                        'code' => $row->code,
                        'is_active' => $this->boolean($row->is_active),
                        'latitude' => $this->decimal($row->latitude),
                        'longitude' => $this->decimal($row->longitude),
                        'parent' => $parent,
                    ]];
                }

                return;

            case 'area_names':
                $query = $this->connection()->table(AddressingTableResolver::resolve('area_names') . ' as n')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as a', 'a.id', '=', 'n.address_area_id')
                    ->select(['n.address_area_id', 'a.id as resolved_area_id', 'a.country_code', 'a.source', 'a.source_id', 'n.name', 'n.source as name_source', 'n.name_type', 'n.is_preferred']);
                foreach ($this->chunked($query, 'n.id') as $row) {
                    $this->assertReference($row->address_area_id, $row->resolved_area_id, 'area name');
                    $area = $this->areaKey($row->country_code, $row->source, $row->source_id);
                    yield ['key' => ['area' => $area, 'name' => (string) $row->name, 'name_type' => (string) $row->name_type, 'source' => (string) $row->name_source], 'data' => [
                        'area' => $area,
                        'name' => (string) $row->name,
                        'source' => (string) $row->name_source,
                        'name_type' => (string) $row->name_type,
                        'is_preferred' => $this->boolean($row->is_preferred),
                    ]];
                }

                return;

            case 'area_roles':
                $query = $this->connection()->table(AddressingTableResolver::resolve('area_roles') . ' as r')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as a', 'a.id', '=', 'r.address_area_id')
                    ->select(['r.address_area_id', 'a.id as resolved_area_id', 'a.country_code', 'a.source', 'a.source_id', 'r.role', 'r.source as role_source', 'r.country_code as role_country_code', 'r.is_primary']);
                foreach ($this->chunked($query, 'r.id') as $row) {
                    $this->assertReference($row->address_area_id, $row->resolved_area_id, 'area role');

                    if ($row->country_code !== $row->role_country_code) {
                        throw new InvalidArgumentException('Address area role country disagrees with its area reference.');
                    }

                    $area = $this->areaKey($row->country_code, $row->source, $row->source_id);
                    yield ['key' => ['area' => $area, 'role' => (string) $row->role, 'country' => (string) $row->role_country_code, 'source' => (string) $row->role_source], 'data' => [
                        'area' => $area,
                        'role' => (string) $row->role,
                        'country' => (string) $row->role_country_code,
                        'source' => (string) $row->role_source,
                        'is_primary' => $this->boolean($row->is_primary),
                    ]];
                }

                return;

            case 'area_relationships':
                $query = $this->connection()->table(AddressingTableResolver::resolve('area_relationships') . ' as rel')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as p', 'p.id', '=', 'rel.parent_address_area_id')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as c', 'c.id', '=', 'rel.child_address_area_id')
                    ->select(['rel.parent_address_area_id', 'p.id as resolved_parent_id', 'rel.child_address_area_id', 'c.id as resolved_child_id', 'p.country_code as parent_country_code', 'p.source as parent_source', 'p.source_id as parent_source_id', 'c.country_code as child_country_code', 'c.source as child_source', 'c.source_id as child_source_id', 'rel.relationship_type', 'rel.hierarchy_type', 'rel.source', 'rel.valid_from', 'rel.valid_until']);
                foreach ($this->chunked($query, 'rel.id') as $row) {
                    $this->assertReference($row->parent_address_area_id, $row->resolved_parent_id, 'relationship parent area');
                    $this->assertReference($row->child_address_area_id, $row->resolved_child_id, 'relationship child area');
                    $parent = $this->areaKey($row->parent_country_code, $row->parent_source, $row->parent_source_id);
                    $child = $this->areaKey($row->child_country_code, $row->child_source, $row->child_source_id);
                    yield ['key' => ['parent' => $parent, 'child' => $child, 'relationship_type' => (string) $row->relationship_type, 'hierarchy_type' => (string) $row->hierarchy_type, 'source' => (string) $row->source], 'data' => [
                        'parent' => $parent,
                        'child' => $child,
                        'relationship_type' => (string) $row->relationship_type,
                        'hierarchy_type' => (string) $row->hierarchy_type,
                        'source' => (string) $row->source,
                        'valid_from' => $row->valid_from,
                        'valid_until' => $row->valid_until,
                    ]];
                }

                return;

            case 'area_state_links':
                $query = $this->connection()->table(AddressingTableResolver::resolve('area_state_links') . ' as l')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as a', 'a.id', '=', 'l.address_area_id')
                    ->leftJoin(AddressingTableResolver::resolve('states') . ' as s', 's.id', '=', 'l.state_id')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 's.country_id')
                    ->select(['l.address_area_id', 'a.id as resolved_area_id', 'l.state_id', 's.id as resolved_state_id', 's.country_id as state_country_id', 'c.id as resolved_country_id', 'a.country_code as area_country', 'a.source as area_source', 'a.source_id as area_source_id', 'c.iso2 as state_country', 's.code as state_code', 's.name as state_name', 'l.hierarchy_type']);
                foreach ($this->chunked($query, 'l.id') as $row) {
                    $this->assertReference($row->address_area_id, $row->resolved_area_id, 'state link area');
                    $this->assertReference($row->state_id, $row->resolved_state_id, 'state link state');
                    $this->assertReference($row->state_country_id, $row->resolved_country_id, 'state link country');
                    $area = $this->areaKey($row->area_country, $row->area_source, $row->area_source_id);
                    $state = ReferenceExportContract::stateKey((string) $row->state_country, $row->state_code === null ? null : (string) $row->state_code, (string) $row->state_name);
                    yield ['key' => ['area' => $area, 'state' => $state, 'hierarchy_type' => (string) $row->hierarchy_type], 'data' => [
                        'area' => $area,
                        'state' => $state,
                        'hierarchy_type' => (string) $row->hierarchy_type,
                    ]];
                }

                return;

            case 'postal_codes':
                $query = $this->connection()->table(AddressingTableResolver::resolve('postal_codes') . ' as pc')
                    ->select(['pc.country_code', 'pc.code', 'pc.is_active']);
                foreach ($this->chunked($query, 'pc.id') as $row) {
                    $country = (string) $row->country_code;
                    yield ['key' => ['country' => $country, 'code' => (string) $row->code], 'data' => ['country' => $country, 'code' => (string) $row->code, 'is_active' => $this->boolean($row->is_active)]];
                }

                return;

            case 'area_postal_codes':
                $query = $this->connection()->table(AddressingTableResolver::resolve('area_postal_codes') . ' as ap')
                    ->leftJoin(AddressingTableResolver::resolve('areas') . ' as a', 'a.id', '=', 'ap.address_area_id')
                    ->leftJoin(AddressingTableResolver::resolve('postal_codes') . ' as pc', 'pc.id', '=', 'ap.postal_code_id')
                    ->select(['ap.address_area_id', 'a.id as resolved_area_id', 'ap.postal_code_id', 'pc.id as resolved_postal_id', 'a.country_code as area_country', 'a.source as area_source', 'a.source_id as area_source_id', 'pc.country_code as postal_country', 'pc.code as postal_code', 'ap.source', 'ap.source_id', 'ap.relationship_type', 'ap.is_primary']);
                foreach ($this->chunked($query, 'ap.id') as $row) {
                    $this->assertReference($row->address_area_id, $row->resolved_area_id, 'postcode area');
                    $this->assertReference($row->postal_code_id, $row->resolved_postal_id, 'postcode row');
                    $area = $this->areaKey($row->area_country, $row->area_source, $row->area_source_id);

                    if ($row->postal_country !== $row->area_country) {
                        throw new InvalidArgumentException('Postcode coverage country disagrees with its area reference.');
                    }

                    $postal = ['country' => (string) $row->postal_country, 'code' => (string) $row->postal_code];
                    yield ['key' => ['area' => $area, 'postal_code' => $postal, 'relationship_type' => (string) $row->relationship_type, 'source' => (string) $row->source], 'data' => [
                        'area' => $area,
                        'postal_code' => $postal,
                        'source' => (string) $row->source,
                        'source_id' => $row->source_id,
                        'relationship_type' => (string) $row->relationship_type,
                        'is_primary' => $this->boolean($row->is_primary),
                    ]];
                }

                return;

            case 'country_currency_links':
                $currencyTable = (new Currency)->getTable();
                $query = $this->connection()->table(AddressingTableResolver::resolve('country_currency_links') . ' as l')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 'l.country_id')
                    ->leftJoin($currencyTable . ' as cur', 'cur.id', '=', 'l.currency_id')
                    ->select(['l.country_id', 'c.id as resolved_country_id', 'l.currency_id', 'cur.id as resolved_currency_id', 'c.iso2 as country_code', 'cur.code as currency_code']);
                foreach ($this->chunked($query, 'l.id') as $row) {
                    $this->assertReference($row->country_id, $row->resolved_country_id, 'currency link country');
                    $this->assertReference($row->currency_id, $row->resolved_currency_id, 'country currency');
                    $country = (string) $row->country_code;
                    $currency = (string) $row->currency_code;
                    yield ['key' => ['country' => $country, 'currency' => $currency], 'data' => ['country' => $country, 'currency' => $currency]];
                }

                return;

            case 'country_timezone_links':
                $timezoneTable = (new Timezone)->getTable();
                $query = $this->connection()->table(AddressingTableResolver::resolve('country_timezone_links') . ' as l')
                    ->leftJoin(AddressingTableResolver::resolve('countries') . ' as c', 'c.id', '=', 'l.country_id')
                    ->leftJoin($timezoneTable . ' as tz', 'tz.id', '=', 'l.timezone_id')
                    ->select(['l.country_id', 'c.id as resolved_country_id', 'l.timezone_id', 'tz.id as resolved_timezone_id', 'c.iso2 as country_code', 'tz.name as timezone_name']);
                foreach ($this->chunked($query, 'l.id') as $row) {
                    $this->assertReference($row->country_id, $row->resolved_country_id, 'timezone link country');
                    $this->assertReference($row->timezone_id, $row->resolved_timezone_id, 'country timezone');
                    $country = (string) $row->country_code;
                    $timezone = (string) $row->timezone_name;
                    yield ['key' => ['country' => $country, 'timezone' => $timezone], 'data' => ['country' => $country, 'timezone' => $timezone]];
                }

                return;
        }

        throw new RuntimeException(sprintf('No reference exporter exists for [%s].', $type));
    }

    /** @return array{country: string, source: string, source_id: string} */
    private function areaKey(mixed $country, mixed $source, mixed $sourceId): array
    {
        if (! is_string($country) || $country === '' || ! is_string($source) || $source === '' || ! is_string($sourceId) || $sourceId === '') {
            throw new InvalidArgumentException('A reference area is missing its natural source identity.');
        }

        return ['country' => $country, 'source' => $source, 'source_id' => $sourceId];
    }

    private function decimal(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('A geographic coordinate is not numeric.');
        }

        return number_format((float) $value, 7, '.', '');
    }

    private function boolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 't' || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'f' || $value === 'false') {
            return false;
        }

        throw new InvalidArgumentException('A reference boolean has an unsupported database representation.');
    }

    /** @return array<string, mixed>|null */
    private function translations(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            try {
                $value = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException('Country translations contain invalid JSON.', previous: $exception);
            }
        }

        if (! is_array($value) || ($value !== [] && array_is_list($value))) {
            throw new InvalidArgumentException('Country translations must be a language-keyed object or null.');
        }

        foreach ($value as $translation) {
            if (! is_string($translation)) {
                throw new InvalidArgumentException('Country translations must map language codes to strings.');
            }
        }

        return $value;
    }

    /** @return Generator<object> */
    private function chunked(Builder $query, string $idColumn): Generator
    {
        $query->addSelect($idColumn . ' as _export_id');
        $lastId = null;

        while (true) {
            $chunkQuery = clone $query;

            if ($lastId !== null) {
                $chunkQuery->where($idColumn, '>', $lastId);
            }

            $rows = $chunkQuery->orderBy($idColumn)->limit(1_000)->get();

            if ($rows->isEmpty()) {
                return;
            }

            foreach ($rows as $row) {
                yield $row;
            }

            $lastId = (string) $rows->last()->_export_id;
        }
    }

    private function assertReference(mixed $id, mixed $resolvedId, string $label): void
    {
        if ($id === null || $resolvedId === null || (string) $id !== (string) $resolvedId) {
            throw new InvalidArgumentException(sprintf('Dangling %s reference encountered in addressing reference data.', $label));
        }
    }

    private function assertOptionalReference(mixed $id, mixed $resolvedId, string $label): void
    {
        if ($id === null && $resolvedId === null) {
            return;
        }

        $this->assertReference($id, $resolvedId, $label);
    }

    /** @param array<string, mixed> $record */
    private function countryOf(array $record): string
    {
        $key = $record['key'];

        return match ($record['type']) {
            'countries', 'states', 'cities', 'areas', 'postal_codes', 'country_currency_links', 'country_timezone_links' => (string) $key['country'],
            'area_names', 'area_roles', 'area_state_links', 'area_postal_codes' => (string) $key['area']['country'],
            'area_relationships' => (string) $key['parent']['country'],
            default => throw new InvalidArgumentException(sprintf('Unsupported addressing reference type [%s].', $record['type'])),
        };
    }
}
