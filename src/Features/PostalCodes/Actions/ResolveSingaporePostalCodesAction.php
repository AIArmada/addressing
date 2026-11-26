<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Actions;

use AIArmada\Addressing\Contracts\PostalAddressLookup;
use AIArmada\Addressing\Contracts\PostalCodeImporter;
use AIArmada\Addressing\Contracts\SingaporePostalCodeResolver;
use AIArmada\Addressing\Data\ResolveSingaporePostalCodesResultData;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\OneMapPostalCodeSource;
use InvalidArgumentException;

final readonly class ResolveSingaporePostalCodesAction implements SingaporePostalCodeResolver
{
    public function __construct(
        private PostalAddressLookup $oneMap,
        private PostalCodeImporter $importPostalCodes,
    ) {}

    /**
     * @param  list<string>  $postcodes
     */
    public function execute(array $postcodes): ResolveSingaporePostalCodesResultData
    {
        $resolved = [];
        $invalid = [];
        $missing = [];

        foreach ($this->normalize($postcodes) as $code) {
            if (! preg_match('/^\d{6}$/', $code)) {
                $invalid[] = $code;

                continue;
            }

            $missing[] = $code;
        }

        if ($missing !== []) {
            $cached = ModelResolver::postalCodeClass()::query()
                ->where('country_code', 'SG')
                ->whereIn('code', $missing)
                ->where('is_active', true)
                ->pluck('code')
                ->map(static fn (mixed $code): string => mb_strtoupper((string) $code))
                ->flip()
                ->all();
            $resolved = array_values(array_filter(
                $missing,
                static fn (string $code): bool => isset($cached[$code]),
            ));
            $missing = array_values(array_filter(
                $missing,
                static fn (string $code): bool => ! isset($cached[$code]),
            ));
        }

        if ($missing !== []) {
            $result = $this->importPostalCodes->execute(new OneMapPostalCodeSource($this->oneMap, $missing));

            if ($result->hasFailures()) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot resolve Singapore postcodes because %d rows failed: %s',
                    count($result->failures),
                    $result->failures[0]->reason,
                ));
            }

            $imported = ModelResolver::postalCodeClass()::query()
                ->where('country_code', 'SG')
                ->whereIn('code', $missing)
                ->where('is_active', true)
                ->pluck('code')
                ->map(static fn (mixed $code): string => mb_strtoupper((string) $code))
                ->flip()
                ->all();

            foreach ($missing as $code) {
                if (isset($imported[$code])) {
                    $resolved[] = $code;
                } else {
                    $invalid[] = $code;
                }
            }
        }

        return new ResolveSingaporePostalCodesResultData($resolved, $invalid);
    }

    /**
     * @param  list<string>  $postcodes
     * @return list<string>
     */
    private function normalize(array $postcodes): array
    {
        $codes = [];

        foreach ($postcodes as $postcode) {
            $code = mb_strtoupper(mb_trim((string) $postcode));

            if ($code !== '') {
                $codes['postcode:' . $code] = $code;
            }
        }

        return array_values($codes);
    }
}
