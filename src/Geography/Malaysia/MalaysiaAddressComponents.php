<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Malaysia;

/**
 * Writer-side MY components behind the 2026-10-08 ruling (no city level).
 *
 * Partner: the subdivision area name when the subdivision slot is
 * town-like, else the daerah name; the entered city text only when
 * assignments say nothing at all (user-created addresses without area
 * picks still show their best-available geography instead of
 * degrading to state-only). Locality: the mukim/lokaliti assignment in
 * the rural case only — when the partner is a town, finer levels
 * collapse and the summary reads town + state. Without a locality
 * assignment the non-town subdivision name itself leads (the mukim IS
 * the locality, e.g. mukim Bagan). City text never feeds the locality
 * slot: beside a daerah partner that would be the postal town leak
 * this ruling removes.
 *
 * The masjid bulk-loader port (035_postcode_partner.sql) mirrors the
 * assignment branches in SQL but stays strict (NULL, no city fallback):
 * stored data distinguishes unknown for the subdivision-extraction todo
 * while display degrades gracefully. Keep the town-like set identical
 * in both places.
 */
final class MalaysiaAddressComponents
{
    public const array TOWN_LIKE_SUBDIVISION_TYPES = ['city', 'municipality', 'bandar', 'pekan'];

    /**
     * @return array{locality: ?string, postcode_partner: ?string}
     */
    public static function for(
        ?string $subdivisionName,
        ?string $subdivisionType,
        ?string $districtName,
        ?string $localityName,
        ?string $cityText = null,
    ): array {
        $subdivisionName = self::clean($subdivisionName);
        $districtName = self::clean($districtName);
        $localityName = self::clean($localityName);
        $cityText = self::clean($cityText);

        $townLike = $subdivisionName !== null
            && in_array(mb_strtolower(mb_trim((string) $subdivisionType)), self::TOWN_LIKE_SUBDIVISION_TYPES, true);

        if ($townLike) {
            return ['locality' => null, 'postcode_partner' => $subdivisionName];
        }

        return ['locality' => $localityName ?? $subdivisionName, 'postcode_partner' => $districtName ?? $cityText];
    }

    private static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
