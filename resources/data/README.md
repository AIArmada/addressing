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

Curated delta (2026-11-03): `countries.json` PS name
`Palestinian Territory Occupied` → `Palestine`, matching ISO 3166-1
Newsletter VI-14 (2013-02-06, "Palestine, State of"), CLDR, the bundled
formatter display, and the package's provider/docs. The 16 PS rows in
`states.json` and 132 PS rows in `cities.json.gz` carry the same
`country_name`, updated to match.

Deliberate exclusion: Israel (IL) is out of the bundled dataset
throughout — no `countries.json` row, no `states.json`/`cities.json.gz`
rows, no provider/formatter, no `address-formats.json` or
`address-validation.json` entry, and no ILS entry in the
commerce-support currency catalogue (removed together in `bce3bd21a`).
PS keeps its ISO currency `ILS`, which therefore links to nothing —
the country seeder skips codes absent from the catalogue.

Curated delta (2026-11-03, re-verification pass): `countries.json`
currency refresh to current ISO 4217 and the commerce-support catalogue
(which is the link source of truth; previously these five linked to
nothing): BG `BGN` → `EUR` (euro adopted 2026-01-01), CW/SX `ANG` →
`XCG` (Caribbean guilder since 2025-03-31), SL `SLL` → `SLE`
(redenomination, old leone ceased 2024-01-01), ZW `ZWL` → `ZWG`
(ZiG, effective 2024-06-25). Timezone IDs refreshed to canonical
IANA/link targets present in the catalogue (previously these seven
linked partially or not at all): AU drops `Australia/Currie`
(covered by `Australia/Hobart`), CA drops the five Toronto/Iqaluit/
Winnipeg/Edmonton duplicates (`Nipigon`, `Thunder_Bay`,
`Rainy_River`, `Pangnirtung`, `Yellowknife`), KI
`Pacific/Enderbury` → `Pacific/Kanton`, MN drops `Asia/Choibalsan`
(covered by `Asia/Ulaanbaatar`), UA `Kiev`/`Uzhgorod`/`Zaporozhye` →
`Kyiv` (with `Simferopol` retained). Translation keys `cn` → `zh`
and `kr` → `ko` across all 249 records, aligning `countries.json`
with `states.json`/`cities.json.gz` (which already use `ko`/`zh-CN`)
and BCP 47. Country-name corrections (propagated to `country_name`
in `states.json`/`cities.json.gz` and to `display` in
`address-formats.json`): IM `Man (Isle of)` → `Isle of Man`, FJ
`Fiji Islands` → `Fiji`, SJ `Svalbard And Jan Mayen Islands` →
`Svalbard and Jan Mayen`, AX → `Åland Islands`, RE → `Réunion`, ST
→ `São Tomé and Príncipe`, BL → `Saint Barthélemy`, MF →
`Saint Martin (French part)`, TR `Turkey` → `Türkiye` (matches ISO,
the formatter, and the docs). AQ `XXX` and the IL/ILS exclusion are
unchanged by design.

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
