<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Database\Factories\PostalCodeFactory;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $country_code
 * @property string $code
 * @property bool $is_active
 * @property array|null $metadata
 */
class PostalCode extends Model
{
    use HasFactory;
    use HasUuids;

    protected static function booted(): void
    {
        static::deleting(function (PostalCode $postalCode): void {
            ModelResolver::areaPostalCodeClass()::query()->where('postal_code_id', $postalCode->getKey())->delete();
        });
    }

    protected $fillable = [
        'country_code',
        'code',
        'is_active',
        'metadata',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('postal_codes');
    }

    /** @return BelongsToMany<AddressArea, $this, AddressAreaPostalCode, 'pivot'> */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(
            ModelResolver::areaClass(),
            AddressingTableResolver::resolve('area_postal_codes'),
            'postal_code_id',
            'address_area_id',
        )
            ->using(ModelResolver::areaPostalCodeClass())
            ->withPivot(['source', 'source_id', 'relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }

    protected static function newFactory(): PostalCodeFactory
    {
        return PostalCodeFactory::new();
    }
}
