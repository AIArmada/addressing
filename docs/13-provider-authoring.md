---
title: Provider Authoring
---

# Provider Authoring

This guide covers adding a new country geography provider to `aiarmada/addressing`. Follow it top to bottom; you should not need to read provider source for conventions. Copy `BrazilGeographyProvider` for a state-only country or `IndonesiaGeographyProvider` for a deep tree, then adapt. Copy `LaosAddressFormatter` for the UPU formatter.

The full pipeline is: **CSV → provider class → consumer registration → docs**. Each stage is required: data without a provider never seeds, a provider without registration is invisible (see [Provider registration](03-configuration.md)), and a provider without docs is a silent gap — see [05-country-data](05-country-data.md) and `resources/geography/README.md`.

## Contracts

A state-only provider implements three contracts; deep providers use the same three with more data. Formatters are separate.

```php
use AIArmada\Addressing\Contracts\CountryAddressAreaMetadataProvider;
use AIArmada\Addressing\Contracts\CountryGeographyProvider;
use AIArmada\Addressing\Contracts\CountryHierarchyProvider;

class BrazilGeographyProvider implements CountryAddressAreaMetadataProvider, CountryGeographyProvider, CountryHierarchyProvider
{
    public const string AREA_SOURCE = 'aiarmada_addressing_brazil_v1';

    private const string PROVIDER_KEY = 'aiarmada.addressing.brazil';

    public function providerKey(): string
    {
        return self::PROVIDER_KEY;
    }

    public function countryCode(): string
    {
        return 'BR';
    }
}
```

- `CountryGeographyProvider` (extends `CountryAddressProfile`) is the only required contract. It contributes `countryCode()`, the stable `providerKey()`, `seed()` for `State` rows, and `addressHierarchies()`.
- `CountryHierarchyProvider` is optional and contributes `addressAreaSource()` plus `stateAreaMappings()`. Every bundled provider implements it; without it the country has states but no area tree and no state↔area links.
- `CountryAddressAreaMetadataProvider` is optional and contributes `areaRoles()`, `areaNames()`, and `areaRelationships()`, all keyed by CSV `source_id`. Empty arrays are a normal pattern, not a gap.
- `CountryAddressFormatter` (extends `AddressFormatter`) lives in a separate class per country and is registered under the separate `addressing.formatters` key, not `addressing.geography.providers`. It contributes `countryCode()` plus `format(AddressData $address): string`.

## Designing addressHierarchies()

Return one `AddressHierarchyDefinition` per address structure the country needs. Every bundled provider uses the `administrative` hierarchy key with label `Administrative / Territorial Geography`; only Malaysia and Singapore add a second `postal` hierarchy, so a new provider should start with `administrative` alone.

```php
use AIArmada\Addressing\Data\AddressHierarchyDefinition;
use AIArmada\Addressing\Data\AddressLevelDefinition;

public function addressHierarchies(): array
{
    return [
        new AddressHierarchyDefinition(
            key: 'administrative',
            label: 'Administrative / Territorial Geography',
            levels: [
                new AddressLevelDefinition(
                    key: 'state',
                    label: 'State / Federal District',
                    kind: 'state',
                    hierarchyType: 'administrative',
                    areaTypes: ['state', 'federal_district'],
                    areaLevel: 1,
                ),
            ],
        ),
    ];
}
```

- `kind` is `state` for the top level that mirrors `State` rows, `area` for everything below it. State-kind levels carry no `assignmentRole`: a `State` is selected through `state_id`, never as an area assignment. (Singapore is the exception that proves the rule: it has no states, so all its levels are `kind: 'area'`.)
- `areaTypes` / `areaType` constrain which CSV area `type` values may fill the level. Use the plural spelling; the singular exists only as a fallback and no bundled provider uses it. State-kind levels use `areaLevel: 1` (singular is the template norm here); area sub-levels use plural `areaLevels`.
- `parentKey` names the parent level `key` within the same hierarchy. It is required on every `area` level so hierarchy validation can walk upward.
- `assignmentRole` is effectively required on every `area` level: without it the role falls back to `{hierarchy}_{level}` (e.g. `administrative_district`), but every bundled area level sets an explicit role. Use the level key as the role (`mukim`, `regency`, `district`, `province`); only prefix with the hierarchy key when one country needs distinct roles per hierarchy (`postal_locality` vs `administrative_district` in Malaysia).

