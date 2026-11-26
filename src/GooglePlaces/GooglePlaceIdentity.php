<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

final class GooglePlaceIdentity
{
    /**
     * @param  array<string, mixed>  $place
     * @return array{
     *     google_place_id: string,
     *     google_feature_id: string|null,
     *     google_cid: string|null,
     *     google_entity_id: string|null,
     *     components: array<int, mixed>,
     *     formatted_address: string|null,
     *     metadata: array<string, mixed>,
     *     navigation_links: array<string, array{url: string, source: string}>
     * }
     */
    public static function fromPlace(array $place): array
    {
        $links = is_array($place['googleMapsLinks'] ?? null) ? $place['googleMapsLinks'] : [];
        $urls = [];

        foreach ($links as $key => $url) {
            if (is_string($url) && $url !== '') {
                $urls[(string) $key] = $url;
            }
        }

        $googleMapsUri = $place['googleMapsUri'] ?? null;

        if (is_string($googleMapsUri) && $googleMapsUri !== '') {
            $urls['googleMapsUri'] = $googleMapsUri;
        }

        $featureIds = [];
        $cids = [];

        foreach ($urls as $url) {
            preg_match_all('/!1s(0x[0-9a-f]+:0x[0-9a-f]+)/i', $url, $matches);

            foreach ($matches[1] ?? [] as $featureId) {
                if (is_string($featureId) && ! in_array($featureId, $featureIds, true)) {
                    $featureIds[] = $featureId;
                }
            }

            $cids = array_merge($cids, self::cidsFromGoogleMapsUrl($url));
        }

        $placeId = is_string($place['id'] ?? null) ? $place['id'] : '';
        $entityId = self::identifier($place['googleEntityId'] ?? $place['google_entity_id'] ?? null);
        $profileId = self::identifier($place['profileId'] ?? $place['profile_id'] ?? null);
        $featureId = self::identifier($place['googleFeatureId'] ?? $place['google_feature_id'] ?? null) ?? ($featureIds[0] ?? null);

        if ($featureId !== null && ! in_array($featureId, $featureIds, true)) {
            $featureIds[] = $featureId;
        }

        $placeCid = self::canonicalCid($place['googleCid'] ?? $place['google_cid'] ?? null);

        if ($placeCid !== null) {
            $cids[] = $placeCid;
        }

        $featureCid = self::cidFromFeatureId($featureId);

        if ($featureCid !== null) {
            $cids[] = $featureCid;
        }

        $cids = array_values(array_unique($cids));
        $cid = $placeCid ?? ($cids[0] ?? null);

        $navigationLinks = [];

        foreach ($urls as $key => $url) {
            $navigationLinks[$key] = [
                'url' => $url,
                'source' => 'google_places',
            ];
        }

        $components = is_array($place['addressComponents'] ?? null) ? array_values($place['addressComponents']) : [];
        $formattedAddress = is_string($place['formattedAddress'] ?? null) ? $place['formattedAddress'] : null;
        $providerPlace = $place;

        if (is_array($providerPlace['photos'] ?? null)) {
            $providerPlace['photos'] = array_values(array_map(
                static function (mixed $photo): mixed {
                    if (! is_array($photo)) {
                        return $photo;
                    }

                    unset($photo['name'], $photo['photoUri']);

                    if (is_array($photo['authorAttributions'] ?? null)) {
                        $photo['authorAttributions'] = array_values(array_map(
                            static fn (mixed $attribution): mixed => is_array($attribution)
                                ? array_diff_key($attribution, ['photoUri' => true])
                                : $attribution,
                            $photo['authorAttributions'],
                        ));
                    }

                    return $photo;
                },
                $providerPlace['photos'],
            ));
        }

        return [
            'google_place_id' => $placeId,
            'google_feature_id' => $featureId,
            'google_cid' => $cid,
            'google_entity_id' => $entityId,
            'components' => $components,
            'formatted_address' => $formattedAddress,
            'metadata' => [
                'provider' => 'google_places_api_v1',
                'google_places' => [
                    'place' => $providerPlace,
                    'resource_name' => self::identifier($place['name'] ?? null),
                    'google_maps_links' => $links,
                    'identifiers' => [
                        'place_id' => $placeId,
                        'feature_ids' => $featureIds,
                        'cids' => $cids,
                        'entity_id' => $entityId,
                        'profile_id' => $profileId,
                    ],
                ],
            ],
            'navigation_links' => $navigationLinks,
        ];
    }

