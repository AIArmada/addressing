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

        return $this->collectChunks($this->readChunksForCountries(array_keys($wanted)));
    }

    /**
     * Read every city in bounded chunks for production seeding.
     *
     * @return Generator<int, list<array<string, mixed>>>
     */
    public function readAllChunks(int $chunkSize = 2000): Generator
    {
        yield from $this->readChunks(null, $chunkSize);
    }

    /**
     * Stream city rows for the given ISO2 country codes in bounded chunks.
     *
     * @param  list<string>  $countryCodes
     * @return Generator<int, list<array<string, mixed>>>
     */
    public function readChunksForCountries(array $countryCodes, int $chunkSize = 2000): Generator
    {
        $wanted = [];

        foreach ($countryCodes as $code) {
            $code = mb_strtoupper(mb_trim((string) $code));

            if ($code !== '') {
                $wanted[$code] = true;
            }
        }

        if ($wanted === []) {
            return;
        }

        yield from $this->readChunks($wanted, $chunkSize);
    }

    /**
     * @param  array<string, true>|null  $wanted
     * @return Generator<int, list<array<string, mixed>>>
     */
    private function readChunks(?array $wanted, int $chunkSize): Generator
    {
        if ($chunkSize < 1) {
            throw new RuntimeException('City seed chunk size must be positive.');
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
            yield from self::readFilteredChunks($handle, $gzip, $wanted, $chunkSize);
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
     * @param  array<string, true>|null  $wanted
     * @return Generator<int, list<array<string, mixed>>>
     */
    private static function readFilteredChunks($handle, bool $gzip, ?array $wanted, int $chunkSize): Generator
    {
        $cities = [];
        $object = '';
        $depth = 0;
        $started = false;
        $expectObject = false;
        $allowArrayEnd = false;
        $ended = false;

        foreach (self::readBatches($handle, $gzip) as $batch) {
            // Equal-length blanking: offsets in $blanked must stay aligned
            // with $batch for the raw slices below.
            $blanked = preg_replace_callback(
                '/"(?:[^"\\\\]|\\\\.)*"/s',
                static fn (array $match): string => str_repeat(' ', mb_strlen($match[0], '8bit')),
                $batch,
            );

            if (! is_string($blanked)) {
                throw new RuntimeException('Unable to parse the address city seed data.');
            }
            $length = mb_strlen($blanked, '8bit');
            $position = 0;

            while ($position < $length) {
                if ($depth === 0) {
                    $position += strspn($blanked, " \t\r\n", $position);

                    if ($position >= $length) {
                        break;
                    }

                    $token = $blanked[$position];

                    if (! $started) {
                        if ($token !== '[') {
                            throw new RuntimeException('Address city seed data must be a JSON array.');
                        }

                        $started = true;
                        $expectObject = true;
                        $allowArrayEnd = true;
                        $position++;

                        continue;
                    }

                    if ($ended) {
                        throw new RuntimeException('Unexpected content after the address city seed array.');
                    }

                    if ($expectObject) {
                        if ($token === '{') {
                            $object = '{';
                            $depth = 1;
                            $expectObject = false;
                            $allowArrayEnd = false;
                            $position++;

                            continue;
                        }

                        if ($token === ']' && $allowArrayEnd) {
                            $ended = true;
                            $position++;

                            continue;
                        }

                        throw new RuntimeException('Expected an address city object in the seed array.');
                    }

                    if ($token === ',') {
                        $expectObject = true;
                        $allowArrayEnd = false;
                        $position++;

                        continue;
                    }

                    if ($token === ']') {
                        $ended = true;
                        $position++;

                        continue;
                    }

                    throw new RuntimeException('Expected a separator in the address city seed array.');
                }

                $run = strcspn($blanked, '{}', $position);
                $next = $position + $run;

                if ($next >= $length) {
                    $object .= mb_substr($batch, $position, null, '8bit');

                    break;
                }

                $object .= mb_substr($batch, $position, $run + 1, '8bit');

                if ($blanked[$next] === '{') {
                    $depth++;
                } else {
                    $depth--;

                    if ($depth === 0) {
                        /** @var array<string, mixed> $city */
                        $city = json_decode($object, true, 512, JSON_THROW_ON_ERROR);

                        if ($wanted === null || isset($wanted[$city['country_code'] ?? null])) {
                            $cities[] = $city;

                            if (count($cities) >= $chunkSize) {
                                yield $cities;
                                $cities = [];
                            }
                        }

                        $object = '';
                        $allowArrayEnd = true;
                    }
                }

                $position = $next + 1;
            }
        }

        if (! $started || ! $ended || $depth !== 0 || $object !== '') {
            throw new RuntimeException('Address city seed data is incomplete.');
        }

        if ($cities !== []) {
            yield $cities;
        }
    }

    /** @param iterable<list<array<string, mixed>>> $chunks
     * @return list<array<string, mixed>>
     */
    private function collectChunks(iterable $chunks): array
    {
        $rows = [];

        foreach ($chunks as $chunk) {
            array_push($rows, ...$chunk);
        }

        return $rows;
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
