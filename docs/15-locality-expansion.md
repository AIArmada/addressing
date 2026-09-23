---
title: Locality Expansion Method
---

# Locality Expansion Method

How postal-`locality` coverage is grown deliberately, division by division,
for any country. Malaysia was the first worked example and the Batu Pahat
pilot established the method; the per-state results live in
[Country Data](./05-country-data.md). Malaysia paths are shown below —
substitute the country's own files when revisiting another country.

## What the pilot did (Johor / Batu Pahat)

1. **Cascade truth first.** Postal localities narrowed only within their
   own hierarchy, so a picked district never scoped them. Addressing now
   declares `refinedBy: 'administrative_district'` on the MY postal level;
   `parentAreaIdForRole()`, `successorRoles()`, and
   `effectiveParentLevel()` honor it with link proof and state fallback.
   See [Usage](./04-usage.md).
2. **Consumer delegation.** Ilmu360's `/institusi` locality dropdown was
   hard-scoped to the state. It now gates on addressing's effective parent
   role and scopes options through the resolved parent — no hardcoded
   Johor/Batu Pahat logic.
3. **Town expansion.** External town lists named Tongkang Pechah and Parit
   Yaani with no matching row. Both were added as Batu Pahat `locality`
   rows. They share postcode 83010 with the town core, so they link it as
   **secondary** with Bandar Penggaram staying primary (same precedent as
   Rengam town sharing 86300 with Mukim Renggam).

## Per-division audit procedure

Repeat for every top-level division (state/province/WP):

1. List the division's current rows from the country's areas CSV by
   district, plus their links in the postal-code areas CSV.
2. Compare against external town lists (Wikipedia district *Towns*
   sections, postcode directories, council portals). Open every source
   cited; snippets are not evidence.
3. Every town missing from the dataset **entirely** (no row of any type)
   is a candidate. Towns already covered by an administrative row (for
   Malaysia: mukim/bandar/pekan) are not duplicated.
4. Verify each candidate's postcode and parent district from addresses
   and postcode directories before adding.

## Modeling rules

- A new town needs a postcode **and** official recognition (council list,
  postcode directory, gazette-adjacent source). Relocate, don't delete.
- Own postcode → primary link, covering admin rows secondary.
- Shared postcode → secondary link only; the existing primary never moves.
  Invariant: exactly one primary per postcode.
- Keep the provider's `source_id` scheme stable. Malaysia uses
  `my:subdistrict:district:{state}:{district}:{slug}`, type `locality`,
  level 3, parent `my:district:{state}:{district}`.
- Follow the file's existing row and link ordering convention. Malaysia
  keeps CSV order alphabetical by slug within the district group, with
  postcode links adjacent and the primary first.

## Starting another country

1. Audit the country's provider: confirm its hierarchies, levels, and
   area types match reality before adding rows.
2. Declare cross-hierarchy narrowing (`refinedBy`) only where it is real,
   with link proof and a broader-scope fallback for districts that
   genuinely have no localities.
3. Run the per-division audit procedure above across every division.
4. Honor the grouped subdivision/locality presentation default; apps
   override it through config, never with hardcoded scoping.
5. Ship the verification set below in the same pass.

## After changing the dataset

Dataset truth is not database truth. Consumers must re-run `address:seed`
(or the bundled `AddressingSeeder`) to pick up new rows and rebuilt
links; the geography seed upserts areas by `source_id` with stable IDs,
so reseeding is safe.

The stale-seed symptom is over-broad scoping: a district page listing
other districts' towns. The narrowing probes find no district links and
fall back to the broader scope by design. If the dataset parents are
correct but the UI is broad, the database predates the dataset — reseed.

## Verification

- Add a sampling test in the country's geography provider test (for
  Malaysia: `tests/src/Addressing/Geography/MalaysiaGeographyProviderTest.php`)
  covering each new row (type, level, parent, `postal_locality` role,
  district + state postal links).
- `GeographyProviderContractTest` must stay green (parent references).
- Update the country section in [Country Data](./05-country-data.md) in the
  same pass.
- Reseed every consuming app database and spot-check one narrowed page.
