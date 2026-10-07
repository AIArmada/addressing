<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\GoogleAddressReferenceDiffData;
use AIArmada\Addressing\Support\AddressValidationProfiles;
use Closure;
use JsonException;
use RuntimeException;

/**
 * Diff one country's validation profile against Google's address oracle.
 *
 * The Chromium i18n dataset is the upstream commerceguys mirrors for
 * require/upper/zip rules, so drift here means our profile disagrees with
 * the source it was transcribed from. This is a fetch-time oracle: nothing
 * is vendored, nothing is auto-applied — the command reports and a human
 * adjudicates (Google is sometimes the wrong one).
 *
 * The fetcher is injectable so tests never touch the network; the default
 * fetcher is a plain file_get_contents call, keeping this package free of
 * HTTP client dependencies.
 */
final class DiffGoogleAddressReferenceAction
{
    public const string ENDPOINT = 'https://chromium-i18n.appspot.com/ssl-address/data/';

    private const array LETTER_FIELDS = [
        'A' => 'line1',
        'C' => 'city',
        'S' => 'state',
        'Z' => 'postcode',
    ];

    /**
     * @param  (Closure(string): array<string, mixed>)|null  $fetcher
     */
    public function __construct(
        private readonly ?Closure $fetcher = null,
    ) {}

    public function execute(string $countryCode): GoogleAddressReferenceDiffData
    {
        $code = mb_strtoupper(mb_trim($countryCode));
        $data = $this->fetch($code);

        if ($data === []) {
            return new GoogleAddressReferenceDiffData($code, googleSilent: true);
        }

        $profile = AddressValidationProfiles::forCountry($code);
        $drifts = [];

        if ($profile === null) {
            $drifts[] = [
                'dimension' => 'profile',
                'ours' => '(unprofiled)',
                'theirs' => 'Google describes this country',
            ];

            return new GoogleAddressReferenceDiffData($code, $drifts, $this->format($data));
        }

        $require = isset($data['require']) && is_string($data['require']) ? $data['require'] : '';
        $googleRequired = $this->fields($require);
        $missing = array_diff($googleRequired, $profile->required);
        $extra = array_diff($profile->required, $googleRequired);

        if ($missing !== [] || $extra !== []) {
            $drifts[] = [
                'dimension' => 'required',
                'ours' => $this->describe($profile->required),
                'theirs' => $this->describe($googleRequired),
            ];
        }

        if (str_contains(mb_strtoupper($require), 'D')) {
            $drifts[] = [
                'dimension' => 'required-suburb',
                'ours' => '(not modeled)',
                'theirs' => 'Google requires the dependent locality',
            ];
        }

        $googleUpper = $this->fields(isset($data['upper']) && is_string($data['upper']) ? $data['upper'] : '');

        if (array_diff($googleUpper, $profile->upper) !== [] || array_diff($profile->upper, $googleUpper) !== []) {
            $drifts[] = [
                'dimension' => 'upper',
                'ours' => $this->describe($profile->upper),
                'theirs' => $this->describe($googleUpper),
            ];
        }

        $googleZip = isset($data['zip']) && is_string($data['zip']) && mb_trim($data['zip']) !== ''
            ? mb_trim($data['zip'])
            : null;

        if ($googleZip !== null && $profile->pattern === null) {
            $drifts[] = ['dimension' => 'pattern', 'ours' => '(none)', 'theirs' => $googleZip];
        } elseif ($googleZip === null && $profile->pattern !== null) {
            $drifts[] = ['dimension' => 'pattern', 'ours' => $profile->pattern, 'theirs' => '(Google has no zip rule)'];
        } elseif ($googleZip !== null && $profile->pattern !== null) {
            $rejected = [];
            $examples = isset($data['zipex']) && is_string($data['zipex']) ? $data['zipex'] : '';

            foreach (explode(',', $examples) as $example) {
                $example = mb_trim($example);

                if ($example !== '' && ! AddressValidationProfiles::matchesPattern($profile->pattern, $example)) {
                    $rejected[] = $example;
                }
            }

            if ($rejected !== []) {
                $drifts[] = [
                    'dimension' => 'pattern-examples',
                    'ours' => $profile->pattern,
                    'theirs' => 'rejects: ' . implode(', ', $rejected),
                ];
            }
        }

        return new GoogleAddressReferenceDiffData($code, $drifts, $this->format($data));
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(string $code): array
    {
        if ($this->fetcher !== null) {
            return ($this->fetcher)($code);
        }

        $context = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
        $raw = @file_get_contents(self::ENDPOINT . $code, false, $context);

        if ($raw === false) {
            throw new RuntimeException("Could not fetch Google address data for [{$code}].");
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Google address data for [{$code}] is not valid JSON.", previous: $e);
        }

        if (! is_array($data)) {
            throw new RuntimeException("Google address data for [{$code}] has an unexpected shape.");
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    private function fields(string $letters): array
    {
        $fields = [];

        foreach (mb_str_split(mb_strtoupper($letters)) as $letter) {
            if (isset(self::LETTER_FIELDS[$letter])) {
                $fields[] = self::LETTER_FIELDS[$letter];
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * @param  list<string>  $fields
     */
    private function describe(array $fields): string
    {
        return $fields === [] ? '(none)' : implode(',', $fields);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function format(array $data): ?string
    {
        return isset($data['fmt']) && is_string($data['fmt']) ? $data['fmt'] : null;
    }
}
