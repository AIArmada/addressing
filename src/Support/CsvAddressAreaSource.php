<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\AddressAreaData;
use Generator;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use SplFileObject;

class CsvAddressAreaSource implements AddressAreaSource
{
    private readonly string $path;

    private readonly string $sourceKey;

    public function __construct(string $path, string $sourceKey)
    {
        if (! file_exists($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("CSV file not found or not readable: {$path}");
        }

        $this->path = $path;
        $this->sourceKey = $sourceKey;
    }

    public function key(): string
    {
        return $this->sourceKey;
    }

    public function areas(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            $file = new SplFileObject($this->path, 'r');
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
            $file->setCsvControl(',');

            $headers = $file->fgetcsv();

            if ($headers === false) {
                throw new InvalidArgumentException('CSV file is empty or has no headers.');
            }

            $headers = array_map('mb_trim', $headers);

            while (! $file->eof()) {
                $row = $file->fgetcsv();

                if ($row === false || $row === [null]) {
                    continue;
                }

                if (count($headers) !== count($row)) {
                    continue;
                }

                $data = array_combine($headers, $row);

                yield new AddressAreaData(
                    source: $this->sourceKey,
                    sourceId: mb_trim((string) ($data['source_id'] ?? '')),
                    countryCode: mb_trim((string) ($data['country_code'] ?? '')),
                    type: mb_trim((string) ($data['type'] ?? '')),
                    name: mb_trim((string) ($data['name'] ?? '')),
                    nativeName: self::nullableString($data['native_name'] ?? null),
                    code: self::nullableString($data['code'] ?? null),
                    parentSourceId: self::nullableString($data['parent_source_id'] ?? null),
                    level: self::nullableInt($data['level'] ?? null),
                    latitude: self::nullableFloat($data['latitude'] ?? null),
                    longitude: self::nullableFloat($data['longitude'] ?? null),
                );
            }
        });
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        $trimmed = mb_trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}
