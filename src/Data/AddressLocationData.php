<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressLocationData
{
    public function __construct(
        public ?string $countryId = null,
        public ?string $stateId = null,
        public ?string $cityId = null,
        /** @var array<string, string> */
        public array $areaAssignments = [],
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            countryId: self::stringValue($attributes['country_id'] ?? null),
            stateId: self::stringValue($attributes['state_id'] ?? null),
            cityId: self::stringValue($attributes['city_id'] ?? null),
            areaAssignments: self::assignmentValues($attributes['area_assignments'] ?? []),
        );
    }

    public function isEmpty(): bool
    {
        return $this->criteria() === [] && $this->areaAssignments === [];
    }

    /**
     * @return array<string, string>
     */
    public function criteria(): array
    {
        return array_filter([
            'country_id' => $this->countryId,
            'state_id' => $this->stateId,
            'city_id' => $this->cityId,
        ], static fn (?string $value): bool => $value !== null);
    }

    /**
     * @return array<string, string>
     */
    public function assignments(): array
    {
        return $this->areaAssignments;
    }

    /**
     * @return array<string, string>
     */
    private static function assignmentValues(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $assignments = [];

        foreach ($value as $role => $areaId) {
            if (! is_string($role) || ! is_string($areaId)) {
                continue;
            }

            $role = mb_trim($role);
            $areaId = mb_trim($areaId);

            if ($role !== '' && $areaId !== '') {
                $assignments[$role] = $areaId;
            }
        }

        return $assignments;
    }

    private static function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
