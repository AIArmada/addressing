<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PlaceLookup\Actions;

use AIArmada\Addressing\Contracts\GoogleMapsInputNormalizer;
use AIArmada\Addressing\Contracts\GoogleMapsLookup;
use AIArmada\Addressing\GooglePlaces\GoogleMapsInputValues;
use AIArmada\Addressing\GooglePlaces\GoogleMapsUrlParser;
use AIArmada\Addressing\GooglePlaces\GooglePlacesConfiguration;

final readonly class NormalizeGoogleMapsInputAction implements GoogleMapsInputNormalizer
{
    private const string PartialStatus = 'partial';

    private const string ResolvedStatus = 'resolved';

    private const string UnresolvedStatus = 'unresolved';

    public function __construct(
        private GoogleMapsUrlParser $urls,
        private readonly GoogleMapsLookup $remoteLookup,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     google_maps_url: string|null,
     *     google_place_id: string|null,
     *     google_feature_id: string|null,
     *     google_cid: string|null,
     *     google_entity_id: string|null,
     *     google_display_name: string|null,
     *     lat: float|null,
     *     lng: float|null,
     *     google_resolution_source: string|null,
     *     google_resolution_status: 'resolved'|'partial'|'unresolved',
     *     google_resolution_fingerprint: string|null,
     *     google_resolution_message: string|null
     * }
     */
    public function handle(array $input): array
    {
        $rawUrl = GoogleMapsInputValues::string($input['google_maps_url'] ?? null);
        $displayName = GoogleMapsInputValues::displayName($input['google_display_name'] ?? $input['display_name'] ?? null);
        $placeId = GoogleMapsInputValues::placeId($input['google_place_id'] ?? null);
        $featureId = GoogleMapsInputValues::string($input['google_feature_id'] ?? null);
        $cid = GoogleMapsInputValues::string($input['google_cid'] ?? null);
        $entityId = GoogleMapsInputValues::string($input['google_entity_id'] ?? null);
        $lat = GoogleMapsInputValues::latitude($input['lat'] ?? null);
        $lng = GoogleMapsInputValues::longitude($input['lng'] ?? null);
        $remoteLookupEnabled = $this->remoteLookupEnabled($input['google_maps_remote_lookup_enabled'] ?? null);
        $source = $this->normalizeSource($input['google_resolution_source'] ?? $input['resolution_source'] ?? null);
        $previousStatus = $this->normalizeStatus($input['google_resolution_status'] ?? null);
        $previousFingerprint = GoogleMapsInputValues::string($input['google_resolution_fingerprint'] ?? null);

        $currentUrl = $this->urls->unwrapConsent($rawUrl);
        $currentFingerprint = $this->fingerprint($currentUrl);
        $inputChanged = $previousFingerprint !== null
            && $currentFingerprint !== null
            && $currentFingerprint !== $previousFingerprint;

        if ($rawUrl !== null && $inputChanged) {
            $placeId = null;
            $lat = null;
            $lng = null;
            $displayName = null;
            $source = 'manual';
        }

        $parsed = $this->urls->parse($currentUrl);

        $currentUrl = $parsed['url'] ?? $currentUrl;
        $currentFingerprint = $this->fingerprint($currentUrl);
        $placeId ??= $parsed['place_id'];
        $displayName ??= $parsed['display_name'];

        if (($lat === null) || ($lng === null)) {
            $lat ??= $parsed['lat'];
            $lng ??= $parsed['lng'];
        }

        $shouldSkipRemoteRetry = $currentFingerprint !== null
            && $currentFingerprint === $previousFingerprint
            && in_array($previousStatus, [self::PartialStatus, self::UnresolvedStatus], true);

        if (
            ! $shouldSkipRemoteRetry
            && ($parsed['needs_redirect_resolution'] ?? false)
            && ! (($placeId !== null) && ($lat !== null) && ($lng !== null))
            && GooglePlacesConfiguration::isLinkResolutionEnabled()
        ) {
            $resolvedUrl = $this->remoteLookup->resolveUrl($currentUrl);

            if ($resolvedUrl !== $currentUrl) {
                $currentUrl = $resolvedUrl;
                $parsed = $this->urls->parse($currentUrl);
                $currentFingerprint = $this->fingerprint($currentUrl);
                $placeId ??= $parsed['place_id'];
                $displayName ??= $parsed['display_name'];
                $lat ??= $parsed['lat'];
                $lng ??= $parsed['lng'];
            }
        }

        if (
            ! $shouldSkipRemoteRetry
            && $placeId === null
            && $displayName !== null
            && $remoteLookupEnabled
            && GooglePlacesConfiguration::isServerLookupEnabled()
        ) {
            $resolvedPlace = $this->remoteLookup->searchByName(
                $displayName,
                $lat,
                $lng,
                $this->countryCode($input),
            );

            if ($resolvedPlace !== null) {
                $placeId = $resolvedPlace['place_id'];
                $lat ??= $resolvedPlace['lat'];
                $lng ??= $resolvedPlace['lng'];
            }
        }

        if (
            ! $shouldSkipRemoteRetry
            && $placeId !== null
            && $rawUrl === null
            && (($lat === null) || ($lng === null) || ($displayName === null))
            && $remoteLookupEnabled
            && GooglePlacesConfiguration::isServerLookupEnabled()
        ) {
            $placeDetails = $this->remoteLookup->placeDetails($placeId);

            if ($placeDetails !== null) {
                $displayName ??= $placeDetails['display_name'];
                $lat ??= $placeDetails['lat'];
                $lng ??= $placeDetails['lng'];
            }
        }

        $lat = GoogleMapsInputValues::latitude($lat);
        $lng = GoogleMapsInputValues::longitude($lng);
        $canonicalUrl = $this->urls->canonicalUrl($placeId, $lat, $lng, $displayName);
        $normalizedUrl = $canonicalUrl ?? $currentUrl;
        $normalizedFingerprint = $this->fingerprint($normalizedUrl);
        $normalizedSource = $source ?? ($normalizedUrl !== null || $placeId !== null ? 'manual' : null);
        $status = $this->resolveStatus($normalizedUrl, $placeId, $lat, $lng);

        return [
            'google_maps_url' => $normalizedUrl,
            'google_place_id' => $placeId,
            'google_feature_id' => $featureId,
            'google_cid' => $cid,
            'google_entity_id' => $entityId,
            'google_display_name' => $displayName,
            'lat' => $lat,
            'lng' => $lng,
            'google_resolution_source' => $normalizedSource,
            'google_resolution_status' => $status,
            'google_resolution_fingerprint' => $normalizedFingerprint,
            'google_resolution_message' => $this->resolutionMessage($status, $rawUrl, $placeId),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function countryCode(array $input): ?string
    {
        $countryCode = $input['country_code'] ?? null;

        if (! is_string($countryCode) || preg_match('/^[A-Za-z]{2}$/', mb_trim($countryCode)) !== 1) {
            return null;
        }

        return mb_strtoupper(mb_trim($countryCode));
    }

    private function resolutionMessage(string $status, ?string $rawUrl, ?string $placeId): ?string
    {
        if (($rawUrl === null) && ($placeId === null)) {
            return null;
        }

        return match ($status) {
            self::ResolvedStatus => null,
            self::PartialStatus => __('Google place ID could not be confirmed from this link. The normalized Google Maps URL will still be saved.'),
            default => __('This Google Maps link could not be fully normalized. It will be saved as-is unless more location details are provided.'),
        };
    }

    /**
     * @return 'resolved'|'partial'|'unresolved'
     */
    private function resolveStatus(?string $url, ?string $placeId, ?float $lat, ?float $lng): string
    {
        if ($placeId !== null) {
            return self::ResolvedStatus;
        }

        if (($url !== null) || (($lat !== null) && ($lng !== null))) {
            return self::PartialStatus;
        }

        return self::UnresolvedStatus;
    }

    private function normalizeSource(mixed $value): ?string
    {
        $normalized = GoogleMapsInputValues::string($value);

        return in_array($normalized, ['manual', 'picker'], true) ? $normalized : null;
    }

    private function remoteLookupEnabled(mixed $value): bool
    {
        return filter_var($value ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function normalizeStatus(mixed $value): ?string
    {
        $normalized = GoogleMapsInputValues::string($value);

        return in_array($normalized, [self::ResolvedStatus, self::PartialStatus, self::UnresolvedStatus], true)
            ? $normalized
            : null;
    }

    private function fingerprint(?string $url): ?string
    {
        return $url === null ? null : sha1($url);
    }
}
