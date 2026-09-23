---
title: L2 Expansion Scoping
---

# L2 Expansion Scoping

Which second administrative layer each of the four flagged countries
(US/BR/MX/CA) should grow next, from which authoritative source, and
what the postcode posture is. This is a scoping note, not an
implementation plan: row counts and URLs were verified in September
2026, but the implementer must re-pull every source at build time.

> Status: all four scoped builds are complete (US 3,143 counties,
> BR 5,571 municípios, MX 2,479 municipios, CA 5,028 CSDs — all
> depth-2 in the [coverage registry](./14-provider-coverage.md)).
> Kept as the source-and-trap reference for those tiers.

For the postal-`locality` method that sits below L2, see
[Locality Expansion Method](./15-locality-expansion.md).

## Shared modeling rules

- L2 rows live in the country's areas CSV as `level: 2` with
  `parent_source_id` pointing at the L1 row. Every code scheme below
  embeds the parent, so parents derive mechanically.
- Volumes (2.5k–5.5k rows) all fit the existing CSV + streaming-reader
  pattern; no format change needed.
- Postcodes are a postcode layer, never L3 areas: ZIPs, CEPs, and
  Canadian postcodes are delivery constructs that cross admin
  boundaries.
- Implementation follows the doc-15 verification set: sampling tests,
  `GeographyProviderContractTest` green, [Country Data](./05-country-data.md)
  updated same-pass, reseed + spot-check.

## United States — counties

- **Layer:** counties and county equivalents (~3,222 rows: 3,143 stateside + 78 PR + 1 DC).
- **Source:** US Census Bureau, public domain. Use the **annual County
  Gazetteer** (current vintage plus centroids), not the code-list text
  files: `national_county.txt` is stale (still lists pre-2015 Alaska
  geography) and `national_county2020.txt` is a frozen 2020 snapshot.
- **Trap:** any 2020-vintage file needs the Connecticut patch — the 8
  legacy counties were replaced by 9 planning regions in June 2022
  (Capitol 09110, Greater Bridgeport 09120, Lower Connecticut River
  Valley 09130, Naugatuck Valley 09140, Northeastern Connecticut 09150,
  Northwest Hills 09160, South Central Connecticut 09170, Southeastern
  Connecticut 09180, Western Connecticut 09190, per 87 FR 34235).
  Ignore the earlier 09017–09033 proposal codes — they were superseded.
- **Codes:** 5-digit FIPS GEOID (`STATEFP + COUNTYFP`); parent is the
  state row. CLASSFP distinguishes county/borough/parish/census-area
  flavors — keep it in the type or a qualifier, don't flatten names.
- **Postcodes:** no free complete ZIP database exists. The free
  [HUD-USPS crosswalk](https://www.huduser.gov/portal/datasets/census_tract_crosswalk.html)
  maps ZIP↔county/tract quarterly with address-share weights, but has
  no PO-Box-only ZIPs, no ZIP+4, and many ZIPs span counties (weights,
  not 1:1). Census ZCTAs (~33,791, public domain) are statistical
  approximations, not ZIPs — usable for centroid display, wrong for
  delivery addressing. The full delivery database is USPS-licensed.

## Brazil — municipalities

- **Layer:** municipalities, **5,571 units** (IBGE now returns 5,571
  directly, including Boa Esperança do Norte, Mato Grosso, and
  the Fernando de Noronha state district alongside the
  Federal District row).
- **Source:** IBGE, free. Canonical machine source is the Localidades
  API (`servicodados.ibge.gov.br/api/v1/localidades/municipios`),
  verified live: each record carries the 7-digit code, name, and the
  parent UF inline.
- **Codes:** 7-digit IBGE code (2-digit UF prefix + 4-digit municipal +
  check digit); parent derives from the UF prefix.
- **Postcodes:** CEP bulk data is **commercial**. The e-DNE (900k+
  codes) is a paid Correios product under a copyright monopoly (Law
  6538/1978); only individual Busca-CEP lookup is free. Options are
  licensing e-DNE, runtime lookup against a CEP API, or
  generic-municipality CEPs (`xxxxx-000`) only. Do not bundle scraped
  CEP data.

## Mexico — municipalities

- **Layer:** municipalities plus the 16 CDMX alcaldías, **2,479**
  (INEGI 2024 count 2,478 plus Villa Juárez, Aguascalientes, created
  August 2026; the number moves as states split municipalities —
  Baja California added San Quintín and San Felipe recently).
- **Source:** INEGI Marco Geoestadístico / Catálogo Nacional de
  Municipios, free (as-built: sourced from Wikidata P3801 CVEGEO
  values, verified against the Spanish Wikipedia state annexes
  and the INEGI 2024 national count — see `05-country-data.md`).
  Each municipio carries a 5-digit CVEGEO
  (2-digit state + 3-digit municipio); parent derives from the
  state prefix.
- **Trap:** CDMX's 16 units are alcaldías (former delegaciones), not
  municipios — type them distinctly like other capital overlay units.
- **Postcodes:** SEPOMEX's `cpdescarga.txt` (state/municipio/locality/CP
  rows) is a **free download but a restrictive license**: free for
  private use, commercialization and redistribution to third parties
  forbidden. It **cannot be bundled** in this package; consuming apps
  must self-download. Design the postcode layer as consumer-supplied
  input, not bundled data.

## Canada — census subdivisions

- **Layer decision:** census subdivisions (CSDs), not census divisions.
  CDs (~293) are statistical groupings too coarse for addressing; CSDs
  are the municipal layer that addresses resolve against. Count moves
  with amalgamations (5,173 in the 2023 file, 5,028 in the 2024 file
  after New Brunswick's reform) — always use the latest vintage.
- **Source:** Statistics Canada, free. The SGC structure files and the
  annual CSD Boundary Files carry names, codes, types, and boundaries.
- **Codes:** 7-digit SGC (2-digit province + 2-digit CD + 3-digit CSD);
  parent chain derives mechanically. Keep the CSD type (city, town,
  réserve, hamlet, …) — it disambiguates same-name rows.
- **Trap:** ~1,028 Indian reserves are CSDs; names and spellings follow
  ISC/CIRNAC recognition and change — do not normalize them by hand.
- **Postcodes:** the full 6-character file is a commercial Canada Post
  product. The free layer is the FSA (first 3 characters, ~1,620 —
  1,621 in the 2011 census file; the count moves): StatCan publishes
  census-derived FSA boundaries, and the per-district FSA lists are
  public. FSA-prefix validation is free; full-postcode resolution
  needs a license.

## Suggested build order

1. **MX** — smallest clean win: single free source, stable codes,
   postcode story fully understood (consumer-supplied SEPOMEX).
2. **US** — sources free and verified, but mind the Gazetteer-vs-txt
   trap and the Connecticut patch; ZIP layer stays weights-only.
3. **BR** — source free and machine-readable, but 5,571 rows is the
   largest import; CEP stays out unless licensed.
4. **CA** — needs the CD-vs-CSD decision confirmed per consumer and
   the reserve-naming caveat honored; smallest postcode story (FSA).