A deep level follows the same shape with `kind: 'area'`, for example Indonesia's regency level: `areaTypes: ['regency', 'city']`, `areaLevels: [2]`, `parentKey: 'province'`, `assignmentRole: 'regency'`. Related types may share one level and role when the addressing semantics are identical.

## The two-hierarchy rule

When a country exposes both an `administrative` and a `postal` hierarchy, every area row must pass the evidence bar of the hierarchy it sits in — and a row that fails one bar must be relocated, never silently discarded:

- **Administrative rows require legal backing**: a gazette, an official boundary book, census admin geography, or an equivalent legal inventory. A real place with no legal standing as an admin unit must not sit in the administrative hierarchy.
- **Postal-locality rows require delivery backing**: a postcode assignment from the national postal operator **plus** recognition as a distinct place by at least one official source (census locality list, electoral geography, local government). No postcode, no locality.
- **Relocate, don't delete.** A row that fails its hierarchy's test moves to the other hierarchy when it passes that bar (a non-gazetted town becomes a postal `locality`, keeping its postcodes). Delete only rows that pass neither bar: non-places, duplicates, errors. Move rows that are merely misfiled.
- **No cross-hierarchy dedup.** The same place may exist in both hierarchies under different types (a gazetted bandar and its postal town are different truths, not duplicates).
- **Postal areas need state-ancestor links.** Any postal row parented below the state must carry a shortcut relationship to its state ancestor in the `postal` hierarchy type, or region-scoped selectors cannot resolve it.

## stateDefinitions and seed()

`seed()` populates `State` rows from a private `stateDefinitions()` list of `['name', 'code']` pairs, keyed by `[country_id, code]`:

```php
private function stateDefinitions(): array
{
    return [
        ['name' => 'Acre', 'code' => 'AC'],
        // ...
    ];
}
```

Use the country's own subdivision codes: ISO 3166-2 second parts (`AC`, `JK`), alpha codes (`BB`…`TH` for Germany), ISTAT numbers (`21`…`88` for Italy), or official numeric strings (`13`, `14` for Bahrain). State names use official endonyms with diacritics (`São Paulo`, `Bayern`, `Piemonte`). Mirror `resources/data/states.json` for names, codes, and ordering so the provider and the bundled data never drift. If a definition changes (a renamed or merged subdivision), delete the stale rows in `seed()` so reseeds converge; Indonesia and South Korea both carry such cleanup.

## areaNames, areaRoles, areaRelationships

All three are keyed by CSV `source_id` and synced with `source = providerKey` on every geography reseed, which is why provider-managed names can be deleted and recreated freely while `manual` aliases survive.

```php
/** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
public function areaNames(AddressCountry $country): array
{
    return [
        'id:province:31' => [
            ['name' => 'Jakarta', 'name_type' => 'common', 'is_preferred' => true],
            ['name' => 'Daerah Khusus Jakarta', 'name_type' => 'official'],
            ['name' => 'DKJ', 'name_type' => 'abbreviation'],
        ],
    ];
}
```

- `areaNames()` holds variants of the canonical CSV name: `historic` (Nanggroe Aceh, Irian Jaya), `official` (full ceremonial forms), `common`, `abbreviation`. English exonyms go here as `alternative` (Germany and Italy carry them); they never replace the canonical name. Return `[]` when the country needs no variants.
- `areaRoles()` maps each shipped `source_id` to its roles (`role`, `country_code`, `is_primary`). Derive it by iterating `addressAreaSource()->areas()` with a `match` on `$area->type`; role names are 1:1 with types and `is_primary` is `true`. Folding a minority type into the majority role (Laos maps `prefecture` areas to the `province` role) is allowed when the distinction carries no addressing meaning, but 1:1 is the norm.
- `areaRelationships()` derives parent links from each area's `parentSourceId` with `relationship_type: 'contains'` and the hierarchy type. Single-level countries keep the boilerplate loop; it is a no-op by design because top-level rows have no parent.

