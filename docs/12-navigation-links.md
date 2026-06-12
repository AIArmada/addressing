---
title: Navigation Links
---

# Navigation Links

## Purpose

Navigation links allow storing manual Google Maps and Waze URLs on addresses, with automatic fallback to generated links when manual links are absent.

## Fields

| Column | Type | Description |
|--------|------|-------------|
| `google_maps_url` | text | Manually saved Google Maps URL |
| `waze_url` | text | Manually saved Waze URL |
| `navigation_links` | JSON | Additional navigation provider links (Apple Maps, Grab, etc.) |

## Manual vs Generated

- Manual URLs always win over generated URLs.
- Generated URLs are convenience fallbacks when no manual URL is set.
- Coordinates and formatted addresses remain the preferred structured data.

### Generation Sources

For Google Maps:
1. `google_maps_url` (manual)
2. `navigation_links.google_maps.url`
3. Place ID (when `provider` is `google` and `provider_place_id` is set) → `https://www.google.com/maps/search/?api=1&query={coord|address}&query_place_id={place_id}`
4. Latitude/longitude → `https://www.google.com/maps/search/?api=1&query={lat},{lng}`
5. Formatted address → `https://www.google.com/maps/search/?api=1&query={address}`

For Waze:
1. `waze_url` (manual)
2. `navigation_links.waze.url`
3. Latitude/longitude → `https://waze.com/ul?ll={lat},{lng}&navigate=yes`
4. Formatted address → `https://waze.com/ul?q={address}`

## API Key Requirements

No API key is required to open Google Maps or Waze URLs. This feature does not perform any server-side URL fetching, expansion, or API calls.

## URL Normalization

The `NormalizeNavigationUrl` helper trims whitespace, validates HTTP(S) schemes, and rejects empty or invalid URLs. It does not fetch or expand URLs.

## Snapshots

When an address snapshot is created, navigation links are copied and preserved. Subsequent edits to the source address do not mutate existing snapshots.

## AddressData Aliases

All these input keys map to `googleMapsUrl`:

```
google_maps_url, googleMapsUrl, google_map_url, googleMapUrl, maps_url, mapsUrl
```

All these input keys map to `wazeUrl`:

```
waze_url, wazeUrl
```

All these input keys map to `navigationLinks`:

```
navigation_links, navigationLinks, external_links, externalLinks
```
