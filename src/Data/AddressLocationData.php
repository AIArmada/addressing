<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressLocationData
{
    public function __construct(
        public ?string $countryId = null,
        public ?string $stateId = null,
        public ?string $cityId = null,
        public ?string $adminArea1Id = null,
        public ?string $adminArea2Id = null,
        public ?string $adminArea3Id = null,
        public ?string $adminArea4Id = null,
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
            adminArea1Id: self::stringValue($attributes['admin_area_1_id'] ?? null),
            adminArea2Id: self::stringValue($attributes['admin_area_2_id'] ?? null),
            adminArea3Id: self::stringValue($attributes['admin_area_3_id'] ?? null),
            adminArea4Id: self::stringValue($attributes['admin_area_4_id'] ?? null),
        );
    }

    public function isEmpty(): bool
    {
        return $this->criteria() === [];
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
            'admin_area_1_id' => $this->adminArea1Id,
            'admin_area_2_id' => $this->adminArea2Id,
            'admin_area_3_id' => $this->adminArea3Id,
            'admin_area_4_id' => $this->adminArea4Id,
        ], static fn (?string $value): bool => $value !== null);
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