## stateAreaMappings and key stability

`stateAreaMappings()` links each `State` (keyed by its `stateDefinitions` code) to its level-1 area:

```php
/** @return array<string, array{area_code: string, source: string, area_level: int, hierarchy_types?: list<string>}> */
public function stateAreaMappings(): array
{
    return [
        'AC' => [
            'area_code' => 'AC',
            'source' => self::AREA_SOURCE,
            'area_level' => 1,
            'hierarchy_types' => ['administrative'],
        ],
        // ...
    ];
}
```

`area_code` is the CSV `code` column, not the `source_id`. When state codes and area codes differ (Indonesia maps ISO `AC` to Kemendagri `11`), the mapping is where the translation lives; most countries use an identity map. `area_level` is always `1`. `hierarchy_types` may be omitted unless one state must resolve different roots per hierarchy (Malaysia declares both).

Two keys with different stability rules:

- `AREA_SOURCE` (public const, `aiarmada_addressing_<country>_v1`) identifies one feed import. It is versioned: bump it when the source data changes incompatibly.
- `providerKey()` (private const, `aiarmada.addressing.<country>`) identifies the long-lived owner of everything the provider seeds. **Never change it**: reseeds deactivate, delete, and recreate areas, aliases, roles, relationships, and state links scoped to this key. Changing it orphans the old rows instead of refreshing them.

## CSV format and source_id conventions

One file per country at `resources/geography/<slug>-address-areas.csv`, loaded through `CsvAddressAreaSource($path, self::AREA_SOURCE)`:

```csv
source_id,country_code,type,name,native_name,code,parent_source_id,level,latitude,longitude
br:state:acre,BR,state,Acre,,AC,,1,,
br:state:alagoas,BR,state,Alagoas,,AL,,1,,
```

- `source_id` is `<lower-iso2>:<type>:<slug-or-official-code>`: slugs for most countries (`br:state:acre`, `bh:governorate:capital`), official statistical codes where they exist (`id:province:11`, `id:regency:1101`, `id:district:110101`, `jp:municipality:01100`). It must be stable across reseeds and unique per feed.
- `level` is 1-based; level-1 rows leave `parent_source_id` empty. Deeper rows point at their parent's `source_id`.
- `code` carries the official subdivision code and is what `stateAreaMappings()` joins on at level 1.
- `native_name`, `latitude`, and `longitude` are usually empty; fill them only with trusted data.
- Canonical `name` values use official endonyms. English exonyms, historic names, and abbreviations belong in `areaNames()`, never in the CSV — except countries whose official administrative language is English, which use English canonically with no aliases.
- Names containing commas are quoted per RFC 4180 (`"Larut, Matang dan Selama"`, `"Praha, Hlavní město"`); the loader parses them with `fgetcsv`. Slugs strip diacritics and punctuation (`liquica`, `sao-paulo`, `sanaa`).

:::warning
`source_id` prefixes do not always equal the row type: Indonesian city rows use the `id:regency:` prefix and only the `type` column distinguishes them. Always match on the `type` column, never by parsing `source_id`.
:::

## Name twins and slug stability

Duplicate names are normal and must never be "fixed" by renaming: Bangladesh ships 8 division/district twins, Laos ships two Vientianes (`VI` province, `VT` prefecture), and Kazakhstan, Kyrgyzstan, Azerbaijan, Belarus, Estonia, and Latvia all carry city/region or municipality twins. Filter by `type` (or `code`), never by name alone, and call the twins out in the country's [05-country-data](05-country-data.md) section and `resources/geography/README.md` line.

When two rows share both name *and* type, the slug alone cannot disambiguate them. Suffix every colliding slug with its lowercased code (`lt:district_municipality:alytus-02`, `lt:district_municipality:alytus-03`) and document the suffix in the same two places. Never invent a distinguishing type to dodge the collision.

## Level keys for mixed flat tiers

