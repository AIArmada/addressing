<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

final readonly class GooglePlaceAddressComponents
{
    /** @var list<array{longText: string|null, shortText: string|null, types: list<string>}> */
    private array $components;

    public function __construct(mixed $components)
    {
        $this->components = $this->normalizeAddressComponents($components);
    }

    /**
     * @return list<array{longText: string|null, shortText: string|null, types: list<string>}>
     */
    private function normalizeAddressComponents(mixed $components): array
    {
        if (! is_array($components)) {
            return [];
        }

        return collect($components)
            ->map(function (mixed $component): ?array {
                if (! is_array($component)) {
                    return null;
                }

                $types = $component['types'] ?? null;

                if (! is_array($types)) {
                    return null;
                }

                return [
                    'longText' => $this->stringValue($component['longText'] ?? null),
                    'shortText' => $this->stringValue($component['shortText'] ?? null),
                    'types' => array_values(array_filter(
                        array_map(
                            static fn (mixed $type): ?string => is_string($type) && $type !== '' ? $type : null,
                            $types,
                        ),
                        static fn (?string $type): bool => $type !== null,
                    )),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $types
     */
    public function value(array $types): ?string
    {
        foreach ($this->components as $component) {
            foreach ($types as $type) {
                if (! in_array($type, $component['types'], true)) {
                    continue;
                }

                return $component['longText'] ?? $component['shortText'];
            }
        }

        return null;
    }

    /**
     * Deduplicated component values in mapping order.
     *
     * @param  list<string>  $types
     * @return list<string>
     */
    public function candidateNames(array $types): array
    {
        $names = [];

        foreach ($types as $type) {
            $value = $this->value([$type]);

            if (is_string($value) && $value !== '' && ! in_array($value, $names, true)) {
                $names[] = $value;
            }
        }

        return $names;
    }

    /**
     * @param  list<string>  $types
     * @return array{longText: string|null, shortText: string|null, types: list<string>}|null
     */
    public function component(array $types): ?array
    {
        foreach ($this->components as $component) {
            foreach ($types as $type) {
                if (! in_array($type, $component['types'], true)) {
                    continue;
                }

                return $component;
            }
        }

        return null;
    }

    public function line1(?string $displayName): ?string
    {
        $streetNumber = $this->value(['street_number']);
        $route = $this->value(['route']);
        $premise = $this->value(['premise']);

        if ($premise !== null && $displayName !== null && $this->normalizeLineComponent($premise) === $this->normalizeLineComponent($displayName)) {
            $premise = null;
        }

        return $this->firstFilled([
            $this->joinParts([$streetNumber, $route]),
            $route,
            $premise,
            $this->value(['sublocality_level_1']),
            $this->value(['sublocality_level_2']),
            $this->value(['sublocality']),
            $this->value(['neighborhood']),
        ]);
    }

    public function line2(): ?string
    {
        return $this->firstFilled([
            $this->value(['subpremise']),
        ]);
    }

    private function normalizeLineComponent(string $value): string
    {
        $normalized = preg_replace('/\s+/u', ' ', mb_trim($value));

        return mb_strtolower(is_string($normalized) ? $normalized : mb_trim($value));
    }

    /**
     * @param  list<?string>  $values
     */
    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $value = $this->stringValue($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  list<?string>  $parts
     */
    private function joinParts(array $parts): ?string
    {
        $parts = array_values(array_filter(
            array_map($this->stringValue(...), $parts),
            static fn (?string $part): bool => $part !== null,
        ));

        if ($parts === []) {
            return null;
        }

        return implode(' ', $parts);
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
