<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class CountryAddressFormatterResolver
{
    /**
     * @var array<string, class-string<CountryAddressFormatter>>|null
     */
    private ?array $formatterMap = null;

    /**
     * @var array<array-key, mixed>|null
     */
    private ?array $configuredFormatters = null;

    public function __construct(private readonly Container $container) {}

    public function resolve(?string $countryCode): ?CountryAddressFormatter
    {
        if ($countryCode === null || mb_trim($countryCode) === '') {
            return null;
        }

        $formatterClass = $this->formatterMap()[mb_strtoupper(mb_trim($countryCode))] ?? null;

        if ($formatterClass === null) {
            return null;
        }

        $formatter = $this->container->make($formatterClass);

        if (! $formatter instanceof CountryAddressFormatter) {
            throw new InvalidArgumentException(sprintf(
                '%s must implement %s.',
                $formatterClass,
                CountryAddressFormatter::class,
            ));
        }

        return $formatter;
    }

    /**
     * @return array<string, class-string<CountryAddressFormatter>>
     */
    private function formatterMap(): array
    {
        $configured = config('addressing.formatters', []);

        if (! is_array($configured)) {
            throw new InvalidArgumentException('Addressing formatters must be class strings.');
        }

        if ($this->formatterMap !== null && $this->configuredFormatters === $configured) {
            return $this->formatterMap;
        }

        /** @var array<string, class-string<CountryAddressFormatter>> $map */
        $map = [];

        foreach ($configured as $formatterClass) {
            if (! is_string($formatterClass)) {
                throw new InvalidArgumentException('Addressing formatters must be class strings.');
            }

            if (! is_a($formatterClass, CountryAddressFormatter::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    '%s must implement %s.',
                    $formatterClass,
                    CountryAddressFormatter::class,
                ));
            }

            $map[mb_strtoupper(mb_trim($formatterClass::countryCode()))] ??= $formatterClass;
        }

        $this->configuredFormatters = $configured;
        $this->formatterMap = $map;

        return $map;
    }
}
