<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Casts;

use AIArmada\Addressing\Data\AddressData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class AddressDataCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $decoded = is_string($value) ? json_decode($value, true, flags: JSON_THROW_ON_ERROR) : $value;
        } catch (JsonException $e) {
            throw new RuntimeException(sprintf(
                'AddressDataCast: invalid JSON for [%s.%s]: %s',
                $model->getTable(),
                $key,
                $e->getMessage(),
            ));
        }

        if (! is_array($decoded)) {
            throw new RuntimeException(sprintf(
                'AddressDataCast: expected array or null for [%s.%s], got %s',
                $model->getTable(),
                $key,
                get_debug_type($value),
            ));
        }

        return AddressData::from($decoded);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof AddressData) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        throw new InvalidArgumentException(sprintf(
            'AddressDataCast: unsupported value type for [%s.%s]: %s. Expected AddressData, array, or null.',
            $model->getTable(),
            $key,
            get_debug_type($value),
        ));
    }
}