A single-level tier may legitimately carry two administrative tiers flat (Sri Lanka's provinces + districts, Burkina Faso's regions + provinces, Lithuania's counties + municipalities). Name the level `key` after the top tier (`province`, `region`, `county`), list every carried type in `areaTypes` top-tier-first, and join the labels with ` / `. Record the country as a depth-2 candidate in the [provider coverage registry](14-provider-coverage.md) so a future split has a starting point.

## State-only versus deep trees

Ship state-only (one `state` level, one CSV level, identity mappings) unless consumer addressing genuinely needs sub-state granularity. Most bundled providers are state-only. Deep trees exist where addressing or hierarchy selection requires them: dual-hierarchy Malaysia and Singapore, depth-3 Indonesia, and depth-2 Algeria, Bangladesh, Brunei, India, Japan, Jordan, Morocco, Nigeria, Oman, Pakistan, Qatar, Spain, Türkiye, and Uzbekistan. When in doubt, start state-only — depth can be added later without breaking the state level, while shipping wrong depth forces consumers to carry it.

## The numeric-key gotcha

PHP casts numeric-string array keys to `int`, so `stateAreaMappings()` returns int keys for countries with numeric codes (see [Numeric state codes](05-country-data.md)). The contract documents `array<int|string, ...>` and the seeder stringifies keys before querying, so this is handled — but your mappings must still declare the codes as quoted strings (`'13' => ...`), and any consumer-side code touching mapping keys must stringify before comparing.

## Formatter layouts

Every provider ships a `CountryAddressFormatter` whose layout must be researched from the UPU addressing sheet for the country (`upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/<cc>En.pdf`), cross-checked against the national post or the UPU POST*CODE database. Training-data knowledge of postcode formats is not sufficient: Djibouti and Burkina Faso both have live 5-digit systems that secondary sources miss, and several UPU sheets contradict Wikipedia. No-postcode claims need two sources.

Pick the closest layout pattern and note the UPU source in a one-line comment:

- **Left** (`{postcode} {locality}`): Laos, Kyrgyzstan, most of Europe. Country prefixes (`HR-`, `LT-`, `AX-`) and spacing (`NNN NN`) pass through exactly as supplied.
- **Right** (`{locality} {postcode}`): Cambodia (province-anchored), Bhutan, Lebanon, Lesotho, Zambia. Latvia adds a comma (`RIGA, LV-1050`).
- **Comma-left** (`{postcode}, {locality}`): Kazakhstan, Belarus, Moldova.
- **Own line below**: Iran, Sri Lanka, Namibia, Malta, Ireland (Eircode), the UK-system Crown dependencies.
- **Own line above**: Albania, the only bundled country whose postcode sits above the locality.
- **None**: Hong Kong, North Korea, most of Africa. Print any supplied code on its own line; never drop user data.

Pass-through rules: formatters print postcodes exactly as supplied — they never add, strip, or validate prefixes and spacing. City/state twins that compare equal print once (`sameText` guard). The country line uses the short display name from `resources/data/countries.json` (`Iran`, not `IRAN (ISLAMIC REP.)`), except where the database spelling is unusable on mail (Isle of Man prints `Isle of Man`, not `Man (Isle of)`). When the model cannot represent part of the UPU line (Serbia's street-level PAK, Gabon's trailing office code), document the gap in the formatter comment and the country's [05-country-data](05-country-data.md) section instead of fabricating it.

## Testing a provider

Add `tests/src/Addressing/Geography/<Country>GeographyProviderTest.php` following the Brazil pattern, and run it per package:

```bash
./vendor/bin/pest --parallel tests/src/Addressing/Geography/BrazilGeographyProviderTest.php
```

Assert at minimum:

1. `seed()` creates every `State` (count plus one renamed-value check proving `updateOrCreate` overwrites stale names).
2. `stateAreaMappings()` covers every state code, with keys in a fixed order.
3. `addressHierarchies()` has the expected keys, kinds, and assignment roles.
4. `SeedCountryGeographiesAction::execute('<ISO2>')` imports the tree: active-area counts per type, one `AddressAreaStateLink` per mapping, and any declared aliases present.

Then exercise the consumer path once: seed the country, create an address with the role assignments from the new hierarchy, and run `SyncAddressAreaAssignmentsAction` — unknown roles and wrong-level areas must be rejected, correct assignments must persist.

Add two formatter cases to `tests/src/Addressing/Geography/CountryAddressFormattersTest.php`: the main UPU layout with a real example from the research, plus one variant (missing postcode, twin dedup, or a documented edge such as a pass-through prefix). Use the country's real division names so the tests double as usage examples.

## Registering the provider

Registration is what makes a shipped provider visible: bundled providers go in the package's own `config/addressing.php` (`addressing.geography.providers` plus `addressing.formatters`), in alphabetical `use` order with entries appended per batch. Consuming apps enable countries through their published copy instead. See [Provider registration](03-configuration.md) for what registration gates and what happens without it. Finish by documenting the country in [05-country-data](05-country-data.md) (one section following the file's template), adding its line to `resources/geography/README.md`, and adding its row to the [provider coverage registry](14-provider-coverage.md).

