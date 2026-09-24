<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Generator;
use RuntimeException;

class CitySeedRowReader
{
    public function __construct(
        private string $jsonPath,
    ) {}

    /**
     * Filtered city rows for a seed run, or null for the full dataset.
     *
     * Production always seeds everything; outside production a configured
     * country list streams only those rows so local databases stay small.
     *
     * @return list<array<string, mixed>>|null
     */
    public function rowsForSeed(mixed $configuredCountryCodes, bool $production): ?array
    {
        if ($production) {
            return null;
        }

        if (! is_array($configuredCountryCodes)) {
            return null;
        }

        $codes = [];

        foreach ($configuredCountryCodes as $code) {
            $code = mb_strtoupper(mb_trim((string) $code));

            if ($code !== '') {
                $codes[$code] = true;
            }
        }

        if ($codes === []) {
            return null;
        }

        return $this->readForCountries(array_keys($codes));
    }

    /**
     * Stream city objects for the given ISO2 country codes.
     *
     * The bundled city dataset is 150k+ rows; streaming keeps filtered
     * non-production seeds small without loading the whole file. A `.gz`
     * sidecar next to the JSON path is preferred when present.
     *
     * @param  list<string>  $countryCodes
     * @return list<array<string, mixed>>
     */
    public function readForCountries(array $countryCodes): array
    {
        $wanted = [];

        foreach ($countryCodes as $code) {
            $code = mb_strtoupper(mb_trim((string) $code));

            if ($code !== '') {
                $wanted[$code] = true;
            }
        }

        if ($wanted === []) {
            return [];
        }

        $gzPath = $this->jsonPath . '.gz';

        if (file_exists($gzPath)) {
            $handle = @gzopen($gzPath, 'r');
            $gzip = true;
        } elseif (file_exists($this->jsonPath)) {
            $handle = @fopen($this->jsonPath, 'rb');
            $gzip = false;
        } else {
            throw new RuntimeException('Unable to read the address city seed data.');
        }

        if ($handle === false) {
            throw new RuntimeException('Unable to read the address city seed data.');
        }

        try {
            return self::readFiltered($handle, $gzip, $wanted);
        } finally {
            if ($gzip) {
                gzclose($handle);
            } else {
                fclose($handle);
            }
        }
    }

    /**
     * Read only matching city objects from the top-level JSON array.
     *
     * Batches are blanked of string literals up front (one PCRE pass, so
     * braces inside names and translations never read as structure), then
     * structural braces are located with C-speed span skips instead of a
     * per-byte PHP loop. Batches always end on a line boundary, and JSON
     * strings cannot contain raw newlines, so a batch never ends inside
     * a string.
     *
     * @param  resource  $handle
     * @param  array<string, true>  $wanted
     * @return list<array<string, mixed>>
     */
    private static function readFiltered($handle, bool $gzip, array $wanted): array
    {
        $cities = [];
        $object = '';
        $depth = 0;

        foreach (self::readBatches($handle, $gzip) as $batch) {
            // Equal-length blanking: offsets in $blanked must stay aligned
            // with $batch for the raw slices below.
            $blanked = preg_replace_callback(
                '/"(?:[^"\\\\]|\\\\.)*"/s',
                static fn (array $match): string => str_repeat(' ', mb_strlen($match[0], '8bit')),
                $batch,
            );
            $blanked = is_string($blanked) ? $blanked : $batch;
            $length = mb_strlen($blanked, '8bit');
            $position = 0;

            while ($position < $length) {
                $run = strcspn($blanked, '{}', $position);
                $next = $position + $run;

                if ($next >= $length) {
                    if ($depth > 0) {
                        $object .= mb_substr($batch, $position, null, '8bit');
                    }

                    break;
                }

                if ($depth > 0) {
                    $object .= mb_substr($batch, $position, $run + 1, '8bit');
                }

                if ($blanked[$next] === '{') {
                    if ($depth === 0) {
                        $object = '{';
                    }

                    $depth++;
                } else {
                    $depth--;

                    if ($depth === 0) {
                        /** @var array<string, mixed> $city */
                        $city = json_decode($object, true, 512, JSON_THROW_ON_ERROR);

                        if (isset($wanted[$city['country_code'] ?? null])) {
                            $cities[] = $city;
                        }

                        $object = '';
                    }
                }

                $position = $next + 1;
            }
        }

        return $cities;
    }

    /**
     * Read the stream in ~2MB batches that always end on a line boundary.
     *
     * @param  resource  $handle
     * @return Generator<int, string>
     */
    private static function readBatches($handle, bool $gzip): Generator
    {
        $carry = '';

        while (true) {
            $chunk = $gzip ? gzread($handle, 2097152) : fread($handle, 2097152);

            if ($chunk === false || $chunk === '') {
                break;
            }

            $chunk = $carry . $chunk;
            $boundary = mb_strrpos($chunk, "\n", 0, '8bit');

            if ($boundary === false) {
                $carry = $chunk;

                continue;
            }

            $carry = mb_substr($chunk, $boundary + 1, null, '8bit');

            yield mb_substr($chunk, 0, $boundary + 1, '8bit');
        }

        if ($carry !== '') {
            yield $carry;
        }
    }
}
