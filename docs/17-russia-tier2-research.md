---
title: Russia Tier-2 Research Log
---

# Russia Tier-2 Research Log

Why Russia stays depth-1, what was ruled out, and the one file
that reopens it. Researched September 2026. Do not repeat this
research — re-pull the named sources at build time instead.

## Decision (September 2026)

Russia stays depth-1 (83 ISO federal subjects). The second tier
is **parked, not cancelled**: the only build meeting the
project's source bar needs the GAR extract (below), which is
unreachable from outside Russia with no public mirror. Revisit
when the file becomes obtainable.

## What Russians actually write

- Russian Post structure: recipient → street + house →
  city/town/village → **[raion, optional]** → [subject] →
  postcode → country. The raion appears on rural mail and is
  skipped for cities. A city address is postcode + city +
  street + house + apartment.
- E-commerce (Ozon/Wildberries pattern): single-field
  autocomplete plus pickup points. Users type city + street;
  the district is auto-filled metadata. Districts earn their
  keep on rural addresses (same-named village
  disambiguation) and official forms.
- DaData (the de-facto autocomplete standard, built on GAR)
  returns `area` = administrative raion ("район в регионе")
  and `sub_area` = municipal settlement as **separate
  fields**, in both divisions.
- Geocoders use municipal vocabulary instead: live
  OpenStreetMap lookups return "городской округ Тверь"
  (urban okrug) and "Калининский муниципальный округ"
  (municipal okrug) where a human would write city / raion.

Conclusion: the administrative raion is the written layer;
the municipal unit is the drawn layer. A package modelling
what people write wants raions (then localities), not
municipal okrugs.

## Options considered

1. **Admin raions from regional lists.** Correct layer, no
   consolidated source — ~80 regional lists stitched by hand.
   Weaker-source work; rejected in favour of (4).
2. **Municipal layer from Rosstat OKTMO open data**
   (`rosstat.gov.ru/opendata/7708234640-oktmo`). Obtainable
   and verifiable (~1,868 units), Cyrillic-only, count drifts
   with reforms — but models what geocoders show, not what
   anyone writes. Rejected as wrong layer for the job.
3. **Skip deliberately.** Free and honest given districts
   barely appear in Russian addresses. This is the current
   posture.
4. **Full stack from GAR (chosen direction, blocked on
   access).** The State Address Register yields raions +
   localities from one official hierarchy (below). Raions
   fall out as the distinct parents of localities — no
   stitching. Blocked only on obtaining the file.

A Malaysia-style dual hierarchy (admin + municipal side by
side) was considered and rejected: Malaysia's trees answer
different questions (governing vs mailing), while Russia's
two maps answer the same question ("what are the districts?")
with contradictory answers. Shipping both pushes an
unresolvable choice onto every consumer.

## The key: GAR (State Address Register)

- Official federal registry: subject → district →
  locality → street → house, coded and hierarchical.
- Publishes **both** trees as separate tables
  (`AS_ADDR_OBJ` + `AS_ADM_HIERARCHY` + `AS_MUN_HIERARCHY`).
  The admin tree is the one to build from.
- Licence: Federal Law 443-FZ declares the data "publicly
  accessible information, published including in the form of
  open data" — compatible, no permission needed.
- Transliteration is undecided (Cyrillic-only source); pick
  one standard (ISO 9/GOST recommended for consistency) at
  build time.
- 2022 claimed territories stay out per the existing ISO
  rule; several subjects (Moscow, Saint Petersburg,
  fully-urbanized oblasts) will be childless — document them
  as such (ACT precedent).

## Access attempts (all failed, do not retry blindly)

- `fias.nalog.ru` (site, `/Frontend`, `/Updates`, direct
  `/Public/Downloads/Actual/` file links): DNS resolves
  (213.24.64.189) but TCP times out on ports 80/443 from
  foreign networks. IP-level blocking. Confirmed from two
  networks, September 2026.
- Fresh official-host recheck, 2026-09-25:
  `https://fias-file.nalog.ru/Frontend` is reachable and its
  developer page advertises "Open data (file dumps)". The
  current page reports a software update on 2026-09-22. The
  public GAR listing renders its format tabs but no file rows;
  the listing's normal `Frontend/OpenDataFiltered` request
  returns HTTP 405. No authentication was attempted or
  bypassed. This is a new official access path, but it did not
  expose a usable dump or either required table.
- `data.nalog.ru`: reachable but only a regional tax-office
  homepage, no datasets. `opendata.nalog.ru`: dead.
  `data.gov.ru`: TLS failure.
- Full GAR mirrors on the open web: none found. GitHub
  holds only tutorial sample chunks (e.g.
  `nurtdinovadf/garbdfias`) and fetch tools pointing back
  at the blocked host.
- KLADR (predecessor, officially dead since January 2018):
  an accessible vendor mirror (`support.edelink.ru/upload/KLADR2.zip`,
  March 2021) holds FMS settlement tables keyed **only to
  federal subjects, with no raion layer** — unusable for a
  hierarchy. Classic KLADR would be 9+ years stale anyway.
- geoBoundaries Russia: ADM0–2 only (no localities), OSM
  volunteer provenance, no parent mapping. Too weak.

## Resume checklist

1. Obtain `AS_ADDR_OBJ` + `AS_ADM_HIERARCHY` (per-region
   files suffice; skip houses/apartments) via a contact on
   a Russian network, a future mirror, or the block lifting.
2. Vet vintage (weekly deltas exist; pin and record the
   date) and confirm the admin tree covers all 83 subjects.
3. Decide transliteration (ISO 9/GOST recommended).
4. Build L2 raions + L3 localities in one pass; raion
   count asserts against distinct parents, locality count
   against the file.
5. Update this log with the source vintage and counts.
