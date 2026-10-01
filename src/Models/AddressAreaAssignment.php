<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressAreaAssignmentOwnerScope;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAreaAssignment extends Model
{
    use HasUuids;

    protected $fillable = ['address_id', 'address_area_id', 'role', 'is_primary', 'metadata'];

    protected static function booted(): void
    {
        static::addGlobalScope(new AddressAreaAssignmentOwnerScope);

        static::saving(function (AddressAreaAssignment $assignment): void {
            AddressOwnerGuard::assertAddressIsWritable($assignment->getAttribute('address_id'));

            if ($assignment->exists) {
                $originalAddressId = $assignment->getOriginal('address_id');

                if ($originalAddressId !== null
                    && (string) $originalAddressId !== (string) $assignment->getAttribute('address_id')) {
                    AddressOwnerGuard::assertAddressIsWritable($originalAddressId);
                }
            }
        });

        static::deleting(function (AddressAreaAssignment $assignment): void {
            AddressOwnerGuard::assertAddressIsWritable(
                $assignment->getOriginal('address_id') ?? $assignment->getAttribute('address_id')
            );
        });
    }

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('address_area_assignments');
    }

    /** @return BelongsTo<Address, $this> */
    public function address(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::addressClass());
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::areaClass(), 'address_area_id');
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'metadata' => 'array'];
    }
}