    private static function identifier(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = mb_trim((string) $value);

        return $value === '' ? null : $value;
    }

    public static function canonicalCid(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = mb_trim((string) $value);

        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        $value = mb_ltrim($value, '0');
        $value = $value === '' ? '0' : $value;

        if (mb_strlen($value) > 20 || (mb_strlen($value) === 20 && strcmp($value, '18446744073709551615') > 0)) {
            return null;
        }

        return $value;
    }

    public static function cidFromFeatureId(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (preg_match('/(?:!1s)?0x[0-9a-f]{1,16}:0x([0-9a-f]{1,16})/i', $value, $matches) !== 1) {
            return null;
        }

        return self::hexToDecimal($matches[1]);
    }

    public static function hexCidFromDecimal(mixed $value): ?string
    {
        $decimal = self::canonicalCid($value);

        if ($decimal === null) {
            return null;
        }

        if ($decimal === '0') {
            return '0';
        }

        $hexadecimal = '';

        while ($decimal !== '0') {
            $quotient = '';
            $remainder = 0;

            foreach (mb_str_split($decimal) as $digit) {
                $digitValue = ($remainder * 10) + (int) $digit;
                $quotientDigit = intdiv($digitValue, 16);
                $remainder = $digitValue % 16;

                if ($quotient !== '' || $quotientDigit > 0) {
                    $quotient .= (string) $quotientDigit;
                }
            }

            $hexadecimal = dechex($remainder) . $hexadecimal;
            $decimal = $quotient === '' ? '0' : $quotient;
        }

        return $hexadecimal;
    }

    /**
     * @return list<string>
     */
    public static function cidsFromGoogleMapsUrl(string $url): array
    {
        $parts = parse_url($url);
        $host = is_array($parts) && is_string($parts['host'] ?? null) ? mb_strtolower(mb_rtrim($parts['host'], '.')) : null;

        if (! is_string($host) || ! GoogleMapsTrustedHosts::isSupportedHost($host)) {
            return [];
        }

        $cids = [];
        $query = $parts['query'] ?? null;

        if (is_string($query)) {
            parse_str($query, $parameters);

            foreach ($parameters as $name => $value) {
                if (! in_array(mb_strtolower((string) $name), ['cid', 'ludocid'], true)) {
                    continue;
                }

                $cid = self::canonicalCid($value);

                if ($cid !== null) {
                    $cids[] = $cid;
                }
            }
        }

        preg_match_all('/!1s(0x[0-9a-f]{1,16}:0x([0-9a-f]{1,16}))/i', $url, $matches);

        foreach ($matches[2] ?? [] as $hexCid) {
            if (is_string($hexCid)) {
                $cids[] = self::hexToDecimal($hexCid);
            }
        }

        return array_values(array_unique($cids));
    }

    private static function hexToDecimal(string $hex): string
    {
        $hex = mb_strtolower(mb_ltrim($hex, '0'));

        if ($hex === '') {
            return '0';
        }

        $decimal = '0';

        foreach (mb_str_split($hex) as $nibble) {
            $carry = hexdec($nibble);

            for ($index = mb_strlen($decimal) - 1; $index >= 0; $index--) {
                $digitValue = ((int) $decimal[$index] * 16) + $carry;
                $decimal[$index] = (string) ($digitValue % 10);
                $carry = intdiv($digitValue, 10);
            }

            while ($carry > 0) {
                $decimal = ($carry % 10) . $decimal;
                $carry = intdiv($carry, 10);
            }
        }

        return $decimal;
    }
}
