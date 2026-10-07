<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\CldrSubdivisionDiffData;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Diff states.json subdivision codes against a CLDR checkout.
 *
 * Unicode CLDR is the independent oracle for ISO 3166-2 subdivision codes
 * and English names. The diff reports three drift classes per country:
 * missing codes (CLDR lists, we lack), stale codes (we list, CLDR
 * dropped), and renames (same code, different name). Report-only: a human
 * adjudicates, because either side can lag a real-world change.
 *
 * The CLDR path is a local unicode-org/cldr-json checkout — the directory
 * that directly contains cldr-core/ and cldr-subdivisions-full/ — so the
 * oracle stays reproducible offline. When addressing.reference.cldr_version
 * is set, checkouts at any other version are refused.
 */
final class DiffCldrSubdivisionsAction
{
    public function execute(?string $countryCode = null, ?string $cldrPath = null): CldrSubdivisionDiffData
    {
        $code = $countryCode !== null ? mb_strtoupper(mb_trim($countryCode)) : null;

        if ($code === '') {
            throw new InvalidArgumentException('Country code must not be blank.');
        }

        $root = mb_trim((string) ($cldrPath ?? config('addressing.reference.cldr_path', '')));

        if ($root === '') {
            throw new InvalidArgumentException('Pass --cldr with a cldr-json checkout path or set addressing.reference.cldr_path.');
        }

        $version = $this->checkoutVersion($root);

        $pinned = config('addressing.reference.cldr_version');

        if (is_string($pinned) && mb_trim($pinned) !== '' && mb_trim($pinned) !== $version) {
            throw new RuntimeException("CLDR checkout is version [{$version}] but addressing.reference.cldr_version pins [{$pinned}].");
        }

        $subdivisions = $this->subdivisions($root, $code);
        $ours = $this->ourStates($code);

        $drifts = [];

        foreach ($ours['coded'] as $key => $name) {
            if (! isset($subdivisions[$key])) {
                $drifts[] = [
                    'country' => mb_strtoupper(mb_substr($key, 0, 2)),
                    'dimension' => 'stale-code',
                    'code' => $key,
                    'ours' => $name,
                    'theirs' => '(dropped by CLDR)',
                ];

                continue;
            }

            if (mb_strtolower($name) !== mb_strtolower($subdivisions[$key])) {
                $drifts[] = [
                    'country' => mb_strtoupper(mb_substr($key, 0, 2)),
                    'dimension' => 'renamed',
                    'code' => $key,
                    'ours' => $name,
                    'theirs' => $subdivisions[$key],
                ];
            }
        }

        foreach ($subdivisions as $key => $name) {
            if (! isset($ours['coded'][$key])) {
                $drifts[] = [
                    'country' => mb_strtoupper(mb_substr($key, 0, 2)),
                    'dimension' => 'missing-code',
                    'code' => $key,
                    'ours' => '(not in states.json)',
                    'theirs' => $name,
                ];
            }
        }

        usort($drifts, static fn (array $a, array $b): int => [$a['country'], $a['code']] <=> [$b['country'], $b['code']]);

        return new CldrSubdivisionDiffData($code, $drifts, $version, $ours['nullCodes']);
    }

    private function checkoutVersion(string $root): string
    {
        $packageFile = $root . '/cldr-core/package.json';

        if (! is_file($packageFile)) {
            throw new InvalidArgumentException("No cldr-core/package.json under [{$root}]; point --cldr at the directory containing cldr-core/.");
        }

        $package = $this->readJson($packageFile);
        $version = isset($package['version']) && is_string($package['version']) ? mb_trim($package['version']) : '';

        if ($version === '') {
            throw new RuntimeException("cldr-core/package.json under [{$root}] has no version.");
        }

        return $version;
    }

    /**
     * @return array<string, string> Hyphenless lowercase code to English name.
     */
    private function subdivisions(string $root, ?string $code): array
    {
        $file = $root . '/cldr-subdivisions-full/subdivisions/en/en.json';

        if (! is_file($file)) {
            throw new InvalidArgumentException("No subdivisions/en/en.json under [{$root}]; the checkout needs cldr-subdivisions-full.");
        }

        $names = $this->readJson($file)['subdivisions']['localeDisplayNames']['subdivisions'] ?? null;

        if (! is_array($names)) {
            throw new RuntimeException("Subdivisions file under [{$root}] has an unexpected shape.");
        }

        $subdivisions = [];

        foreach ($names as $key => $name) {
            if (! is_string($key) || ! is_string($name)) {
                continue;
            }

            $key = mb_strtolower($key);

            if ($code !== null && mb_strtoupper(mb_substr($key, 0, 2)) !== $code) {
                continue;
            }

            $subdivisions[$key] = $name;
        }

        return $subdivisions;
    }

    /**
     * @return array{coded: array<string, string>, nullCodes: int}
     */
    private function ourStates(?string $code): array
    {
        $rows = $this->readJson(__DIR__ . '/../../resources/data/states.json');
        $coded = [];
        $nullCodes = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            if ($code !== null && mb_strtoupper((string) ($row['country_code'] ?? '')) !== $code) {
                continue;
            }

            $iso = isset($row['iso3166_2']) && is_string($row['iso3166_2']) ? mb_trim($row['iso3166_2']) : '';

            if ($iso === '') {
                $nullCodes++;

                continue;
            }

            $coded[mb_strtolower(str_replace('-', '', $iso))] = (string) ($row['name'] ?? '');
        }

        return ['coded' => $coded, 'nullCodes' => $nullCodes];
    }

    /**
     * @return array<mixed>
     */
    private function readJson(string $file): array
    {
        try {
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Could not parse [{$file}].", previous: $e);
        }

        if (! is_array($data)) {
            throw new RuntimeException("File [{$file}] has an unexpected shape.");
        }

        return $data;
    }
}