## Role and area-type vocabulary

Every assignment role and area type in use across the bundled providers, extracted from `addressHierarchies()`. State-kind levels carry no assignment role; a `State` is always selected through `state_id`.

### Assignment roles

| Role | Countries | Area types | Levels |
| ---- | --------- | ---------- | ------ |
| `administrative_district` | MY | district, minor_district | 2–3 |
| `administrative_division` | MY | division | 2 |
| `administrative_subdivision` | MY | city, municipality, mukim, subdistrict | 2–4 |
| `daira` | DZ | daira | 2 |
| `district` | BD, ID, IN, PK, TR | district | BD 2, ID 3, IN 2, PK 2, TR 2 |
| `lga` | NG | lga, area_council | 2 |
| `liwa` | JO | liwa | 2 |
| `mukim` | BN | mukim | 2 |
| `municipality` | JP | municipality | 2 |
| `planning_area` | SG | planning_area | 2 |
| `postal_district` | SG | postal_district | 1 |
| `postal_locality` | MY | locality, precinct | 2 |
| `postal_sector` | SG | postal_sector | 2 |
| `province` | ES, MA | ES province; MA province, prefecture | 2 |
| `regency` | ID | regency, city | 2 |
| `region` | SG | region | 1 |
| `tuman` | UZ | tuman, city | 2 |
| `wilayat` | OM | wilayat | 2 |
| `zone` | QA | zone | 2 |

### Area types by level

Level 1 is always state-kind (one level per country, `areaLevel: 1`), except Singapore, which has no states: its level-1 `postal_district` and `region` are `kind: 'area'`.

State-level (kind `state`, level 1): administrative_region, arctic_region, area, atoll, autonomous_city, autonomous_community, autonomous_district, autonomous_oblast, autonomous_region, autonomous_republic, autonomous_sector, autonomous_territorial_unit, canton, capital_city, capital_district, capital_territory, city, city_municipality, city_with_county_rights, commune, county, department, dependency, district, district_municipality, districts_under_republic_administration, division, economic_prefecture, emirate, entity, federal_city, federal_district, geographical_region, governorate, island, krai, local_council, metropolitan_administration, metropolitan_city, municipality, nation, oblast, okrug, parish, popularate, prefecture, province, quarter, region, regional_unit, republic, rural_municipality, sheadings, special_administrative_region, special_city, special_municipality, special_self_governing_city, special_self_governing_province, state, state_city, territorial_unit, territory, town, union_territory, urban_community, urban_municipality, voivodeship, wilaya, wilayah_persekutuan.

Sub-state (kind `area`, level 2+, plus SG level 1): area_council, city, daira, district, division, lga, liwa, locality, minor_district, mukim, municipality, planning_area, postal_district, postal_sector, precinct, prefecture, province, regency, region, subdistrict, tuman, wilayat, zone.

### Naming new roles and types

- Use the country's own administrative term, lowercased with underscores: `mukim`, `wilaya`, `oblast`, `voivodeship`, `barangay`-style local terms over English approximations.
- Prefer reusing a generic role when the semantics match an existing one (`district`, `province`) over coining a near-synonym.
- Set the role to the level key. Prefix with the hierarchy key (`postal_`, `administrative_`) only when one country ships several hierarchies whose roles would otherwise collide.
- Keep one role per level; group related types under it (Indonesia's `regency` covers `regency` and `city`) instead of splitting roles per type.
