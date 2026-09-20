<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\ResolveSingaporePostalCodesResultData;
use AIArmada\Addressing\Models\PostalCode;
use AIArmada\Addressing\Support\OneMapClient;
use AIArmada\Addressing\Support\OneMapPostalCodeSource;
use InvalidArgumentException;

final class ResolveSingaporePostalCodesAction
{
    public function __construct(
        private readonly OneMapClient $oneMap,
        private readonly ImportPostalCodesAction $importPostalCodes,
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
            $cached = PostalCode::query()
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

            $imported = PostalCode::query()
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

            if ($code !== '' && ! in_array($code, $codes, true)) {
                $codes[] = $code;
            }
        }

        return $codes;
    }
}
