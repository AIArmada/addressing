<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

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
     * @param  resource  $handle
     * @param  array<string, true>  $wanted
     * @return list<array<string, mixed>>
     */
    private static function readFiltered($handle, bool $gzip, array $wanted): array
    {
        $cities = [];
        $object = '';
        $depth = 0;
        $inString = false;
        $escaped = false;

        while (true) {
            $chunk = $gzip ? gzread($handle, 8192) : fread($handle, 8192);

            if ($chunk === false || $chunk === '') {
                break;
            }

            // Byte length: the loop below indexes raw bytes, not characters.
            $length = mb_strlen($chunk, '8bit');

            for ($index = 0; $index < $length; $index++) {
                $character = $chunk[$index];

                if ($object === '') {
                    if ($character !== '{') {
                        continue;
                    }

                    $object = '{';
                    $depth = 1;
                    $inString = false;
                    $escaped = false;

                    continue;
                }

                $object .= $character;

                if ($inString) {
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($character === '\\') {
                        $escaped = true;
                    } elseif ($character === '"') {
                        $inString = false;
                    }

                    continue;
                }

                if ($character === '"') {
                    $inString = true;
                } elseif ($character === '{') {
                    $depth++;
                } elseif ($character === '}') {
                    $depth--;
                }

                if ($depth !== 0) {
                    continue;
                }

                /** @var array<string, mixed> $city */
                $city = json_decode($object, true, 512, JSON_THROW_ON_ERROR);

                if (isset($wanted[$city['country_code'] ?? null])) {
                    $cities[] = $city;
                }

                $object = '';
            }
        }

        return $cities;
    }
}
