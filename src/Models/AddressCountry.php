<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Database\Factories\AddressCountryFactory;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Models\Currency;
use AIArmada\CommerceSupport\Models\Timezone;
use DomainException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property string $id
 * @property string $iso2
 * @property string $name
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $numeric_code
 * @property string|null $native
 * @property string|null $capital
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $tld
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 * @property array|null $translations
 */
class AddressCountry extends Model
{
    use HasFactory;
    use HasUuids;

    protected static function booted(): void
    {
        static::deleting(static function (AddressCountry $country): void {
            $countryId = $country->getKey();

            $referenced = ModelResolver::stateClass()::query()->where('country_id', $countryId)->exists()
                || ModelResolver::cityClass()::query()->where('country_id', $countryId)->exists()
                || ModelResolver::areaClass()::query()->where('country_id', $countryId)->exists()
                || ModelResolver::addressClass()::query()->withoutOwnerScope()->where('country_id', $countryId)->exists();

            if ($referenced) {
                throw new DomainException('Countries with states, cities, areas, or addresses cannot be deleted.');
            }

            $country->currencies()->detach();
            $country->timezones()->detach();
        });
    }

    public function delete(): ?bool
    {
        return DB::transaction(fn (): ?bool => parent::delete());
    }

    protected $fillable = [
        'iso2',
        'name',
        'phone_code',
        'iso3',
        'numeric_code',
        'native',
        'capital',
        'region',
        'subregion',
        'tld',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
        'translations',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('countries');
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<Currency, $this>
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, AddressingTableResolver::resolve('country_currency_links'), 'country_id', 'currency_id');
    }

    /**
     * @return BelongsToMany<Timezone, $this>
     */
    public function timezones(): BelongsToMany
    {
        return $this->belongsToMany(Timezone::class, AddressingTableResolver::resolve('country_timezone_links'), 'country_id', 'timezone_id');
    }

    /**
     * @return HasMany<State, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(ModelResolver::stateClass(), 'country_id');
    }

    protected static function newFactory(): AddressCountryFactory
    {
        return AddressCountryFactory::new();
    }
}
