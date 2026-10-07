# Addressing Data Resources

`countries.json` is the primary bundled dataset (ISO 3166-1 country/territory address entities). `states.json` and `cities.json` are bundled for address geography.

Source: [nnjeim/world](https://github.com/nnjeim/world) — the country, state, and city JSON files are copied from the source. Seed actions assign UUID PKs at runtime (int IDs from source are stripped). Shared currency, language, and timezone data is provided by `commerce-support`.

Curated deltas in `states.json` (2026-09-20, both upstreams stale): Burundi
18 → 5 provinces (July 2025 reform; codes 01-05 provisional pending ISO
3166-2:BI), Estonia dropped Toila (merged into Jõhvi 2025). Burkina Faso
keeps the pre-2025 list — the 2 new province names and new ISO codes are
unavailable in accessible sources.

Curated deltas (2026-10-07): `states.json` dropped the CN-TW row (Taiwan is
its own country with its own provider, deliberately not a CN area) and
renamed FR-973 `French Guiana` → `Guyane` (region/department rows, native
field, and provider already agree). `countries.json` AQ currency
`AAD`/`Antarctican dollar` → `XXX`/`No universal currency`: an honesty fix
with no runtime effect — the country seeder never reads `currency`, and
neither code exists in the commerce-support currency catalog, so AQ links
to nothing before and after.

`address-validation.json` (212 countries, IL deliberately excluded) carries
per-country postcode patterns, required fields, and uppercase fields,
extracted from commerceguys/addressing (MIT) at `d9cda0c` and curated to the
bundled storage forms: 33 entries carry a `note` recording a storage-form
adaptation (outward-only, prefixed, prefix-level), an our-data-wins
adjudication against a stale upstream shape, or a compiled-from-bundle
pattern where commerceguys has none. Recipient-name requirements are dropped:
the `Address` model has no recipient fields. `upper` is conventional-case
guidance, not a validation rule: nothing uppercases on write, and the only
reader is the Google drift oracle (`address:reference:google`), which flags
disagreement with Google's `upper` letters for human adjudication.
GH is deliberately unprofiled: Ghana Post GPS is a locator, not a postcode,
and neither Google nor UPU defines a GH postcode shape.

`address-formats.json` (228 countries) is the declarative twin of the
formatter shells: `{display, lines[], abbreviations?}` per country,
interpreted by `AddressFormatRenderer`. Every entry was transcribed from
the hand-written formatter it replaced and proven byte-identical on the
trimmed-input path by a 15,048-case old-vs-new differential (every
field-presence subset per country, plus duplicate and code-case
variants); the five state abbreviation maps moved into the file verbatim.
Raw-constructed `AddressData` with padded strings now trims (notably
street lines), which the old shells did not — the persistence path always
trims via `AddressData::from()`, so this only affects hand-built input.

Do not add districts, postcodes or other locality datasets to this directory in the core package. Use `AddressAreaSource` imports instead.
