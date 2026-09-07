<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostalCode extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        static::deleting(function (PostalCode $postalCode): void {
            AddressAreaPostalCode::query()->where('postal_code_id', $postalCode->getKey())->delete();
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
            AddressArea::class,
            AddressingTableResolver::resolve('area_postal_codes'),
            'postal_code_id',
            'address_area_id',
        )
            ->using(AddressAreaPostalCode::class)
            ->withPivot(['source', 'source_id', 'relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }
}
