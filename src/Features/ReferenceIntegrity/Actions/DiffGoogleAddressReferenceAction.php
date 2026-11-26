<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\ReferenceIntegrity\Actions;

use AIArmada\Addressing\Contracts\GoogleAddressReferenceSource;
use AIArmada\Addressing\Data\GoogleAddressReferenceDiffData;
use AIArmada\Addressing\Support\AddressValidationProfiles;
use InvalidArgumentException;

/**
 * Diff one country's validation profile against Google's address oracle.
 *
 * The Chromium i18n dataset is the upstream commerceguys mirrors for
 * require/upper/zip rules, so drift here means our profile disagrees with
 * the source it was transcribed from. This is a fetch-time oracle: nothing
 * is vendored, nothing is auto-applied — the command reports and a human
 * adjudicates (Google is sometimes the wrong one).
 */
final readonly class DiffGoogleAddressReferenceAction
{
    private const array LETTER_FIELDS = [
        'A' => 'line1',
        'C' => 'city',
        'S' => 'state',
        'Z' => 'postcode',
    ];

    public function __construct(private GoogleAddressReferenceSource $source) {}

    public function execute(string $countryCode): GoogleAddressReferenceDiffData
    {
        $code = mb_strtoupper(mb_trim($countryCode));
        if (! preg_match('/^[A-Z]{2}$/D', $code)) {
            throw new InvalidArgumentException('Country code must contain two ASCII letters.');
        }

        $data = $this->source->fetch($code);

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
