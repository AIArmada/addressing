---
title: Reference Pipelines
---

# Reference Pipelines

Two artisan oracles keep the package's reference data honest. Both are
report-only: they print drift and exit non-zero on any disagreement, and a
human adjudicates — either side can lag a real-world change, and neither
oracle writes back into the package.

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
