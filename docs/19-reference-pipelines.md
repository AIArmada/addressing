---
title: Reference Pipelines
---

# Reference Pipelines

The artisan reference tools produce evidence for maintainers. Google and CLDR
checks are report-only, while the structured export and offline comparison
make installed data inspectable without exposing application-owned records.
No tool writes proposals back into the package or database.

## Structured installed-data snapshot

```bash
php artisan address:reference:export --output=/secure/review/addressing.ndjson
php artisan address:reference:export --output=/secure/review/malaysia.ndjson --country=MY
php artisan address:reference:export --output=/secure/review/revision.ndjson --seed-revision=address-data-2026-10
php artisan address:reference:compare /secure/review/before.ndjson /secure/review/after.ndjson
```

Exports use the single versioned contract
`aiarmada.addressing.reference-export` version 1. NDJSON records cover
countries (including their curated language translations), states, cities, areas, alternate names, roles, typed area
relationships, state links, postcodes, postcode coverage, country currencies,
and country timezones. Explicit field allowlists exclude addresses,
attachments, snapshots, resolution gaps, UUIDs, write timestamps, area slugs,
arbitrary metadata, and provider payloads. Foreign references are represented
by natural/source identities rather than installed row IDs.

The file is published atomically with private permissions. PostgreSQL exports
use a repeatable-read, read-only transaction so all reference tables come from
one database snapshot. Keyset batches and bounded-fan-in external sorting keep
memory and open file handles bounded. The header records the export time,
country scope, configured provider scopes, relevant city, postcode, and
Indonesia village seed policies, and optional seed revision. It labels the
file as an installed-state observation. A seed revision is factual provenance
that the operator must know; it is never guessed from the installed Composer
version.

Comparison validates the header and exact record shape, canonical ordering,
identities, per-record hashes, type counts, and the whole-record digest while
streaming both files. It reports added, changed, missing, unchanged, and
`scope_unknown` rows, with at most 100 example identities. A country absent
from either export or a relevant provider/seed-policy difference cannot
establish deletion; its records are reported as unknown scope. Every missing
row is only an absence observation, even when the scopes match. Duplicate
identities are reported as ambiguous, and the comparison never selects a winner.
The comparison works offline and only proposes review work. Maintainers check
geographic sources, update the owning CSV or provider data and tests, and use
the reviewed source change as the eventual update path.

## Google address data

```bash
php artisan address:reference:google MY
php artisan address:reference:google --all
```

Fetches the country's record from the Chromium i18n dataset (the upstream
commerceguys mirrors for `require`/`upper`/`zip` rules) and compares it
against `address-validation.json`:

- `required`: the profile must require exactly the fields Google's
  `require` letters name (`A`/`C`/`S`/`Z` → `line1`/`city`/`state`/
  `postcode`). A Google-required dependent locality (`D`) is reported
  separately: the model cannot express it.
- `upper`: conventional-uppercase fields must agree.
- `pattern`: one-sided zip rules drift either way, and every Google
  `zipex` example must pass our pattern.
- `profile`: Google describes a country we leave unprofiled.

The fetch is live per run (nothing vendored) through an injectable
fetcher, so tests never touch the network and the package gains no HTTP
client dependency. Google `fmt` templates are printed for context but not
compared: line structure is an editorial choice, not a rule.

First queued finding: MY requires `state` in our profile while Google's
`require` is `ACZ`. Malaysian postcodes route without the state, so the
profile is the stricter side — queued for curation, not auto-changed.

## CLDR subdivisions

```bash
git clone --depth 1 https://github.com/unicode-org/cldr-json.git
php artisan address:reference:cldr MY --cldr=/path/to/cldr-json/cldr-json
php artisan address:reference:cldr --all
```

Diffs `states.json` subdivision codes against the English subdivision
names in a local cldr-json checkout (offline and reproducible). Three
drift classes per country:

- `missing-code`: CLDR lists a subdivision we lack.
- `stale-code`: we list a code CLDR dropped.
- `renamed`: same code, different name (case-insensitive).

Point `--cldr` (or `addressing.reference.cldr_path`) at the directory
that directly contains `cldr-core/` and `cldr-subdivisions-full/`.
`states.json` rows without an `iso3166_2` code cannot be compared and are
counted, not drifted. Set `addressing.reference.cldr_version` to the
checked-out version after the first green run; later runs refuse any
other version so reports stay comparable over time. See
[configuration](03-configuration.md).

## Curation loop

1. Run one or both oracles (single country while editing, `--all`
   periodically or in CI).
2. For each drift row, decide which side is right using the UPU sheet or
   the national post as arbiter — never the oracle alone.
3. Edit the owning data file (`address-validation.json`, `states.json`)
   and note the source in the relevant `05-country-data.md` section.
4. Re-run until clean; keep the CLDR pin current.
