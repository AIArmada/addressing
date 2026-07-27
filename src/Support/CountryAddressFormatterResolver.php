<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class CountryAddressFormatterResolver
{
    public function __construct(private readonly Container $container) {}

    public function resolve(?string $countryCode): ?CountryAddressFormatter
    {
        if ($countryCode === null || mb_trim($countryCode) === '') {
            return null;
        }

        $resolvedCode = mb_strtoupper(mb_trim($countryCode));

        foreach (config('addressing.formatters', []) as $formatterClass) {
            if (! is_string($formatterClass)) {
                throw new InvalidArgumentException('Addressing formatters must be class strings.');
            }

            $formatter = $this->container->make($formatterClass);

            if (! $formatter instanceof CountryAddressFormatter) {
                throw new InvalidArgumentException(sprintf(
                    '%s must implement %s.',
                    $formatterClass,
                    CountryAddressFormatter::class,
                ));
            }

            if (mb_strtoupper(mb_trim($formatter->countryCode())) === $resolvedCode) {
                return $formatter;
            }
        }

        return null;
    }
}
