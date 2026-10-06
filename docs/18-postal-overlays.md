---
title: Postal Overlays
---

# Postal Overlays

How postcode data is bundled per country, and the overlay verdict for
all 228 providers. The grouping machinery (`postal_locality` roles,
`refinedBy`, grouped subdivision/locality controls) is generic — see
[locality expansion](15-locality-expansion.md) — this doc records which
countries use it and why the rest do not.

## Verdicts

- `complete` — postcode CSVs bundled and imported (see table below).
- `runtime` — resolves postcodes at runtime instead of CSVs
  (Singapore via OneMap).
- `none` — no national postcode system (UPU list + formatter evidence).
  Supplied codes still print as pass-through; nothing to import.
- `admin-ready` — bundled admin already covers towns; only the
  postcode→area links are queued (compilation work, no new rows).
- `expansion` — towns are not covered by bundled rows (or codes run
  below locality granularity); needs doc-15 locality rows first.

## Dataset rules

- File pair per country: `{slug}-postal-codes.csv`
  (`country_code,code`) and `{slug}-postal-code-areas.csv`
  (`postcode,area_source_id,relationship_type,is_primary`).
- Exactly one primary link per postcode that has links
  (enforced by the `PostalCodeCsvImportShard*Test` shards).
- Bundled postcodes are stored at base level. Suffixed formats
  resolve through their base at lookup: full 8-char Argentine CPA
  (`N4419ABC`, `C1406DOB`) strips to the bundled base (`4419`,
  `C1406`) via the country provider's lookup keys. Block-face
  suffixes carry no L2 signal and are out of scope for every
  built set.
- Single national codes shared by many rows import code-only (no
  links): the code does not discriminate areas (Niue, Nauru,
  Turks and Caicos, Anguilla).
- Shared codes take primary = office-holding / admin-center commune
  (documented per country below); catch-alls with no office holder
  take the largest commune.
- Excluded: delivery-point-only fragments (Les Abymes 97101–97109
  sectors are internal routing, not delivery codes), delivery-type
  variants (Monaco 98001–98099), PO-box-only ranges (Andorra la
  Vella box ranges), uninhabited stations without admin rows
  (Clipperton 98799, Greenland 3972/3982/3984 — 3985 Nerlerit
  Inaat filled → Sermersooq in B1), and private
  carrier-internal codes (Parcelforce Panama agency codes).
- Liechtenstein 9489 is omitted: sources split between Schaan and
  Vaduz and the newest compilation drops it. Re-add from
  Liechtensteinische Post evidence only.
- Iceland 512 is omitted: the register serves Ísafjarðardjúp farms
  near Hólmavík and the commune (Súðavík vs Ísafjarðarbær) cannot
  be settled from published sources. Re-add from Pósturinn
  evidence only.
- Faroe Islands FO-485 spans Runavík and Eystur (Skálafjørður):
  Runavík primary, Eystur secondary.
- Saint Lucia LC01 501 Marisule is dual-linked: Castries primary
  per the government addressing table, Gros Islet secondary (border
  area).
- Parked for lack of a verifiable complete source (do not ship
  partial compilations):
  - DR Congo: UPU addressing sheet codEn.pdf confirms a 7-digit
    postcode system (province/city/sector/office structure, e.g.
    1003071/1004131 Kinshasa, 3202011 Kwilu), so the verdict is not
    `none`. No allocation source: GeoNames has no CD export (404)
    and Mapanet CD rows carry empty codes.
  - Bahrain: overturned — SLRB states 478 Logical Map Boundaries
    following the administrative blocks, matching the shipped 479
    (the earlier ~1000+ was the number range, not the used count).
  - Egypt: access recheck 2026-09-25 (retried same day): https://egyptpost.gov.eg/
    still HTTP 403 and https://www.egyptpost.org/ timed out. The
    official BareedMall page
    (https://bareedmall.egyptpost.org/bareedmallstorefront/bareedmall/ar/v/Enpo)
    is a stamp shop, not a postal directory; its current indexed copy
    has no allocation rows and direct fetch returned 502. No complete
    source, row count, data vintage, or reuse terms are available.
    MapAnet remains thin (371 rows / 81 codes); needs Egypt Post data
    or a reachable full office directory.
  - Trinidad and Tobago: access recheck 2026-09-25: current TTPost
    status page (https://ttpost.net/news/postal-code/) says the system
    is complete and codes are available for all addresses, but queries
    are handled by email or WhatsApp; it publishes no allocation rows
    or downloadable list. The 2017 FAQ
    (https://ttpost.net/2017/02/27/trinidad-tobago-postal-code-system-tt-pcs-2/)
    likewise directs users to TTPost outlets. No dataset vintage or
    reuse terms are published; GeoNames has no TT export.
  - Kuwait: access recheck 2026-09-25: the official MOC page
    (https://www.moc.gov.kw/en/important-links?tab=2) advertises
    separate P.O.-box and block-number tables, but its indexed view
    contains only empty table headers and filters; direct fetch timed
    out. No allocation rows, vintage, or reuse terms were retrieved.
    PACI Kuwait Finder remains unreachable and GeoNames has no KW
    export. MapAnet's 349 rows remain unattributable at L2 (Jahra's
    92 rows lumped under one locality; Ahmadi has 9; block suburbs
    are empty).

  - Timor-Leste: access recheck 2026-09-25: https://ctl.tl/ failed
    DNS resolution (`nodename nor servname provided, or not known`).
    The official UPU sheet
    (https://www.upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/tlsEn.pdf)
    already documents TL+5 and examples (including TL11212 and
    TL42000); its indexed copy is about five years old and contains
    no allocation table. This qualifies the “brand new” description
    in the August 2026 profile but does not provide a usable list.
    No allocated-code rows, current vintage, or reuse terms are
    available; revisit when Correios de Timor-Leste publishes a list.
  - Uganda: access recheck 2026-09-25: https://ugapost.go.ug/ redirects
    to https://web.ugapost.go.ug/ and returns HTTP 200 with a 559-byte
    app shell, not a postcode allocation. The official UPU sheet
    (https://www.upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/ugaEn.pdf)
    timed out while fetching. UCC's 2026-06-11 “Allocation Chart” page
    (https://www.ucc.co.ug/download/allocation-chart-uganda/) exposes
    no postcode rows. The Ministry of ICT's FY2025/26–2029/30 plan
    (https://ict.go.ug/public/site/documents/ict-strategic-plan-202526-202930.pdf)
    labels the National Postcode and Addressing System “Concept” and
    describes work to develop/adopt it. No allocation vintage, rows,
    or reuse terms are published. The E-Posta API remains
    authentication-gated; needs a public Posta Uganda allocation list.
  - Cape Verde: access recheck 2026-09-25: https://www.codigopostal.cv/
    timed out after 12 seconds. The current operator FAQ
    (https://www.correios.cv/faq, HTTP 200; last updated 2020) gives
    only three four-digit examples (7600, 7601, 7602) and points to a
    “Pesquisar Códigos Postais” home-page icon; the live home page
    exposes no finder. The branch-contact page
    (https://www.correios.cv/contactos; last updated 2019) is not a
    territory-wide CPN table. No allocation vintage or open-data
    reuse grant was found; the general terms
    (https://www.correios.cv/termos-e-condicoes) retain an
    all-rights-reserved notice. GeoNames' registry has no CV rows.
    Needs the complete Correios list.
  - Myanmar: access check 2026-09-25: MapAnet MM holds Yangon
    townships only (89 rows / 87 5-digit codes, r1=11); the live
    system is 7-digit per UPU mmrEn 11/2022 (0505001, 1118001,
    0213202) with township-level allocation. GeoNames has no MM
    export and youbianku carries region prefixes only. Needs a
    township code list plus a township→district crosswalk (townships
    unbundled); do not ship the Yangon fragment.
  - Guyana: access recheck 2026-09-25: the official finder
    https://guypost.gy/find-your-postcode/ returns HTTP 403. The
    official explainer
    https://guypost.gy/understanding-postcodes-in-guyana/ describes
    the 7-digit fields and one example (2210201), but contains zero
    allocation rows and publishes no dataset vintage or reuse terms.
    GeoNames' registry has no GY rows. Needs a public allocation list
    from Guyana Post.
  - Tajikistan: access recheck 2026-09-25 surfaced an official
    Tajik Post index page
    (https://tajikpost.tj/ru/перечень-почтовых-индексов-таджикистан/)
    in the search index with oblast, district, and locality columns,
    but a direct GET returned HTTP 404. The indexed copy provides no
    stable downloadable table, stated vintage, or reuse terms; a
    complete live allocation could not be verified. No CSV was built.
  - Lesotho: access recheck 2026-09-25: the operator URLs
    (https://lesothopost.org.ls/ and https://lps.org.ls/) are not
    fetchable from this network. The UPU addressing sheet
    (https://www.upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/LSOEn.pdf),
    dated 09/2004, confirms 3-digit codes and only the Maseru 100
    example; it has no allocation table. No complete row count,
    current vintage, or reuse terms are available. GeoNames has no LS
    export and third-party lists still conflict (Leribe 300 vs 9730);
    MapAnet crawl returns zero records.
## Built datasets

| Country | Code | Codes | Links | Source |
|---|---|---:|:---:|---|
| Afghanistan | AF | 1408 | 1408 | Afghan Post official finder GeoJSON /client_postal_code (1,408 six-digit zones PPDDZZ: province 10–43 + city 01–50/rural 51–99 + delivery zone; rural_dist/city_distr + post office + centroid per zone; live re-pull 2026-10-03 set-identical); rural zones join districts province-scoped + transliteration aliases, city zones join capital districts (Herat→Hirat, Mehtarlam, Matun, Taloqan, Maydan Shahr, Shiberghan, Maymana, Parun); M7 re-verification vs COD-AB v03 polygons (every centroid PIP + zone-overlap): 12 links moved off Nominatim/OSM-boundary errors — Want→Waygal (Want Waigal), Pusht-i-Koh→Shindand (99.9%), Alfaroq→Feroz Koh (76%), Nawa-i-Mesh→Baghran (99.8%), Chehelgazi→Qaysar (52% + shrine in-polygon), Dand Ghuri→Pul-e-Khumri (94%, false friend of Dahana-e-Ghori), Darwaz→Darwaz-e-Payin (99.5%), Sayed Abad→Sar-e-Pul (91.5%), Kaldar/Hairatan zone→Sharak-e-Hayratan (99.7%), Allah Yar→Jawand (53% + toponym in-polygon), Sang Atash→Ab Kamari (81%), Jalalabad Airport→Behsud (100% + rural Boundary); Khaibar kept Qaysar on an Almar/Qaysar strip tie; verified keeps Gershk/Marja/Babajee→Nahr-e-Saraj/Nad-e-Ali, Takhtapul→Spin Boldak, Dand→Kandahar, Lija Mangal→Lija Ahmad Khel, Pamir→Wakhan, Sheltan→Shigal, Dilaram→Khashrod, Abshar→Dara, Morghab→Feroz Koh, Eshkmesh/Eshkashem tag-swap pair; 2 fills (396701 Bahramcha→Deh-e-Shu — Bahramcha is Dishu's capital — no Garmser secondary on 34% alone; 425201 Gizab→Gizab AF2507, Daykundi tag only); 401/401 districts covered, zero multis |
| Åland | AX | 33 | 33 | Åland postcode register via Dörnbach enumeration + Postnord 2024 (AX-22100–AX-22950; 5 PO-box codes excluded); bare domestic input gains the AX- prefix at lookup |
| Albania | AL | 524 | 525 | Posta Shqiptare official branch list (519 offices + Tranzit 1700; Gramsh 3301–3310, Peqin 3501–3506, Tepelenë 6301–6311, Përmet 6401–6409 filled) + 2017 Tirana coverage doc (bashkia per office, pins 1029 Kamëz primary + Tirana secondary) + postzipcode village tables + Nominatim/Photon municipality resolution; 138 office→municipality retargets off old-district blocks (61/61 covered); 4511 Shenkoll + 8408 Trebisht + 8409 Fushë-Bulqizë newer offices kept, 8520 Morinë customs weak-kept, 8707 GN-only franken-row dropped (Tropojë caps 8706); homonym keeps 3019/3025 Elbasan, 2504 Kavajë, 1046 Tirana, 1507 Krujë, 7024 Korçë, 9335 Mallakastër; weak keep 5021 Berat |
| Algeria | DZ | 3908 | 3908 | Algérie Poste office codes via geoalgeria/baridimap (3,908 offices, 1:1 code↔office incl. 49–69 new wilayas; commune→daira via JO/frwiki; GN agrees on 2,918/3,162 codes; 119 renumbered-olds dropped as dead; 125 unverifiable GN-only dropped; Mapanet DZ codes discarded, 6/1045 office agreement — daira-constructed; BOD/Debdeb/Aïn Smara kept as communes per infoboxes+ONIL, mapped to In Amenas/El Khroub; M7: code-set re-verified 3908/3908 zero rot + 19 retargets to JO dairas at 3 signals each — Aflou-batch 03024/03030/03031→Aflou, 03027/03041→Oued Morra, 03034→Aflou, 03013→Gueltat Sidi Saad; Djelfa-batch 17043/17047→Birine, 17051→Sidi Ladjel, 17040→Had Sahary, 17021/17061→Faïdh El Botma; 14018→Hamadia, 05046→Djezzar, 12044→Negrine, 12043/12045→Bir El Ater, 26058→Tablat; 548/548 covered) |
| American Samoa | AS | 1 | 0 | UPU single-code list (96799); ZIP+4 strips to 5-digit base at lookup |
| Argentina | AR | 2501 | 3115 | Correo Argentino official finder backend wsFacade.php action=localidades (23,544 localities, cp+partido per province letter; 27 name aliases incl. Pueyrredón→La Capital-SL, J.F.Borges→Capital-SDE, Hucal→Huncal-areas-spelling) → code→partido majority primary (2,080 codes; 1,520 agree with GeoNames rollup, 108 decisive GN overruled, 69 ties single-primary-broken by GN-agreement else alphabetical); 109 GN-only codes kept (DPTO-hint/gazetteer/Photon+Nominatim state-constrained county map — unconstrained mapping caused Güemes-Chaco/San Antonio-Jujuy/Capital-Catamarca collisions, fixed); all 28 GN-unresolved + 18 GN-ties resolved officially; CABA 312 C-codes via Mapanet rows coord-clustered (largest 1.5km cluster) + Nominatim barrio→comuna, 15 official action=cpa street probes anchor/override (C1406/C1407/C1416→C10, C1405/C1414→C15, C1428/C1429→C13, C1440→C9, C1086→C1; C1137 C3-primary + C1-secondary, C1416 C10-primary + C11-secondary; C1223/C1480 single garbage rows dropped, official area probes contradict; C1163 lat sign-fixed); MapAnet interior final confirm (12,107 rows): 30-sample 14 agree, 5 conflicts all kept for built on decisive CA majorities (4313/4419/4504 minority-locality samples, 8373 Lácar 4v3), 4635 +Humahuaca secondary on 1v1 evidence tie, 6336 kept Catrilo (no Mapanet rows; GN letter L + 63xx range + proximity beat single OSM point-geocode); CPA base level only (interior 4-digit + CABA C+4-digit) — full 8-char block-face codes strip to base at lookup, suffixes out of scope; B19: live georef + operator refetch → 17 areas fixes (Pueyrredón law rename et al), 2 San Miguel retargets, 3151/5400 tie flips, 6 CABA remaps from addressed usage (Mapanet-cluster ~40%-wrong in 14xx fringe); 13 holds |
| Armenia | AM | 781 | 781 | Haypost api.haypost.am postalIndex (official finder backend, 780 branch-index records on B11 re-pull with coords; city typos fixed via address village: Doxs→Doghs, Xursal→Ghursal, Mexri→Meghri, 0919 Artashat-village→Artashar via branch coords; B11: +0109 Kentron +0236 Ashtarak new branches, 3518 Vaghatin Goris→Sisian per mtad.am, 2102 Tashir kept — Spyur+OSM live though API-dropped); non-Yerevan via city→GeoNames new-municipality join + coord-nearest disambiguation (8 wiki/address manuals: Mkhchyan Artashat, Yerazgavors Akhuryan, Nor Kyank ×2 Vedi/Artik, Nerkin Sasnashen Talin, Nor Geghi Nor Hachn, Verin Karmiraghbyur Berd, 4206=4010 city); 20 far-match audits fixed 10 (Artashar/Armavir-village/Mrgashat/Vardanashen Metsamor, Khoronk Araks, Getashens Martuni, Teghut-Lori Alaverdi, Yeghegnut-Lori Pambak, Kasakh Nairi; Hoktember=Sardarapat→Armavir; 3014 Dubai-coord + 4112 Tavush-region Haypost data errors noted); 93 Yerevan offices district-mapped via Photon reverse-geocode (0074 Shengavit + 0010 Kentron anchors match Spyur/postalcode.pro; 0022 Avan via Avan-Arinj address); 132 Mapanet-only codes dropped as office-number artefacts (Spyur: office №7 postcode is 0074, not 0007; city sub-office + Vardenis-village extras unverified) while 20 Haypost-only kept (9 Yerevan gaps + 0102–0108 + Teghut/Abovyan/Vayk); ADDED am:municipality:khoy to areas (8th Armavir community per Armstat 2025, 15 codes) |
| Andorra | AD | 7 | 7 | UPU AND profile + parish list (AD100–AD700); bare domestic input gains the AD prefix at lookup |
| Anguilla | AI | 1 | 0 | UPU single-code list (AI-2640); bare domestic input gains the AI- prefix at lookup |
| Azerbaijan | AZ | 1186 | 1186 | GeoNames dump at district/city level (AZ 0100–AZ 8011; city branches split to city municipalities); bare/spaceless input gains the AZ prefix + space at lookup |
| Austria | AT | 2501 | 2623 | GeoNames dump at district level (1000–9992; 19k street rows; 117 cross-district codes multi-linked, row-majority primary, secondaries need 2+ rows; 30 ties broken by PLZ-district map, 4550 seat-rule Kirchdorf; 124 admin2-less codes manual: Vienna specials to Vienna state, 89 statutory-city/office codes OSM-verified incl. 2127 Mistelbach + 5089 Salzburg-Umgebung + 4985 Ried; 3 PLZ-map-only codes 8471/8565/9104 excluded unverified; B12: 16 primaries retargeted — St. Pölten batch 3100/3104/3105/3107/3109/3140/3151 + Wiener Neustadt batch 2700/2703/2705/2706/2707 to statutory cities, 1140/1210 to Vienna, 2231 Gänserndorf, 2680 Neunkirchen — + 3140 district secondary) |
| Australia | AU | 3165 | 4186 | ABS ASGS Ed3 allocation join Mesh Block→POA 2021 × →LGA 2021 (368k blocks; GeoNames admin2 proven unreliable: 3585 Swan Hill misfiled Gannawarra, 3691 Kiewa misfiled Albury, 0872 Alice misfiled Laverton, 4825 Mt Isa misfiled Carpentaria); MB-count majority primary, secondaries need 2+ MBs (180 single-MB slivers dropped); 527 LVR/PO-box codes via place→street-code ABS primary + hand-mapped mail centres (Hunter MC Newcastle, New England MC Tamworth, Mid North Coast MC Kempsey, Gippsland MC Morwell, Sydney Gateway Granville→Cumberland, AFPO Sydney, Dangar→Newcastle, Winnellie→Darwin, Navy Warships→Rockingham, Antarctic 7151→Kingborough); 9 MB-ties adjudicated (2178 Penrith infobox-order, 2808 Cowra village, 4497 Balonne Thallon, 6420 Narembeen Muntadgin, 2721 Bland, 3572 Loddon, 5150 Burnside, 5273 Kingston-SA, 5310 Loxton Waikerie); tri-state 0872 7 links (MacDonnell P + Central Desert + Barkly + Ngaanyatjarraku + East Pilbara + SA/NT L1s); 4825 Mt Isa P + Boulia + Cloncurry + Barkly (Alpurrurulam) + Burke; 2406 Moree Plains P + Balonne + Goondiwindi straddle exception; 3586/3500 single (Mallan=2734, Paringi=2738 per AusPost — GN rows + wiki stale); ACT→L1 (no LGAs); unincorporated SA/NSW/NT/Vic + APY/Maralinga→L1s; JBT + externals 6798/6799/2899 + pseudo-POAs 9494/9797 unlinked (no areas); 3989 junk + 1419/2058 unlocatable MCs dropped; AusPost finder has LVR index gaps (2052/5005/1001 false zeros, mirror-confirmed) so zeros never drop; B19: ASGS-gazetted re-audit → 7 flips (5150 Mitcham, 5273 Naracoorte, 7469 West Coast, 0862 Barkly, 0885 Groote, 2335 Singleton, 7215 Break O’Day) + 13 secondary adds + 9 drops; APY/Maralinga rows added |
| Bahrain | BH | 479 | 479 | Block number = postcode per UPU BHR.pdf (3–4 digits; 101–1218 within the UPU 1XX–12XX range; first-two = region applies to 4-digit codes): youbianku governorate block table (479 blocks, zero cross-governorate; A'ali area genuinely spans three governorates — Northern 732/734/736/738/740/742/744, Capital 733/743/745 per Sanad/Nasfa addressed evidence, Southern 746+748); MapAnet BH full pull re-verified 2026-10-03 (141 rows): 106/106 certain-town blocks agree with zero disagreements (zero-padded 0101=101; x00 area bases 300/500/600/700/900/1000/1200 excluded as synthetic multi-town aggregates); SLRB count recall is 478 Logical Map Boundaries vs 479 shipped — off-by-one unresolved, no SLRB block list obtainable; no open-data block list on data.gov.bh (ODS portal has stats only) |
| Bangladesh | BD | 1373 | 1373 | GeoNames vintage (1349) verified vs 2018 Bangladesh Post finder + UPU profile + 24 post-2018 fills (Mapanet/postcodebase/GPO dir; 2024 official Cumilla page); cross-block keeps 1333-35/3893/5470/8013; Mapanet typos rejected |
| Barbados | BB | 1176 | 1185 | BPS finder JS dataset (BB11000–BB27193 incl. 15 office base codes; 9 BB23 splits dual-linked; BB190215 6-digit typo omitted); bare domestic input gains the BB prefix at lookup |
| Bermuda | BM | 80 | 113 | BPO 2013 Blue Pages street vote, 2,063 rows (B4 rebuild; GeoNames BM agrees on the 80-code street set + HM/GE dual sides; box codes GE CX / HM GX class excluded); HM 10–12 City-primary + Pembroke, HM 08/09/17 City-secondary, HM 14/15/16/18/19/20 Devonshire-secondary, GE 03/05 Town-primary, GE 01 parish-only, HS 02 triple, plus FL/DV/PG/SB/WK splits; compact input gains the official space at lookup |
| Belarus | BY | 3140 | 3140 | B13 re-verification: GeoNames dump at district level (3,133 rows / 3,123 unique, zero drift; French-transliteration admin2 fuzzy-joined oblast-scoped + explicit aliases; Minsk city admin1-04 + 9 city rayons → L1 Minsk city; 5 1v1 ties re-adjudicated with losers' true codes found — 211227→Liozno, 211657→Polotsk (Gomel village), 220024→Minsk city (Kholopenichi=222024), 222374→Miadel (Budslav, Belposhta-cited), 222834→Pukhavichy (Krasny Oktyabr; Urechye=223834); Oev=Loyew by shared admin2code 625906; empty-admin Rodno row Berestovitsa 231778 by district-center place) × Mapanet (33 raions + integrator Talachyn-town pull) + Belposhta-family branch list (2,449 codes, full-sweep 1,408 agree) + wiki infoboxes: 25× Byaroza-cluster 225205–225247 Brest→Byaroza (GN lacks Byaroza admin2), 211440→Polotsk, 211620→Verkhnedvinsk, 231470→Dzyatlava, 247711→Kalinkavichy, dropped corrupt 213918 (transposed Vawkavysk) + 247047 (of 247407), filled 19 town/city codes (222024/247407/211631/213910/211091/211092/Novopolotsk 211441+211443–211449+211500+211501/Vawkavysk 231891+231894+231896); ~70 Mapanet-only extras held (single family) incl. Byaroza-town 225203/225204/225208; 231894-vs-231918 Dulevtsy conflict held; branch-doc city-row error class (213660/222131/222150/222132/222152) kept per GN unanimity; postal files CRLF |
| Belgium | BE | 1146 | 1146 | GeoNames dump at province level (1000–9992; zero cross-province codes; 12 seat codes verified 1000 Bruxelles/2000 Antwerpen/3000 Leuven/4000 Liège/5000 Namur/6000 Charleroi/7000 Mons/8000 Brugge/9000 Gent/1300 Wavre/3500 Hasselt/6700 Arlon; Brussels 22 codes to Brussels-Capital region) |
| Bhutan | BT | 38 | 38 | B16 inline (2 renames Chhukha + Lhuentse incl. slugs, zero leg moves; gate_bt ALL PASS): 37/37 youbianku office-table codes match attribution incl. cross-block 21104→Dagana, 11/38 OSM hits all match; 36001 Tsirang kept (operator provenance + HQ pattern). Original bundling: Bhutan Post legacy finder, all 20 dzongkhags queried (11001–46002; office base codes only; finder Gewog column holds office towns so links are district-level; Samdrupjongkhar/Trashiyangtse spelling variants) |
| Bosnia and Herzegovina | BA | 563 | 625 | BiH routing manual 2013 (7,363 settlements; 70101–89247; 55 cross-boundary codes dual-linked; 71335 Pržidi omitted); B14 fix 2026-10-04 (gate_ba.py): 71000→4 Sarajevo legs, 71123+INS, 74208 Stanari-primary, 77253 Bosanski-Petrovac-primary, 76000 dropped + 76300 Bijeljina filled, 53 multis, unit-grain codes held; OPEN-12 fill 2026-10-06: HP routing table 2013 + 3 live operator networks → 47 fills (37 proven-live incl. duals 71124/71213 + 10 beyond-65 incl. duals 71214/71216, 71335 omission overturned), 79293 dropped + 72293 Opara, retired 74321/75000/88267 absent (successors live), 57 multis, 10 likely-live + 15 likely-retired + singletons held, 55-vs-50 dropped-unreconcilable |
| Brunei | BN | 394 | 394 | post.gov.bn finder scrape (kampong→mukim; 23 Peti Surat PO-box + 87 ministry large-user rows excluded; Kampong Amo A/B/C unfetched — 2026-10-06 retry PROVEN Amo→PD1151 via Buku Poskod (B/C PD1351/PD1551 family-only, held out) + 16 more gap kampungs, Kedayan BK incumbents kept 1v1; finder spells Burong Pinggai Ayer, Kampong Peramu = Peramu) |
| Brazil | BR | 5547 | 5547 | GeoNames general-CEP dump (5,525 XXXXX-000 codes) joined EXACT on IBGE 7-digit codes, zero name matching; 18 DF satellite codes → Brasília municipality; 12 admin2-less rows ViaCEP-adjudicated (Amapari→Pedra Branca 1600154, Livramento do Brumado→Livramento de Nossa Senhora 2919504, GN mislabels Assis Chateaubriand→Riachão do Bacamarte + São Bento de Pombal→São Bentinho + Mosquito→Palmeiras do Tocantins corrected; Jamari 78937→Itapuã do Oeste by seat coords); 76861-000 Itapuã added via ViaCEP (GN gap); 10-code ViaCEP spot-check 9 direct + Fernão 17455-000 confirmed via municipal letterhead (ViaCEP has proven -000 gaps: 01000-000 also silent); 65 municipalities codeless (GN snapshot gaps); street-level suffixes out of scope (need licensed DNE; no prefix-strip normalizer — metro prefixes can span municipalities). B21 (2026-10-06): 38 RO 789xx→768/769xx renumbers + 78937 dup drop + 68948 Serra relink + 22 general adds; follow-up +7 (3 RO + 3 small-city + Bujari 69926); 10 RO + 42 unlinked + DF-19 held |
| Bulgaria | BG | 4351 | 4363 | B17 inline (gate_bg ALL PASS): 22 fills (GN-live + wiki/census/office/OSM: 2655/2658/4446/4456/4848/4888/5156/5399/6233/6631/6832/6838/6839/6863/6886/6897/7918/8289/8339/9495/9496/9024) + 34 removals (Teteven-13 + Yablanitsa-6 dead blocks, 13 orphan singles, branch codes 6609/7101) + Malko Tarnovo 835x→816x renumber x5 — REVERSING the build note (operator + Google/hotel + OSM + 2017 tender prove 816x live; mapanet/WPC 835x is stale lineage; 8 villages held) + 2789 +belitsa / 2791 −yakoruda legs + office-wins rule (7 DONOTADDs: 2096/2190/3264/5173/7685/9822/9494; GN stale blocks Mirkovo-209x/Nesebar-822x/Malko-835x/Ivaylovgrad-69xx); 12 dual-linked; ~46 holds (24 orphan singles + wiki-only + GN-opens + Malko-8). Original bundling: CRC/Bulgarian Posts 2016 directory (5358 settlement rows) + GeoNames screened vote-by-vote (598 fiction rows + 354 stale codes dropped; 12 cross-municipality codes dual-linked; Sarnitsa post-2015 split + Gurkovo/Ognen/Ravninata admin-truth overrides; Byala Ruse/Varna + Dobrich city/municipality disambiguated) |
| Burkina Faso | BF | 467 | 467 | Live La Poste BF finder (`laposte.bf/codespostaux` endpoints, Oct 2026): 350 commune rows + 122 quartiers (Ouagadougou/Bobo-Dioulasso only) + 109 agences = 467 distinct 5-digit codes, 47/47 provinces, zero cross-province codes, one 2-digit block per province (split pairs share 62 ex-Soum / 79 ex-Tapoa; Tapoa/Soum communes split per COD-AB: Dyamongou Botou+Kantchari, Karo-Peli Arbinda+Koutougou); UPU 10000/10010/70000 confirmed, UPU 91001 BAMA illustrative (finder: 91001 = Orodara agence, Bama = 90200); homonyms BOUSSOUMA/NAMISSIGUIMA/DJIGOUERA resolved by province both directions (10/10 reverse-probe agreement); 2 codeless communes (Komsilga, Silly); CINKANSE postal locality 70550/70551 → Boulgou (absent from COD-AB); NIAMBOURI = Niabouri typo |
| Canada | CA | 1663 | 1673 | GeoNames FSA dump (1,651 FSAs: urban→CSD via admin2/place join + 445-code alias table for amalgamated-city sectors — Toronto/Ottawa/Hamilton boroughs, Montréal/Laval/Longueuil/Gatineau/Québec/Lévis/Saguenay/Trois-Rivières/Shawinigan/Sherbrooke boroughs, CBRM/Halifax/Dartmouth/Bedford/Sackville, North/West Vancouver, Brantford; 186 rural X0X→province); 63 NB places resolved to 2023 entities via GNB Socrata ward polygons + full-gazetteer coords (Coverdale→Salisbury, Kingsclear→Hanwell, Youngs Cove→Arcadia, Smiths Creek→Butternut Valley, Apohaqui→Kings RD, Kingston→Fundy RD, Debec→Lakeland Ridges, Inkerman→Shippagan, Deer Island→Southwest RD, Baie-Sainte-Anne→Kent RD; E7C Saint-Basile→Edmundston per 1998 amalgamation over off-point) + Wikipedia reform extracts; 6 NS rural towns via StatCan 2021 CSD point-in-polygon (Iona/Big Bras d'Or→Victoria Subd. B, Loch Lomond/Fourchu→Richmond Subd. B, Coldbrook→Kings Subd. C, Christmas Island→Cape Breton); 9 dual links (E1H Beausoleil P + Maple Hills, E2E Rothesay P + Quispamsis, E2H Saint John P + Rothesay, E3C Fredericton P + New Maryland, R1B St. Clements P + St. Andrews, V7J/V7P North Van District P + City, J6Z Bois-des-Filion P + Lorraine, J7T Saint-Lazare P + Les Cèdres); audit fixes — West Island cities (Hampstead/Westmount/Côte-Saint-Luc/Montréal-Ouest/Kirkland/Dorval/Pointe-Claire/Beaconsfield) + North/West Vancouver + Brantford + Red Deer County rescued from metro admin2, Montréal neighbourhoods (Saint-Michel/Mercier/Saint-Henri/Saint-Pierre) rescued from distant same-name cities; LDU strips to FSA at lookup. B21 (2026-10-06): G0B+H4Z dropped (retired/unassigned), 14 FSA adds + 15 legs (R5-block Springfield/Taché, S7 Saskatoon, V7Z dual); S7B/S7C secondaries held |
| Cambodia | KH | 1633 | 1633 | B16 inline (5-leg Samraong collision fix 240401-05 → OM municipality; gate_kh ALL PASS): 210/210 L2 exact vs NIS district list, 1628/1633 district-part audit fit, 1537/1633 COD-AB overlap. Original bundling: Postcode = NIS commune code: cambodiapostalcode.com 205 district pages (1601 commune codes, NIS-exact incl. Aoral 0504 + 120209 Phsar Chas UPU anchor) + NIS stat.go.jp docs for 5 CPC-missing districts (Basedth 050101–050115, Chum Kiri 070401–070407, Ou Reang 110301–110302) + ybk Srei Santhor-correct blocks for Chantrea 200101–200106 + Preah Vihear city 130801–130802; 22/23/24 use postcode numbering Kep/Pailin/OM (UPU compendium Pailin 230200 + areacambodia Kaeb 220201 + Pailin 230201–230204 ×3 sources; OM=24 by elimination) so CPC's NIS-numbered Kep/Pailin/OM blocks mechanically remapped (district parts kept: ybk's OM 04/05 swap + Kampong Speu shift + Srei Santhor 02xxxx fabrication + 030301 dup all rejected as ybk corruption; zipcode.com.ng shown to copy ybk); 060705 Kraya reassigned Ballangk→Santuk (misfiled page); UPU 141006 = stale NIS-2009 Prey Veaeng numbering (current city block 141001–141004); Ou Krasar moved Damnak Chang'aeur→Kaeb post-2009 (230102 gap preserved); Bokor 0709 separate from Chum Kiri; XX000 province-base codes excluded (zone routing, commune system complete) |
| Denmark | DK | 1159 | 1159 | GeoNames dump joined on municipal codes (0800–9990) |
| Djibouti | DJ | 10 | 10 | UPU DJI addressing profile 05/2020 (La Poste de Djibouti; footer (c) upu.int): 77101 Djibouti Ville + 77102–77105 Marabout/Einguela/Nasser/Balbala + 77201/77301/77401/77501/77601 Arta/Ali Sabieh/Dikhil/Obock/Tadjourah villes; townoak 2nd-signal on 7 codes (77102–77104 UPU-only); rural routes via capital codes — 6/20 linked is complete by design |
| Dominican Republic | DO | 528 | 530 | B16 inline (1 rename Quisqueya incl. slug, zero leg moves; gate_do ALL PASS): bundle set == live INPOSDOM data.json set exactly (528/528); both multis dual-confirmed by operator rows; 6 GN-only DN codes correctly excluded (operator-silent). Original bundling: INPOSDOM codigo-postal data.json (1403 sector rows; 10100–94100 + Moca 53xxx + Santiago 58081 overflow; 2 junk rows excluded; DN 10100–10699 links L2; 71100 Pueblo Viejo/Guayabal + 81100 Cabral/Jaquimeyes dual-linked, largest primary) |
| Ecuador | EC | 1225 | 1225 | B17 inline (10 INEC-2026 formal renames incl. slugs + 176 leg moves, zero code changes; gate_ec ALL PASS): bundle set == GN set exactly (1225/1225, 0 multis); 080701-03 La Concordia legacy-08 prefix kept; 4 zone-90 legs re-confirmed (El Triunfo/El Carmen/Cotacachi); Borbón held (no cantonization law). Original bundling: GeoNames dump at canton level (010101–900004; all 222 cantons; undelimited-zone codes to absorbing cantons: El Piedrero El Triunfo, Manga del Cura El Carmen, Las Golondrinas Cotacachi) |
| Eswatini | SZ | 81 | 81 | Region-grouped letters H/L/M/S (H100–H126, L300–L317, M200–M223, S400–S415): WP postcodes 76 + H101 Swazi Plaza fill (WP + youbianku agree) + youbianku-confirmed extras H121 Emsahweni/H124 Mahlanya/H125 Ebuhleni/H126 The Gables/M223 The Hub; post-fix set equals youbianku 81 exact (H 25/L 18/M 22/S 16); all links region primaries by code letter incl. M211 Sithobela + M214 Siphofaneni on Manzini despite Lubombo tinkhundla (postal/admin mismatch); UPU H100 Mbabane anchor |
| El Salvador | SV | 262 | 262 | 262 district codes × 44 post-reform municipalities (gist/youbianku cross-checked, groupings verified against reform annex; upstream typos fixed via third sources: Candelaria 1302→1402, Alegría 3404→3402, Cacaopera 3216→3203, Chilanga 3203→3205 per mapanet/UPU compendium) |
| Estonia | EE | 5481 | 5499 | GeoNames dump joined on municipality names (5293 codes; Toila rows → Jõhvi post Nov-2025 merger) + 8 GeoNames-missing cities via mapanet/WPC town sets with business-register spot checks (112 town codes) + 84 postiindeks.ee/ADS-agreed fills (Narva 21026-21076 x50, Noarootsi 912xx x21, Viimsi/Hiiumaa/Antsla/Tartu-vald/Kehtna/Tallinn/Peipsiääre/Viljandi-vald/Võru-vald 13); 11 EHAK cells current (430/431 unswapped, Jõhvi 250, Antsla 145, Märjamaa 502, Narva-Jõesuu 515, Põhja-Pärnumaa 637, Saue 725, Sillamäe 736, Tori 806, Valga 857); 17 boundary codes dual-linked, primary = majority rows else town side |
| Ethiopia | ET | 50 | 81 | Mapanet full pull (343 town rows, 50 four-digit codes 1000–7260) + Photon revgeo (342 rows; county = woreda-or-zone, Amharic ሰሜን ጎንደር/ሰሜን ወሎ decoded) × Wikipedia woreda→zone crosswalks (Oromia/Somali/Afar/Amhara/Tigray/SNNP district lists) + 93 Nominatim singles for null-county towns; post-split zones applied (East Bale 8 woredas per townsvillages/codepen, Gondar N/C/W per EDRMC hotspot + researchsquare, West Omo = Maji/Bero/Surma, Bench Sheko = ex-Bench Maji remainder, Kwiha/Adi Gudom/Samre→South East per EDRMC town table over Photon/OSM Southern mislabel); 22 multi-zone codes majority-primary (3260 Jarar P + Nogob + Fafan + Erer, 4620 Gofa P + Dawro + Konta + Basketo, 7220 Kilbet P + Fanti + N.Wollo + Wag Hemra; 1v1 ties 4600 Gamo first-row + 5160 Sheka on Tepi zonal seat); Dirashe-special towns→Gardula per Dirashe Zone article; 6220 Om Hajer town row dropped (Eritrea Gash-Barka, airport row kept West Tigray); Itang town→Anywaa per OSM; Tepi→Sheka (zonal seat) over Photon Majang; Jemu→Bench Sheko per OSM over seat claim; B14 verify 2026-10-04 (gate_et.py): 1000 flipped Addis-primary (UPU+cheat), tree 127→124 (Amhara 13; 4 renames/3 drops), 50/81/22 sealed, 1230 + Dessie/Woldiya/Sekota + Harawo held |
| Finland | FI | 3576 | 3576 | B17 inline (gate_fi ALL PASS): 292/292 tree exact vs kunnat + 107/107 cities + legs == GN admin3 0/3576 + merger mappings confirmed live; one fix 00002 hattula→helsinki (GN-inconsistent row; Posti's own FI-00002 Helsinki). Original bundling: GeoNames dump joined on admin3 municipality codes (00002–99999; Swedish/Finnish bilingual names mapped; Pertunmaa→Mäntyharju, Valtimo→Nurmes, Honkajoki→Kankaanpää post-merger mapping) |
| Faroe Islands | FO | 118 | 119 | Posta code tables via da/fo wiki (FO-100–FO-970; 12 postsmoga excluded; FO-485 dual-linked); bare domestic input gains the FO- prefix at lookup |
| France | FR | 20316 | 20340 | GeoNames 51,611-row dump joined on department code, re-verified B13 vs Hexasmal + geo.api (4,624 qualifier rows `01014 9`/`75303 SP 07`/`13661 AIR`/`78078 CITYSSIMO` stripped to distinct bases — the build deleted literal ` CEDEX` but kept distributor tokens; all strips 1:1, GN row IDs preserved; 24 cross-department duals — 7 GN-artefact legs deleted on merger/exclave evidence: 02160 Gernicourt→Cormicy, 30130 Roquemaure fix, 49440/61420/62147/62760/73670 exclave-fraction rules; 1v1 ties by seat size incl. 3 B13 primary flips 01590→01 Champfromier, 69700→69M Montagny, 69780→69M Marennes; 13 Rhône→Métropole retargets on merged-commune names Oullins-Pierre-Bénite + Grigny-sur-Rhône incl. 7 CEDEX; 93380 filled = Saint-Denis post-Pierrefitte-merger; 2026-10-06 Hexasmal downward sweep via INSEE crosswalk: 6,051/6,051 metro codes bundled, 0 attribution deltas, zero fills; Roissy 95701 / Orly 94391 held single-signal; 69 split via EPCI 200046977 commune-by-commune, 69001–69009 Lyon-city single 69M; Clipperton 98799 dropped) |
| French Guiana | GF | 25 | 25 | La Poste Hexasmal (Sep 2026) |
| French Polynesia | PF | 83 | 93 | La Poste Hexasmal (Sep 2026); shared: 98732 Huahine, 98735 Uturoa, 98790 Rangiroa, 98796 Nuku-Hiva |
| Greenland | GL | 28 | 28 | Post Greenland + postcode lists (town→municipality mapping); B1 re-verification (GeoNames GL dump × wiki towns list + town articles): 3985 Nerlerit Inaat / Constable Pynt filled → Sermersooq (Mittarfeqarfiit official addressed usage + 3 mirrors; GeoNames misses live 3984/3985); 3970 Pituffik kept on enclosing Avannaata (unincorporated enclave); 5/5 municipalities covered |
| Croatia | HR | 1094 | 1094 | Hrvatska pošta live finder scrape (10000–53534; customs-only 10004 + 36 retired codes excluded); B20 verify-only: 556/556 L2 exact vs WP+citypopulation, 1094/1094 HP-covered, 1089 unanimous + 5 keeps (UPU office rule), 10004/31200 held out |
| Chile | CL | 346 | 346 | GeoNames dump 1:1 at province level (346 rows, zero cross-province codes, all 56 provinces; 2018 Ñuble split handled via 21-commune map to Diguillín/Itata/Punilla; Aisén→Aysén, Ranco→El Ranco, trailing-PROVINCE aliases) |
| China | CN | 2353 | 2353 | GeoNames 2,352-row dump, province-scoped prefecture join (same-name Fuzhou/Yulin/Suzhou/Taizhou/Yichun disambiguated by province; Tibetan/Uyghur romanization aliases Rikaze→Xigazê, Aba→Ngawa, Shannan→Lhoka, Diqing→Dêqên, Kaxgar→Kashgar, Kumul→Hami, Hetian→Hotan, Tacheng→Tarbaĝatay, Kezilesu→Kizilsu, Xilin Gol→Xilingol, Wulanchabu→Ulanqab; Lupanshui typo→Liupanshui); 12 GN admin1 misfiles corrected (Zhalantun/Arun/Morin Dawa→Hulunbuir, Ulanhot/Tuquan/Jalaid/Arxan→Hinggan, Ejin/Alxa banners→Alxa, Da Qaidam→Haixi on 817 prefix+place, Jingdong→Pu'er); 79 L1 links (56 municipality + Jiyuan + 4 Hubei direct + Shihezi/Wujiaqu + 15 Hainan direct + Nanhui); zero cross-prefecture codes; B19: 16 GN-admin2 retargets (YBK + NBS-divmap) + 2 swaps + 1 drop + 5 fills (zero-link prefectures 3→0); 452600 held |
| Cuba | CU | 772 | 777 | B15 inline (2 renames Vieja/Songo-hyphen, 18 corrupt drops, moves 10200-Centro + 99420-Yateras, duals 10600-Cerro(P) + 11400-Playa(P), fills 22600/53310/73200/97310/19120; gate_cu ALL PASS): Mapanet full crawl 144/186 + Nominatim ~70 (rural reliable, Habana noisy) + Archdiocese parish usage 100+ (confirms Mayabeque incl. 34xxx) + UPU + 6 Gitmo dirs; keeps: Artemisa 35/37/38 renumber over stale 32xxx (operator + UPU/youbianku), 3 office duals, HdE article; holds: Camaguey parallel sectors, 62410/77200/74680/34390/34140 single-signal, 12900-Castilla. Original bundling: Correos de Cuba office-search API full pull (841 offices; 6 zero-code HQ rows excluded; 16 office-municipio aliases incl. Buenaventura→Calixto García, La Maya→Songo-La Maya, Cuatro Caminos→Najasa; San Luis split Pinar/Santiago by province; 3 shared codes dual-linked, majority primary) |
| Czechia | CZ | 2694 | 2738 | GeoNames dump at district level (100 00–798 62; 2694 codes = current dump, zero diff; 15507 rows; 44 cross-district duals row-majority primary: 29 kept multi-row minorities + 15 RUIAN-refilled x1 secondaries 273 51 Praha-zapad + 289 14 Kolín + 294 13 Liberec + 321 00 Plzeň-jih + 334 52 Domažlice + 357 35 Karlovy Vary + 364 64 Sokolov + 380 01 Třebíč + 385 01 Klatovy + 507 13 Semily + 517 61 Ústí nad Orlicí + 539 44 Svitavy + 563 01 Svitavy + 675 26 Jihlava + 783 42 Prostějov; 384 01 Chlístovice row is a GeoNames error — Nebahovy's exact coordinates, cross-prefix, in-dump duplicate 285 22 — so Prachatice-only; ties 507 91 Jičín (Stará Paka office) + 544 43 Trutnov (Kuks office) + 569 94 Svitavy (Telecí) per OSM postcode boundaries; holds single 285 09 / 331 62 / 353 01-Sokolov; Prague codes 100 00–199 00 ×58 capital_city-only, zero cross-rows); spaceless input gains the official space at lookup |
| Colombia | CO | 3681 | 3681 | GeoNames dump 1:1 at municipality level (3,681 rows, zero cross-municipality codes; department-scoped exact/containment/fuzzy join + 11 overrides incl. Tumaco, Buga, Los Robles La Paz, Providencia Islands, Sincé, Tolú, El Carmen-Santander, Togüí-Boyacá; containment traps Tolú Viejo→Toluviejo + Palmas del Socorro→Palmas Socorro overridden; Mapiripana→Barrancominas by Nominatim centroid); B20: San Jacinto del Cauca area + 5 codes added, 81 Bogotá legs L1→20 localities (zones 1101-1120), 42 renames |
| Costa Rica | CR | 492 | 492 | Ministerio de Salud official 492-district DIVTER list (= postal codes per UPU province+canton+district structure; 10101–70605); GeoNames 473-row dump used only for canton-prefix map (Valverde Vega→Sarchí 2019 rename + León Cortés variant mapped); 17 post-GN districts (Jaris/Quitirrisí/La Amistad/San Lorenzo/Labrador/Canalete/Birrisito/La Victoria/Puente Salas/Cabeceras/Matambú/Caldera/Bahía Drake/Gutiérrez Brown/Lagunillas/La Colonia/Reventazón) + 5 new-canton codes (Río Cuarto 21601–21603, Monteverde 61201, Puerto Jiménez 61301) added; superseded 20306/60109/60702 excluded |
| Cyprus | CY | 1132 | 1135 | GeoNames postal dump at locality level (1000–9999; 1036 Nicosia quarters + 4528 Pentakomo-primary dual); B20 vs official CyPost directory xlsx: +8 codes (1000/3014/5000/6029/8203/8204/8652/8653), 5720 spurious dropped + L2 dedup, Fylousa swap fixed, 1025 Omorfita leg, 5 renames incl. U+03BF homoglyph |
| Guadeloupe | GP | 33 | 33 | La Poste Hexasmal (Sep 2026) |
| Guam | GU | 21 | 21 | USPS village ZIPs (Chalan Pago-Ordot has no asserted code); ZIP+4 strips to 5-digit base at lookup |
| Guatemala | GT | 549 | 549 | GeoNames dump at department level (01001–22220) × Correos operator PDFs (21/22 depts) + UPU scheme; B19: 01025 Zona 25 fill (directory + legal usage), 3 municipality renames, 548/548 L1 links re-verified (prefix + GN-admin1 + directory Petén 29/29) |
| Guinea-Bissau | GW | 52 | 62 | Mapanet full pull (150 locality rows with coords) + OSM sector reverse-geocode per row (1160–9300; Bissau 12 codes to autonomous sector; Bolama single 9300 triple Uno-primary + Bubaque + Caravela; 5000 triple Bafatá-primary + Galomaro + Gamamundo; 3200/3300/3600/6400/8300/8400 dual seat-primary; Bigene/Catió/Komo/Bolama sectors codeless in source) |
| Guinea | GN | 58 | 60 | Mapanet full pull (544 locality rows) filed per prefecture + OSM prefecture reverse-geocode per code (001–460; UPU radical structure: digit-1 = natural region, Conakry 001 + Kindia 100 + Labé 200 examples match; 430 dual Kissidougou-primary + Kérouané; 232 dual Mali-primary + Koubia for Matakaou village; Guékédou spelling variant mapped) |
| Guernsey | GG | 10 | 13 | GeoNames GY1–GY10 × Wikipedia GY postcode area (GN agrees 10/10; Herm GY1 3HR + Jethou GY1 4AB stay St Peter Port; GY6 Vale + St Andrew, GY7 St Pierre du Bois + St Saviour, GY8 Forest + Torteval dual-linked with alphabetical primaries, no population split) |
| Haiti | HT | 240 | 240 | UPU-listed 235 (Parcelforce Sep19 dept/arrondissement/commune table = Mapanet live scrape = postcode.info live index, set-identical) joined via UPU 42-district prefix list (digit-1 = department, digits-1–2 = arrondissement; 75 split 3rd-digit 752 Baradères / 751+753+754 Anse-à-Veau; 7520/7521 kept Baradères per UPU district table over the Parcelforce Anse-à-Veau grouping) + 5 GeoNames/Krezicart sub-locality codes the UPU list lacks (4530 Moreau Paye, 6145 Thomassin, 6146 Fermathe, 6147 Pergnier, 8313 La Colline); HT3408 excluded — prefix 34 has no UPU district, GeoNames-only street-address row; bare domestic input gains the HT prefix at lookup |
| Greece | GR | 974 | 984 | ELTA street register (72k rows, 497 localities) joined via GeoNames dimos codes (10442–85800; 10 cross-municipality codes dual-linked, street-majority primary; 2019 split dimos mapped to current municipalities; Athos codes to Mount Athos region; bundled Agioi Deka corrected to Gortyna); spaced input strips to bundled spaceless form at lookup; B18: ELTA-exact, 14121/14122 → Irakleio (Attica) + 49083 → Central Corfu |
| Germany | DE | 10812 | 10911 | GeoNames dump at district level (01067–99998; 19k street rows, state-scoped place join + local aliases incl. Calvörde→Calvoerde, Helgoland→Heligoland, Brühl→Bruehl, Hürth→Huerth, Straubing-Bogen surplus + 26 dispatched pairs; 98 cross-district codes dual-linked, within-state row-majority primary incl. 88348 matched via Bad Waldsee row; 99986 Recklinghausen comment-row tie + 12529 Dahme-Spreewald dual confirmed via places-in-germany.com; 13 junk codes excluded incl. Kreuzburg 838→1038, merged-Bissendorf 3000→21220, Weiden West 92640, Wilhelmshaven EC 46457/50735/51200/82396/83308/95326, Trier WC 52348, Hamburg-Pinneberg 22113/15834/20331). B19 (2026-10-06): set == fresh GN exactly; 20 same-name-town retargets + 4 swaps (07919/12529/21465/92637) + 22113 Stormarn secondary (Hamburg primary); 98711 S5 held; 10/10 anchors + 10/10 OSM spots |
| Georgia | GE | 74 | 83 | Gpost.ge finder re-verified Oct-2026 (~370 queries, ~1,400 cards: every district filing carries exactly its bundled code; homonym villages vote their own district's code — Vani ×5, Bazaleti, Akhalsopeli ×3, Abastumani; Tbilisi 8 village/settlement-attached codes → L1 city — 0167 Mukhiani-2 street block, 0190 spans Isani+Samgori so no district refinement; 7300 Tskhinvali-region 5 links Akhalgori P dual-filed ცხინვალი + district + Java + Eredvi + Kurta + Tighva, Tskhinvali town unlisted; 6600 Sokhumi P + 5 Abkhaz districts incl. Gulripshi via Estonka/Dranda/Merkheuli; Azhara Kodori villages carry 6600 but file under Gulripshi with no second signal — held linkless; Kutaisi 4600/4602 + Tbilisi/Batumi/Poti street space out of scope, 4608/6004/6010 kept correct-but-partial; civil-registry office list agrees ~45/45 live rows — Khobi 5300 row is a transcription error vs 10× 5800 usage, Akhalgori 0600 stale pre-2008 vs live 7300 town + 6 villages; zipcode.com.ng/ntdtvjp village sub-codes rejected — live gpost returns XX00 for every sampled village) |
| Honduras | HN | 84 | 85 | B17 inline (2 changes: Cantarranas rename incl. slug + Taulabé accent, zero leg moves; gate_hn ALL PASS): 37/37 GN codes bundled with 37/37 dept agreement; 47 Mapanet-only kept (18 prefix blocks zero splits); 5 missing XX000 bases unsupported, correctly absent. Original bundling: Mapanet full pull (12,791 rows, 6,416 localities, 68 5-digit codes, all 18 departments) + 16 GeoNames-only codes (38-row GN dump: dept capitals Mapanet lacks — Santa Rosa de Copán 41101, Juticalpa 16101, Yoro 23101, La Paz 15101, Pespire 51201, Siguatepeque 12111 + SPS sectors 21101–21104 + 9 more); 21/21 overlap codes agree on department; 12101 dual-linked Comayagua-p (143 rows) + Francisco Morazán-s (45 rows, Comayagüela twin — GN lists both cities); Tocoa 32301 (GN) + Sonaguera-area 32351 (Mapanet) coexist, no conflict; UPU sheet stale (2004 2-letter system); L1 department links |
| Hungary | HU | 3048 | 3066 | B16 worker (2 leg drops 2943/Kisbér + 9764/Sárvár→Szombathely-primary, 3 fills 3244/3603/9719; 20→18 multis; gate_hu ALL PASS): GN postal + WD P281/P131 + hu.wiki + OSM adjudication; 18 surviving multis leg-confirmed; 2242/3071 held out. Original bundling: Magyar Posta + KSH gazetteer joined dataset IrszHnk (Feb 2026; 3569 settlement rows; 1007–9985); districts via KSH seat names + Budapest kerület map (Hegyháti→Hegyhát the only non-seat name); 20 cross-district codes dual-linked, population-majority primary (7814 Siklósi 356 vs Pécsi 352 + 9375 Soproni 258 vs Kapuvári 241 closest) |
| India | IN | 19238 | 19488 | GeoNames IN dump (postal rows × district names + 90-entry ALIAS table for renames: Prayagraj/Ayodhya/Gurugram/Narmadapuram/AhilyaNagar/Dharashiv etc. + PINOVER state-misfile fixes); non-split PINs direct (cp1); 4,609 split-district PINs verified by Nominatim centroid revgeo (polite 2.2s + retry; Palnadu/Konaseema/new-district aware; 5 DNS errs retried); district-suffix strip (Mumbai City/Suburban, Udhampur, Krishna); 33 Kanchipuram-split TN PINs by taluk-position (Kancheepuram vs Chengalpattu); cross-state border PINs adjudicated (521178/521402→Khammam TS, 515281/515286/515766→Sri Sathya Sai, 312614→Chittorgarh, 532291→Srikakulam, 797112→Chümoukedima, 673310→Mahe, Puducherry enclaves→Puducherry district); Delhi South/West-Midnapore/Rangareddy splits via alias-None; 216 dual-links (secondary needs ≥2 votes + ≥25%), (1,1) ties → Nominatim side. B20 (2026-10-06): tree ZERO changes, Ladakh-5 held out per LGD policy; postal L1 sweep (~1500 pins, India Post mirrors, 2-vote rule): 195 pins re-legged (AP/NTR + TN restructure, Bengaluru/Godavari/Nagaon/Sambhal batches, Delhi 412→111/113) + 83 missing pins added (Karimnagar block, Kanchipuram run, singles); Delhi validation + stale-leg-pattern + KA watch holds |
| Iceland | IS | 174 | 178 | Pósturinn register via is.wiki (101–900; 20 PO-box/special omitted; 512 omitted, see rules; 4 neighbour-served communes dual-linked) |
| Indonesia | ID | 9359 | 9359 | Mapanet full pull (9,110 rows, 5-digit, 33 r1 provinces) × GeoNames PPL+ADM3/ADM4 join on BPS admin2 (4041 exact≤60km + 505 fuzzy≤30km + 4177 3-digit-block + 29 cross-province + 257 district-manual incl. 343xx E.Lampung under r1=11, Limo→Depok, Bulukerto→Wonogiri, 1651x/57698 misfiles); ID codes never span regencies so 463 multis resolved by 15km coord-cluster majority (minority rows = Mapanet row errors: Ciwaringin-Cirebon under 16114 etc.), 178 ties by ≤10km-strong block plurality + 25 postal manuals; city-prefix post-passes (401/402→Bandung-city, 161→Bogor-city, 151→Tangerang-city, 154→Tangsel, 405→Cimahi, 164→Depok, 173/175→Bekasi-regency, 6611-3→Blitar-city, 382→Bengkulu-city); GN-admin2 systematics fixed (Tapaktuan→A.Selatan, Johan Pahlawan→A.Barat, Selebar/Gading Cempaka→Bengkulu-city, Mapanget→Manado, Sanan Wetan→Blitar-city, Ciputat→Tangsel); old→new BPS remap for Papua splits + Sorong/Maybrat/Raja Ampat/Deiyai/Paniai/Tolikara per-row splits; name-linked areas-code bugs (Lutim 7324, Dumai 1472, Kutim 6408); 4 Timor-Leste rows dropped (Atabae/Maliana/Passabe/Tilomar), 1 'Lainnya di' junk row dropped; Oct-2026 re-verification: GeoNames admin2 proved systematically rotated (Jakarta 5-municipality cycle, Sumut/Sumbar/Aceh/NTT/Sultra/Sulsel/Lampung/Kalimantan/Maluku/Papua block shifts) so all 5,513 codes re-verdict by GN place-name vote through the village tree + coords + postal refs: 1,912 links corrected, coverage 369→389 L2; known source gaps (no rows to verdict): Surabaya 60xxx, Semarang 50xxx, Medan-core 201xx, Makassar-core 901xx, Malang-core 651xx, gap-fill Oct-2026: Pos Indonesia postcode book (prangko.nl: 7 regional + 8 city volumes, incl Jakarta/Surabaya/Medan/Bandung/Denpasar/Palembang/Manado/Yogyakarta) x GeoNames places/coords: 3,613 codes added (1,350 both sources, 189 book-only, 2,074 GN-only), same place-vote adjudication + unconstrained safety net; 25 Timor-Leste rows + 11 ambiguous single-row code typos dropped; Ambon/Semarang volumes absent from the mirror so those towns closed via worldpostalcode town pages instead (Ambon verified complete incl 97231-37 with 97120 correctly absent; +18 Semarang-city codes with kelurahan assignments); 9,144 codes, 514/514 L2; town-page sweep Oct-2026: 429 worldpostalcode town pages (14,431 place rows) fully diffed, +241 codes by place-vote verdict, 7 dropped (1 typo, 2 page errors, 4 Timor-Leste), 7 Batusangkar links fixed 50 Kota→Tanah Datar; contra sweep: all 8,848 shared codes re-voted, 99 links moved (inherited GN-admin2 errors), 24 typo-dupe/stale codes dropped, 15 keeps; 9,361 codes; open-log #1 retry Oct-2026 (official Pos directory oracle; GN-family = old vintage): 6 relinks (99674→Boven Digoel, 20524/25→Medan, 92661→Sinjai, 98865→Dogiyai, 84111→Kota Bima) + 2 drops (52191 page error, 34663 zero-source); 9,359 codes; only unassigned 18xxx/47xxx-49xxx/88xxx absent |
| Ireland | IE | 139 | 141 | Eircode routing keys: GeoNames dump (139 keys with towns) cross-checked to Wikipedia routing-area table (136 keys; A94 = Blackrock Dublin confirmed via property records; A82/A92 dual Meath+Cavan / Louth+Meath, first-listed primary; K67/T12/T23 GN-only); full 7-char eircodes strip to 3-char routing key at lookup |
| Italy | IT | 4781 | 4791 | GeoNames 18,415-row dump joined on province sigla (105/109 L2 direct; AO 136 rows/20 codes → L1 Valle d'Aosta, region has no provinces); Sardinia 629 rows/160 CAPs point-remapped to 8 post-2025 provinces via 478-point Nominatim reverse-geocode (477 hit, all 8 provinces; ~60 Cagliari-centroid junk-coord rows excluded from vote); 11 junk-only single-comune CAPs Photon-verified with matching CAP echo (Isili/Nurri/Escalaplano/Monastir/Nuraminis/San Sperate/Villasor/Dolianova/Muravera/Villasimius→Cagliari metro, Teulada→Sulcis Iglesiente); 10 cross-province codes dual/triple-linked (08020 Nuoro-primary + Gallura secondary — Sassari leg dropped, ISTAT zero Sassari-metro comuni; 08030 Cagliari-primary over Nuoro — Oristano dropped, 10 Sarcidano vs 7; 09020 Medio-Campidano-primary + new Cagliari secondary for the Ussana/Pimentel/Samatzai trio; 07030/08010/09010/09030/09040 row majorities; 12071 Cuneo + 18025 Imperia prefix-series tiebreaks, Photon-confirmed both towns genuinely share: Briga Alta-CN/Mendatica-IM, Bagnasco-CN/Massimino-SV). B14: +48 fills (07051/52 Gallura, 09050–09069 Cagliari-metro run, 09064 Ogliastra, 09065 Nuoro, 09089 Oristano, Cesena 47521/22, Ravenna 48121–48125, Verbania 28921/23–25, new-comune codes 10079/29031/33014/36044/36048/52019/61036/62031, singles 04031/15122/41123/71051/82014), −2 drops (32047 Sappada→33012 Udine, 47023 Cesena retired); +3 sigla (OT/VS/OG), Sulcis HOLD; 09132/33 + La Spezia 19127–30 HOLD |
| Isle of Man | IM | 9 | 27 | GeoNames IM1–IM9 × Wikipedia IM postcode area (IM86/87/99 PO-box/large-user excluded); 51 GN localities parish-mapped via Nominatim forward-geocode (IM4 7 parishes Braddan-primary, IM5 Patrick-primary, IM6 Michael-primary + German on Cronk-y-Voddy, IM7 6 parishes Garff-primary, IM9 Arbory and Rushen-primary); 4 conflicted/no-hit singles excluded (Garth + Ballacannell truly IM9, Hillberry truly IM2, Stuggadhoo/Shoughlaige unknown; IM7 Ballasalla noise) |
| Jersey | JE | 2 | 12 | Wikipedia JE postcode area (JE1 large-users + JE4 PO-boxes + JE5 bespoke-delivery excluded as non-geographic per Royal Mail; JE2 → St Helier-primary + St Clement + St Saviour, JE3 → 9 parishes Grouville-primary alphabetical); outward codes cannot reach vingtaine level, verdict at L1 parish |
| Iraq | IQ | 348 | 348 | Mapanet full pull re-verified (348 1:1 town↔code rows, 5-digit, code set exact) → L1 governorate by r1 filing + M5-adjudicated overrides: keeps Abu Ghraib 31020/Salman Pak 34004→Baghdad (Baghdad-district towns), Aqre/Sheekhan/Kalak→Nineveh (disputed-territory ties), Mishtiqa→Nineveh (unlocatable, unresolved tie), Mekhmour/Debca/Quwair→Nineveh (Makhmur district: Nominatim + Makhmur article), Alton Copri→Kirkuk (Dibis, echo 44022), Kwaisanjaq/Koya→Erbil (Koya district), Kifri→Diyala (echo 32004) + Diyala-filed Kefri duplicate, Al Suwaira 58012→Wasit (Suwayra-district pin); in-block corrections Latifiya 10080→Baghdad (Mahmudiya subdistrict, echo 10080), Qal'at Diza 46016→Sulaymaniyah (Pshdar, echo 46016), Sharazor/Penjaween/Said Sadiq→Sulaymaniyah (declined Halabja), Helabcha 46006→Halabja (Halabja town, echo 46006), Shafiiyya 58014→Qadisiyyah (Diwaniya pin+fwd); Halabja holds 46006+46018; UPU 61102 PO-box-only out of scope |
| Iran | IR | 109 | 111 | Mapanet full pull re-verified 2026-10-03 (364 rows, 109 5-digit codes, 30 r1 provinces — code-set identical, zero drift) → L1 province by r1 (Nahavand-row coords prove Qazvin not Hamadan, Sari-row coords prove Tehran Pakdasht, Kerman via Arzuiyeh, Kordestan split needs none); Bojnurd rows under the Razavi r1 → North Khorasan, Ferdows/Sarayan rows under the North r1 → South Khorasan; M7 fix: 96914 → Razavi Khorasan (5 unanimous Gonabad rows, Photon 5/5 Razavi/Gonabad, wiki county membership, addressed "Gonabad 96914" usage) + 97716 Razavi secondary (Gazi/Jazin rows in Jazin RD, Bajestan; Ferdows 3v2 keeps the South primary); dual 45617 Qazvin P (10 r1=21 rows + Magan/Mahin Tarom-e Sofla) + Zanjan s (5 Abhar-area rows); Alborz codeless (no Mapanet r1); bundled codes are 5-digit prefixes of the live 10-digit system (UPU IRN profile; post.ir unreachable) |
| Kenya | KE | 977 | 977 | PCK office directory (951 codes; office towns × GeoNames admin1, 877 exact joins, rest via block/neighbour evidence; 00127 Ruaraka + 08010 typos fixed against PCK codes 00618/80100); B18: PCK 4-list agreement → 28 fills + 26 Nairobi-fallback moves (977/977) |
| Kazakhstan | KZ | 2525 | 2525 | Mapanet full pull (2,980 6-digit rows, 16 r1 regions; Kazpost PP prefix system) × GeoNames PPL admin1 join (current regions incl. Abai/Jetisu/Ulytau) for the 4 split r1s (826 rows: 748 name + 78 PPDD-block); 47 questionable towns adjudicated by Nominatim fwd with postcode echoes (Usharal 040200 Jetisu, Kokzhide 040313 Almaty, Ekpendi 040213 Jetisu, Marinogorka 071005 Abai, Martobe→Shymkent enclave kept); 50 GN-stale overrides to block (Dmitrievka/Belokamenka/Bobrovka/Kainar/Kamyshenka/Appaz/Terekty/Yntaly/Kiik/Kokozek/Kopa area mismatches); Samar district genuinely splits block 0710 (Kokpekti-Abai + Samar-E.Kazakhstan); 452 70/71/72/79-series rows dropped as Kazpost internal directory artefacts (departments, Dekretniki/Uvolennye/PPP, sequential per-branch numbering; Komintern real code is 030406); 121800–121812 corrected to 0218xx per official Tselinograd doc + 131309→151309 (no collisions); Ulytau thin (3 codes; Zhezkazgan/Satpayev/Zhanaarka absent from source), Shymkent single (Martobe). M6 revisit: 9 Almaty-region districts reparented city→region (Balkhash/Enbekshikazakh/Ile/Karasay/Kegen/Raiymbek/Talgar/Uygur/Zhambyl) + 070209 Tarbagatai E.Kazakhstan→Abai (Ayagoz-block singleton; WPC Ayagoz page + GN PPL admin1 Abai); Marinogorka wording fixed; full WPC re-scrape (4,172 codes, 185 towns) confirms shipped set (only-shipped = the 14 corrections; 589 artefact-series + ~1,070 single-signal gap codes incl. Shymkent 1600xx / Zhezkazgan 10060x documented, not filled); `gate_kz.py` ALL PASS |
| Japan | JP | 120720 | 120801 | Japan Post ken_all official gazette (124,837 rows Shift_JIS; JIS municipality join incl. designated-city ward roll-up 1413x→Kawasaki 14130, 1415x→Sagamihara 14150, 2714x→Sakai 27140, 2213x→Hamamatsu 22130, 4013x→Fukuoka 40130; 35 abolished rows + jigyosyo firm codes excluded); 69 cross-municipal codes dual-linked alphabetical primaries; 1,741/1,747 munis (6 Northern Territories villages codeless, Russian-administered); stored dashed NNN-NNNN official form. B21 (2026-10-06): 77 Tenryu-ku legs 22100→22130 + 432-0000 phantom drop + 38 KEN_ALL adds (32 Gosen 959-10xx hole + 6 scattered, terminal-authority exception); tree zero, 235/235 sample |
| Jordan | JO | 351 | 352 | Mapanet 354 rows → GeoNames fuzzy (GeoNames JO PPLs carry no admin2) → Photon reverse-geocode qada (328/354; Nominatim only 92/321, GeoNames admin2 empty) × DoS 2015 census liwa↔qada table + ar.wiki liwa pages (decisive: Udhruh→Ma'an Qasabah not Petra, Orjan→Ajloun Qasabah, Rajm Shami→Mowaqqar, Umm Rasas→Jizah, Hisban/Umm Basatin→Naour, Mujib→Qasr, Irhab→Mafraq Qasabah, Hawsha→Badiah Gharbiyah, Umm Jamal/Deir Kahf/Salhiyah→Badiah Shamaliyah, Hawwara→Irbid Qasabah, Waqqas→Aghwar Shamaliyah despite OSM Janoobiyah label); 85/88 OSM cross-check agree (3 border adjudications: 71221 Ajloun, 71228 Jerash via Burmah, 11152 Quaismeh via Wehdat camp); overrides: 71910 Shobak (719xx), 61258 Sahab (r1=16 Industrial City), 64710 Hasa (coords+revgeo beat r1 misfile), 25710 Mafraq Qasabah (DoS Bal'ama; r1+geoname garbage); 11121 dual Wadi Essier+Jami'ah, 11190 Amman Qasabah (Al Abdali 0.947 beats Qaser Al Adel 0.522), 11134 Marka agreed; JO codes do not block by governorate (61256 Quaismeh) |
| Kyrgyzstan | KG | 919 | 919 | Mapanet full pull re-verified 2026-10-03 (920 rows / 919 codes; 51 district/city leaves incl. new Toguz-Toro 721500-721506 + Toktogul 721600-721621; 720000–725032; page→link sweep 882/882 agree) + 11 retargets (720900–720910 Suzak→region: Jalal-Abad City leaf + city archive + Kyrgyz Post hub filing; Kachkynchy city-admin) + Aitmatov rename (ex Kara-Buura, Law KR 2023-04-10 No. 82); seat anchors: Mapanet + state-archives + RU-wiki + OSM (Tokmok 724200/724915 over stale RU-wiki 722000 live at Kyzylsuu; Naryn 722900 over stale 722600 live at At-Bashy; Kara-Köl 721000-721003 at region; Kachkynchy 720910; Kazaman 721500; Daroot-Korgon 723700; archive.kg 715xxx/Kant-720900/Belovodskoe-722040/Ala-Buka-720610/Toktogul-town-721000 rows stale/erroneous); Jalal-Abad city renamed Manas 2025-09 (region unchanged); gaps: Talas city code unknown (724200 stays Tokmok), 722620 At-Bashy two-signal only, 721619 Ustasai Mapanet-only weak |
| Kiribati | KI | 25 | 25 | MICTTD official 37-code table via archive (KI0101–KI0303; 12 uninhabited-island codes excluded); bare domestic input gains the KI prefix at lookup |
| Kosovo | XK | 127 | 127 | Posta e Kosovës regional lists via archive.org (10000–73000; 10020 transit centre excluded; post-split offices mapped to current municipalities; 40700 Runikë mapped to Skenderaj by office location though filed under Mitrovica; Parteš/Ranilug/North Mitrovica have no office code) |
| Laos | LA | 26 | 148 | EPL official postcode API (7,781 village rows, 2025-01-20: district office blocks PP0D0, Xaisomboun 18000-block, Pakse district 16000 with 16010 = Champasack-district office) + Mapanet district pages (VTE 9/9 exact, Namtha 03000, CH all-16010 structurally contradicted) + addressed hotel usage (Pakse 16000 live, 16010 stale) + LSB/census tree oracles; province zones shared by all districts, capital-district primary (Sekong La Mam, Attapeu Samakkhixay, Xaisomboun Anouvong; Champasak 16010 -> 16000 B15; district office blocks + 11060/11070 zones + Km-52/special entries noted but unmapped; VTE EPL blocks overlap districts so VTE keeps verified subs) |
| Latvia | LV | 697 | 731 | GeoNames dump at novads level (LV-1001–LV-5752; city-edge splits dual-linked); bare domestic input gains the LV- prefix at lookup; B20: builder city/muni collapse fixed (96 moves + 12 duals + 9 flips + 5015 swap; 6 zero-leg areas filled), Varaklani 4835-38 Rezekne→Madona per 2025 merger law |
| Lebanon | LB | 688 | 701 | Mapanet 2741-row pull → base 4-digit (Hazmiyeh keeps published 1107-2090 → Baabda; 3 spaced 1107 20xx Beirut sectors strip to base at lookup) × Photon reverse-geocode caza per point × 56ok directory cards (603 codes) × wiki place oracle × GeoNames gazetteer; Beirut 12 codes → L1 (no caza), Akkar single-caza gov (Khirbet Er Remmane Syria-point corrected); 16 zero rows adjudicated as built (Aabrine→Batroun, Btaaline→Baabda, Jeblayeh/Mazraat Ed Dahr→Chouf, Btater→Aley, Nahr Ibrahim→Byblos, Chira dual Bsharri P + Koura s per wiki border village, Jarjour→Miniyeh-Danniyeh, Zeita→Sidon, Brital/Haouch Ed Dahab/Hay El Mathaneh/Jdaidet El Fekeheh/Maqneh/Ouadi El Assouad/Ras Baalbek→Baalbek); ties/multis stand (3730/5277/5420/6662 singles, 3868/4841/5722/6292/6776/7352 duals, 4143/5203/8226 multis; 4143/5203 primaries kept per stability); revisit 2026-10-03: +5 Hermel fill (8123/8128/8151/8173/8242, 4-signal unanimous), 18 retargets (3018/3514→MD, 4215→Batroun, 4362/4384→Byblos, 5649→Chouf, 6642/6710/7150→Jezzine, 6851/6875/6893→Tyre, 7121/7192→Nabatieh, 1835→Zahle, 1855→Rashaya, 3769/3911→Bsharri), +2 secondaries (5428 Chouf, 8119 Hermel); Tripoli caza codeless in all sources (LibanPost fill needed) |
| Liberia | LR | 31 | 31 | philib post-office list (39 offices → 31 base 4-digit codes 1000–7520, all county-pure; cites MOPT official list, now 404; UPU lbrEn.pdf anchors 1000 Monrovia + 4000 Buchanan, overrules osint repo's stale 3100 Buchanan); 8 Monrovia 1000-xx delivery units strip to base 1000 at lookup; offices serve whole counties so links are L1 county (codes do not resolve to the 157 districts); Gbarpolu codeless in source (Bopolu code unknown); MapAnet LR crawl empty (structure but zero records), GeoNames has no LR export; B14 tree reschemed to the 2022 census (gate_lr.py) without touching the overlay |
| Liechtenstein | LI | 13 | 13 | Swiss Post PLZ list (9489 omitted, see rules) |
| Lithuania | LT | 2023 | 2068 | GeoNames dump at municipality level (00001–99069; 3 cross-county stray links dropped; same-county splits dual-linked) |
| Luxembourg | LU | 4333 | 4434 | B13 re-verification: CACLR registry × GeoNames × 2018 Post file (L-1110–L-9999; 13 stale GeoNames commune labels folded into 8 merged communes with zero misses; 93 cross-commune codes = 72 inherited duals verified + 20 new duals + L-1110 airport dual, 3 grown to 3–4 legs; 15 dead codes dropped — 10 GN-only phantoms, 4 retired streets, 4008; 65 live B-type CEDEX + 26 streetless N-type + dormant L-7300 deliberately held out (2026-10-06 retry: L-7533 Fischbach secondary dropped on BD-Adresses 18/18 Mersch + Nominatim; 1634/2632 secondaries confirmed); bare domestic input gains the L- prefix at lookup |
| Madagascar | MG | 110 | 114 | 114 districts modelled (INSTAT/Wikipedia list; French↔Malagasy name variants mapped; Maroantsetra→Ambatosoa post-2021 regions); mapanet district pages (103) + youbianku Urban codes (101/110/201/301/401/501/601) + wiki-infobox new-split codes (Mandoto/Isandra/Lalangina/Vohibato share 113/314/303/305, dual-linked established-primary); B14 verify 2026-10-04 (gate_mg.py): zero-diff tree, 74 WP-corroborated singles, 4 multis + 102/103 split + 101/501 anchors sealed, 302 absent; 2026-10-06 retry: 29/32 sealed by WP/OSM/addressed, 3 held (111/323/606), French-list directories single-lineage |
| Malawi | MW | 491 | 491 | Government Gazette 2019-05-10 General Notice 37 "Malawi Postcodes 2019" pp.169-177 (primary: 491/491 GeoNames rows reconciled — 480 clean + 10 OCR-damaged + Lulanga typeset-dupe row adjudicated 301100 by the 100-pointer scheme rule + nowmsg/ipostalcode mirrors); MACRA post-codes page via Wayback 2026-05-17 (199-code subset, all match; 205112 Mavwere web-table dupe loses to gazette + GN 205113); UPU MWI 01/2021 (6-digit format + exact 204101/312200; 102010/309070/309010 examples scheme-inconsistent illustrations) + UPU Aug-2022 type table (999999 N); pcng district pages 30/30 + prostobank/ipostalcode spots; WP pins Luchenza->Thyolo + Mzuzu->Mzimba; Lumbadzi 204108 single-primary Dowa (gazette block, no second signal), Ngabu twins coexist (315110/315111 Chikwawa + 316106 Nsanje); zero secondaries, all 28 districts covered (Lilongwe 76, Blantyre 50, Kasungu 39); UPU 2002 profile superseded |
| Malaysia | MY | 3048 | 3924 | Pos Malaysia (existing dataset; Oct-2026 retry: 96510→Pakan primary + 96100 Pakan drop, Tandek 89050→89100, Beluru 98050 dual, Lachau + Sungai Tenggang rows+95000; Oct-2026: added 28 concretely proven gap codes (addressed usage or clean courier listing + Pos range check) on postal-town primaries incl. 94111 Tanjung Datu; purged 4 packaged phantoms (14700, 42425, 42900, 42907) — each in a fully dead API neighborhood (Pulau Indah is 42920, Telok Panglima Garang is 42500/42507/42509); 21 more candidates excluded as unproven (live range, no exact evidence — reinstate with concrete proof only); 10 claimed codes rejected as "Post Code Not Exist" — 22564, 27800, 29115, 29452, 29466, 94100, 48500, 56300, 64999, 74300; 21040 gains Marang secondaries Jerung + Bukit Payung for Kampung Temiang / Jerong Seberang / Jerong Tuan; 42920 sharpened onto a new Bandar Pulau Indah town row (ex-Pulau Lumut) with Mukim Klang secondary; Pingan Pingan 89130/89137/89138/89139 office set reinstated (coherent 4-type pages + Pos-live cell). Proof standard: getStateByPostcode validates live range/cell, not exact existence — 200s need independent support, only 400s are decisive) |
| Malta | MT | 27823 | 27823 | MaltaPost postcode finder API exhaustive sweep (89 towns incl. 3 CBD + Comino; street codes ATM 2000–ZBK 5100; sub-localities to parent councils: Kappara San Ġwann, Gwardamanġa Pietà, Baħrija Rabat; Victoria/Rabat disambiguated); compact input gains the official space at lookup |
| Maldives | MV | 199 | 202 | Postcodebase + 56ok cross-validated island lists (00010–23000; resort/uninhabited codes + Malé/Villingili street ranges excluded; 05020 dual-linked) |
| Marshall Islands | MH | 2 | 2 | USPS (96960 Majuro, 96970 Ebeye; outer atolls route via hubs); ZIP+4 strips to 5-digit base at lookup |
| Mauritius | MU | 1990 | 1990 | Mauritius Post finder exhaustive scrape (11101–91710 + 182 Rodrigues R-codes at dependency + 4 Agalega A-codes); B14 verify 2026-10-04 (gate_mu.py): zero-diff tree, UPU anchors + clean blocks + 58 whole-village cross-block sealed; 2026-10-06 retry: live-MP POST harvest replicates 1,990/1,990 exactly, MDPA 2024 street snapshot 1,987/1,990 (6 stale/typo rows adjudicated to bundle), link-level audit still future work |
| Martinique | MQ | 30 | 35 | La Poste Hexasmal (Sep 2026); shared: 97218 Basse-Pointe, 97222 Bellefontaine, 97250 Saint-Pierre |
| Mayotte | YT | 11 | 18 | La Poste Hexasmal (Sep 2026); shared primaries: Mamoudzou, Dzaoudzi, Chirongui, Mtsamboro, Bandraboua, Dembeni, Ouangani |
| Mexico | MX | 32448 | 32448 | GeoNames 144,655-row dump joined on INEGI state+municipality code (01000–99998; zero miss, zero cross-municipality codes; 2,457/2,479 munis — 22 codeless are post-dump creations: Villa de Pozos, Puerto Morelos, San Quintín, Seybaplaya, Dzitbalché, Eldorado, Juan José Ríos + 15 Chiapas/Morelos splits). B21 (2026-10-06): PF1 77580/77586 → Puerto Morelos; 317 adds + 473 bundle-only held (single-lineage, Correos-live blocked); 384/384 sample, 1285 admin correctly absent |
| Micronesia | FM | 4 | 4 | USPS via FSM government (96941 Pohnpei, 96942 Chuuk, 96943 Yap, 96944 Kosrae); ZIP+4 strips to 5-digit base at lookup |
| Moldova | MD | 1215 | 1220 | GeoNames dump at district/city level (MD-2000–MD-7843; shared-code primary = majority-village district); bare domestic input gains the MD- prefix at lookup; B20: legs copied GN admin1 1:1 → 15 moves (Basarabeasca 67xx, Bender zone, Roghi) + MD-5222 del + MD-5219 add (conflict resolved, omission superseded) |
| Montenegro | ME | 149 | 150 | Pošta CG branch network via API (81000–85530; 80000 service code + retired 81122 excluded; 85333→Tivat). B6 revisit: 85333 gains a Kotor secondary (Dobrota 2 branch + Jun-2023 Pošta notice; Tivat primary kept); retired 81125/81128 (branch pages to 2023/2025) + Ljubotinj branch excluded; stale commercial-data 85354/85357 + single-tag 81201 held out; 84216 Pljevlja + 85317 Kotor kept over pin artefacts; `gate_me.py` ALL PASS |
| Monaco | MC | 1 | 0 | UPU MCO profile (98000 delivery; 01–99 are delivery-type) |
| Mongolia | MN | 39 | 39 | Mongol Post branch directory (21 aimag posts + UB horoo branches; EasyBox lockers carry no codes; 3 codeless service branches omitted; per-sum codes not published; B18: 4 sum renames, postal untouched 39/39) |
| Morocco | MA | 2083 | 2088 | GeoNames 1,325-code base + Mapanet top-up as before; M4 annuaire audit: 3 primaries moved to the Poste Maroc filing (35224 Oulad Ayyad Taza→Taounate, 80100/80650 Agadir→Inezgane quartiers), 6 xx119 Casablanca phantoms dropped (no annuaire/GN/PCB trace, bare no-suburb rows, suffix 119 unattested); keeps: 29004 Médiouna, 86603 Inezgane, 90052 Tanger-Assilah, 16175 Sidi Kacem, 35113 Guercif, 12050/12100 Skhirate-Témara (annuaire header typo); 62/75 covered (13 new/small codeless — Poste Maroc has no sections for them) |
| Montserrat | MS | 8 | 8 | Government of Montserrat postcode pamphlet; bare domestic input gains the MSR prefix at lookup |
| Nauru | NR | 1 | 0 | UPU single-code list (NRU68) |
| Netherlands | NL | 4071 | 4092 | 4-digit prefixes (IE routing-key precedent; full 6-char needs licensed PostNL file): GeoNames dump joined on CBS gemeentecode + Voorne aan Zee 2023-merger fix (20 codeless rows), every prefix verified live in PDOK/BAG LocatieServer (15 phantoms dropped: 1099/1234/1940/1954/3160/4200/4857/5100/5217/5442/6060/6817/7146/7379/7940, zero in adres + postcode indexes); 55 GeoNames multis adjudicated by BAG address counts (19 pop-pick flips incl. 2324 Leiden, 3981 Bunnik, 8611 Gaastmeer, 1431 Aalsmeer, 2761 Zevenhuizen, 4043 Opheusden, 1906 Limmen, 1755 Petten, 1616 Hoogkarspel, 3633 Vreeland, 5091 Middelbeers, 8465/8466 De Fryske Marren, 9354 Zevenhuizen-Gr, 7233 Vierakker, 6574 Berg en Dal, 3651 Nieuwkoop, 3366 Molenlanden, 1695 Hoorn; 9617/9623 Midden-Groningen by gemeentecode after token-match tie; 38 zero-address sides dropped, 20 genuine cross-municipality duals kept incl. 9999 Het Hogeland single after 4 big-city postbus-artefact drops); full NNNN LL strips to 4-digit prefix at lookup; B18: BAG census → 1216 Wijdemeren secondary dropped, 6153 Beekdaelen + 6881 Rozendaal secondaries added |
| New Caledonia | NC | 50 | 50 | La Poste Hexasmal (Sep 2026) |
| New Zealand | NZ | 1769 | 1769 | GeoNames postal dump at locality level (0110–9893; 1081 Ostend/Surfdale dual-linked, Ostend primary; 58 places resolved via coords + council/NZ Post maps, Waioruarangi to Kaikōura on 7300 delivery). B21 (2026-10-06): 32 onzp-street adds + 23 homonym-side retargets (Nukuhau phantom + Waipawa→Tora); 4180 leg-less held; 1081 dual kept |
| Namibia | NA | 149 | 149 | NamPost official postcode table (149 offices, 5-digit 2018+ system 10000–23017, all 14 regions incl. ǁKaras + Kavango East/West; MapAnet NA rows carry stale pre-2018 ZA-era codes — Windhoek 11002 vs official 10005 — excluded); offices are delivery points below constituency granularity (Windhoek suburbs/kiosks/malls + rural villages) so links are L1 region; B14 revisit: 149/149 re-verified vs poster PDF + prefix rule, tree Karas→ǁKaras + Okorukambe→Okarukambe |
| Mozambique | MZ | 145 | 151 | Mapanet re-pull (436 rows / 138 r2 cells, up from 329/100; all 113 kept codes still present, cell attribution consistent) × OSM containment per locality: 32 fills (1304 Vilanculos, 1310 Panda, 1312 Zavala, 2106 Nhamatanda, 2112 Muanza, 2114 Marromeu, 2309 Tsangano, 2312 Zumbo, 2401 Nicoadala, 2402 Namacurra, 2405 Pebane, 2409 Morrumbala, 2413 Mopeia, 2415 Namarroi, 3104 Mossuril, 3107 Muecate, 3110 Murrupula, 3113 Nacala-a-Velha, 3115 Erati, 3119 Ribaue, 3202+3205 Metuge, 3209 Namuno, 3213 Quissanga, 3215 Muidumbe, 3218 Nangade, 3219 Palma, 3302 Sanga, 3307 Metarica, 3308 N'gauma, 3310 Nipepe, 3311 Muembe) + Doa secondary on 2307 (Doa town row in Mutarara cell); 4 multis (3206 Ancuabe P + Chiure/Mecufi via Murrebue, 3211 Macomia P + Ibo via Quirimba/Quissanga, 3301 Mecanhelas P + Mandimba, 2307 Mutarara P + Doa); holds: city codes (Beira 2100-2103, Matola 1105/1112-1114, Tete 2300/2301, Quelimane 2400, Pemba 3200/3203, Nacala 3112, Inhambane-city 1300, Maxixe 1301), new-district cells (3118 Rapale, 3105 Ilha), mixed cells (1200/1201/1202 Xai-Xai city+Chonguene, 3100 Nampula city+Rapale, 3111 Mogincual/Liúpo/Mossuril split, 3201 Mieze), 1213 Beira-cell anomaly (not Xai-Xai); 132/136 districts (codeless: Xai-Xai, Mogincual, Nampula rural, KaMaxaquene); 11 post-2013 districts + Ilha + Inhambane/Maxixe-D backlog |
| Nepal | NP | 753 | 753 | 2025 federal palika system (digit-1 = province; old 1991 district/office codes superseded): postcodenepal.com 77 district pages (748 palika codes, all districts sequential XX01–XX0N single-block; Kaski/Pokhara dup page deduped; prabat-URL page = Parbat; 18 slug spelling variants mapped; nawalparasi→Nawalpur east) + Khotang 10501–10510 derived (only free Koshi slot 101–114, 10 palikas per district page, district-level only); anchors: gov local-level PDF (40504 Pokhara/40710 Aabukhaireni/10707 Chaubise/11101 Mechinagar all match) + nepalish KMC 30608 + ward-pattern 30608-01–32; GPO site unreachable |
| Nicaragua | NI | 890 | 890 | B15 inline (2 renames SJRC-del + Waspam, zero leg moves; gate_ni ALL PASS): 16/17 archived operator dept maps read code-by-code (Madriz 404, OSM-covered), 144/144 Nominatim sweep, Mapanet Managua 609/609 subset, codigo-postal.org 25 spot incl. 7 OSM-overrules, UPU 5-digit; holds: SJN-del-Norte vs INIDE-de-Nicaragua, X0/13003 absent. Original bundling: Official Correos de Nicaragua allocation via Wayback print-all (PostalCodes.php?operation=printall, snapshot 2013-09-30, 929 rows = Wikipedia's 929: 153 municipality Código-Maestro rows + 776 Managua barrios → 890 distinct 5-digit codes, zero multi-municipality); live finder Cloudflare-403 from this network; 6 spelling aliases (San Juan de Nicaragua→San Juan del Norte, Cruz del Río Grande→La Cruz, Desembocadura de Cruz Río Grande→Desembocadura, Kukrahill→Kukra Hill, Waspam→Waspan, El Tuma La Dalia→El Tuma - La Dalia); Mapanet full pull (774 rows/753 codes, both UPU anchors 12005 Santa Ana Sur + 11147 Bello Horizonte present) agrees on 753/890; all 153 municipalities covered |
| Nigeria | NG | 1926 | 1926 | Mapanet full pull (29,099 locality rows, 1,947 distinct 6-digit codes; no GeoNames NG postal dump, no NIPOST machine-readable source): state via 3-digit dispatch-prefix majority over 37 region titles (215 prefixes, all ≥97% except 3 adjudicated) with Mapanet misfilings corrected by locality evidence — 620–632 Yobe filed under Zamfara (Damaturu/Nguru/Bade), 660–672 Taraba filed under Yobe (Jalingo/Ardo-Kola/Bali/Arufu), 882 Zamfara filed under Taraba (Kauran Namoda); Gusau 880001 per NIPOST-mirror majority (konnect/zipcode.ng/postalcodes.com.ng) so 6 Gusau 860xxx rows dropped as wrong-code (860 = Kebbi/Birnin Kebbi), 6 Igbo-locality 842xxx rows + 1 Kauran Namoda 852xxx row + 1 888222 row dropped as conflicts, 7 malformed codes dropped; 61-row 982101–982104 Benue cluster KEPT above Wikipedia's stated 982002 max (unanimous Benue attribution, Kwande-area towns); zero multi-state codes; L1 state links (774-LGA join needs NIPOST LGA facility file) |
| North Macedonia | MK | 326 | 326 | Makedonska Pošta 2016 unit list + settlement directory (1000–7550; 1137 uncertain commune + retired/stale codes excluded) |
| Norway | NO | 5110 | 5110 | Bring Postnummerregister current (gjelder fra 1.10.2026, 5112 codes) + 2024 vintage + logg-nye/endr (GN proven stale: holds all 40 retired, lacks all 16 additions); B19: 14 fills (8 logg-nye + 0040/0540 Oslo + 9173–9176 Svalbard) + 40 drops (39 opphør→9999 + 8128→8120 redirect); 2 S-codes held (0046/0047) |
| Niue | NU | 1 | 0 | UPU single-code list (9974) |
| Oman | OM | 99 | 99 | Parcelforce Oct-19 posting guide (Wayback; live URL Akamai-403) × youbianku live directory × ntdtvjp mirror (99/99 incl 424; YBK 423-dupe on Dama Wattaeen rejected + doubled 614/615/616 rows) × zipcode.com.ng office street addresses (111 Airport Heights, 114 Jibroo/Port Sultan Qaboos, 314 Musanaa court, 515 Ibri Dariz Rd) × citypopulation census locality→wilayat × ar/en wiki wilayat + village articles × Nominatim/Photon wilayat-boundary containment × ROP Muttrah districting (Ruwi/Hamriya/Wadi Kabir/Darsait) × addressed rows; UPU OMN 01/2026 anchors 112 Ruwi + 133 Al-Khuwayr + 311 Sohar; all 99 single-primary, zero duals; 127 Wattayah adjudicated Muttrah (Yandex + LEI + ROP-station + sakan + dubizzle over OSM polygon), 129 Seeb + 213 Salalah weak keeps; 517/518 current Buraimi admin over stale PF Dhahirah label; 615 Samail (not Nizwa), 621 Saiq→Jebel Akhdar, 329 Ar-Raddah→Saham; 5 wilayats codeless in all 4 directories (Duqm/Mahout/Mazyona/Shalim/Wadi Al Maawil); 100 addressed-only general code + 138 Al Mouj + zng x00 rows excluded as unverified singles; zng branch-ID table (Mutrah 169 etc) proven non-postal |
| Palau | PW | 2 | 16 | USPS bulletin (96939 Ngerulmud/Melekeok, 96940 rest, Koror primary); ZIP+4 strips to 5-digit base at lookup |
| Pakistan | PK | 3114 | 3121 | Pakistan Post office directory (3130 delivery offices; GPO service areas × GeoNames admin2 × post-split crosswalk; 7 NPO code collisions dual-linked incl. 07529 Dumba Goth/Gulistan-e-Jauhar; Quetta East/West by railway line; 11 districts with no table office: Allai, Darel, Haveli, Kolai-Palas, Lower South Waziristan, Mohmand, Roundu, Sohbatpur, Surab, Upper Dera Bugti, Wadh; Shigar covered via 16810). M6 revisit: tree + 3114/3121 overlay re-verified clean vs live Pakistan Post table + PART-I/II + Circulars 4/2022 + 15/2021 (7 duals kept, 318-code singleton sample + 155 block-odds accounted for); Circular-11/2023 Islamabad 5-code watch item; `gate_pk.py` ALL PASS |
| Palestine | PS | 603 | 603 | [Ministry postal-zone table](https://site.mtde.gov.ps/home/PostalCodes), accessed 2026-09-25 (755 locality/code rows; 603 distinct P3 codes); all rows fall within the 16 governorate ranges in [Instruction No. 1/2022](https://mjr.ogb.gov.ps/Decrees/ViewText/32052), with no cross-governorate codes; each published code links once to its bundled L1 governorate. (Decree page is Cloudflare-403 with no Wayback snapshot; same official text in Gazette 187 verified via [An-Najah Maqam mirror](https://maqam.najah.edu/media/uploads/2022/01/legislations/%D8%A7%D9%84%D8%B1%D9%85%D8%B2_%D8%A7%D9%84%D8%A8%D8%B1%D9%8A%D8%AF%D9%8A.pdf).) A full Mapanet pull (2026-10-03, 866 rows) is set-identical at 603 codes; its only cross-governorate code is P149, where Beit Safafa + Sharafat (Jerusalem) outvote the lone Al Walaja (Bethlehem) row, so no Bethlehem secondary is bundled — the ministry list and legal range also place P149 in Jerusalem. [UPU profile](https://www.upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/pseFr.pdf) 05/2025 independently confirms P126, P144 and P610 examples. The page has no published vintage or reuse terms; locality areas remain unbundled |
| Papua New Guinea | PG | 67 | 76 | Mapanet 219 rows / 56 r2 cells (=districts, LLG sets; all 22 provinces anchor-mapped) → district via seat LLGs + Morobe LLG navbox (423 all-Bulolo incl. Waria/Watut, 427 Menyamya, 422 Wau town → Wau-Waria) + Ijivitari 5-LLG table (241 single); NCD 9 codes suburb-mapped (Badili/Konedobu/Town→South, Boroko/Jacksons→North-East, Waigani/University/Parliament/Gerehu→North-West per voter-registration convoy report); 4 multis (461 Chimbu-wide Kundiawa-primary, 355 Bougainville-wide Buka-primary, 293 Wapenamanda P + Kandep s on 2v2 Tsak/Wage split, 136 North-West P + Goilala s); 60/96 districts (36 rural codeless in source); B13 re-verification: Mapanet full scrape (86/86 n3 pages, 219 rows) reproduced the bundled 62-code set exactly; +5 Post PNG office fills (135 Gordons→NE per PNGEC 2022 schedule, 332 Tabubil→North Fly, 512 DWU→Madang, 613 Kokopo→Kokopo, 635 Lihir→Namatanai), tree rename Bulolo_District→Bulolo; held out 541 (addressed usage prints Manus 641) + 417 Gusap (district unattributable) |
| Paraguay | PY | 2887 | 2887 | B17 inline (verify-only, zero changes; gate_py ALL PASS): 259/259 p4 + 18/18 p2 pure, gap-free sectors, 0 multis; youbianku transcription re-verified + 9 codeless confirmed empty + 001013 operator anchor. Original bundling: youbianku 6-digit enumeration (2887 codes, 30 list pages; DGEEC-backed Dinacopa system: dept(2)+district(2)+sector(2)) → district via 4-digit prefix (259 prefixes, 1 detail breadcrumb each, 8×3 purity sample all pure; 44 abbrev/typo bridges incl. Asunción 6 prefixes→city-district, Guayaibí/Botrell/Ycuamandiyú spellings); Mapanet PY holds the obsolete 4-digit system (Asunción remap non-mechanical, excluded); 254/263 districts (9 small/new codeless: Boquerón, Campo Aceval, Cerro Corá, Itacuá, Laurel, Nueva Asunción, Paso Horqueta, Puerto Adela, San José del Rosario) |
| Peru | PE | 2669 | 2671 | B16 inline (4-code Loreto rotation fix + 42 leg moves + 2 renames; gate_pe ALL PASS): bundle set == GN set exactly (2669/2669); GN spans >1 admin2 on exactly the 2 multis with matching primaries. Original bundling: GeoNames dump at province level (01000–25701; all 196 admin2 join clean; 2 cross-province codes dual-linked: 14000 Chiclayo-primary + Lambayeque, 14013 Lambayeque-primary + Chiclayo) |
| Philippines | PH | 1919 | 1919 | B22 (15 drops + 2 relegs + 2222 add; gate_ph ALL PASS): PHLPost locator (960 rows, incomplete) + jayson + GN 2190-code set diff (254 institutional/PO-box correctly excluded, 2 junk); retired DavOro 81xx ×9, old-Cebu ×4, 0905 big-user, 4532 stale dropped; 9610/9612→MDS; 2222 Subic→Zambales; H1 1Q2026 forward-delta held (bundle 1Q2025-vintage); sub-office singles + 8016 scope quirk pinned; NCR legs 1000–1899. Original bundling: GeoNames dump at province level (1000–9811; 258 facility/PO-box codes excluded; NCR linked at region) |
| Poland | PL | 20248 | 20395 | Poczta Polska SPNA official file at powiat level (00-002–99-742; 332 multi-powiat codes dual-linked); attribution verified against independent MapAnet crawl (34,770 locality rows, 17,917 codes) via gazetteer TERYT: multis 21 both-sides + 174 one-side confirmed, 136 Mapanet-silent; singles spot-sample 150: 127 agree, 0 contradicted, 23 silent; sole contra 14-120 adjudicated script artifact (single-row Jankowice gazetteer tie 2808/2803, both range-absurd for 14-1xx Ostróda area — staged ostródzki P + Olsztyn s stands); dashless input gains the official dash at lookup; B19r1: 198 SIMC-vote conflicts adjudicated vs live PP finder → 154 codes changed (95 flips incl. 10 same-name-twin clusters, 10 swaps, 59 artifact drops), 15 keeps, 29 holds; B19r2: 525 PP-probed (332 multis + 198 conflicts + 94 inverse + 10 novote) → 227 codes (17 flips, 81 swaps, 142 drops, 10 adds, 51 stale-code drops); holds H-ADD/H-COV(87-220)/H-MEDIUM |
| Portugal | PT | 197772 | 197772 | GeoNames dump at municipality level (206,942 street rows → 197,772 distinct 7-digit codes 1000-001–9980-999, code set exact; zero cross-municipality codes so 1:1 links; all 308 municipalities covered, none codeless; spelling-variant join Vila da Praia da Vitória→Praia da Vitória 397; B18: bundle Lisbon→Lisboa, postal pair CRLF→LF, 25/25 CTT-mirror checks) |
| Puerto Rico | PR | 177 | 177 | GeoNames USPS ZIP dump joined on municipio FIPS (00601–00962; PO-only status per ZIP unverified); ZIP+4 strips to 5-digit base at lookup; B20 verify-only: tree == Census 2024 exactly (78+901), 177/177 GN-matching legs, 00938 held for human USPS lookup |
| Réunion | RE | 37 | 37 | La Poste Hexasmal (Sep 2026) |
| Russia | RU | 43531 | 43531 | GeoNames 6-digit dump (43,538 rows → 43,531 codes at federal-subject L1; tier-2 raions stay parked per doc 05); stale GN names remapped (Chita Oblast → Zabaykalsky incl. 687 Agin-Buryat, Kamchatka Oblast → Kamchatka Krai incl. 688 Koryak); 5 okrug/subject gaps rescued by prefix+place (625/626/627→Tyumen, 628→Khanty-Mansi, 629→Yamalo-Nenets, 166→Nenets, 679→Jewish AO, 689→Chukotka; B11 fix: 136 x 626xxx Tobolsk/Tavda/Vagay/Isetskoye moved KHM→Tyumen per ru-WP prefix table + Nominatim RU-TYU + WP districts); Baikonur 468xxx dropped (Kazakhstan); 144700 UFPS-MO office kept at Moscow city, 78 x 901xxx mail-route codes kept at origin region; 3-digit prefix coherence clean otherwise; no 26x–29x (Crimea/new-territory) codes |
| Romania | RO | 37914 | 37914 | GeoNames dump at department level (010011–927250; street codes linked to county). B21 (2026-10-06): postal zero changes — bundle == GN export exactly (same lineage); mirror partial (CV 186/186 + IF 105/105 set-equal); integrator prefix-join 37914/37914 clean; 330-sample + 3 county shortfalls held |
| Senegal | SN | 163 | 182 | La Poste commune table via gist mirror (4158 quartiers, 161 codes) re-verified against the live official postal-data.js (4143 rows; identical 163-code set incl. bureau rows 10200 Dakar RP + 16500 Thiaroye; UPU 10000/27000 examples match; junk 0/159/36/41 dropped) + per-commune re-adjudication (fr.wiki dept/commune/arrondissement articles, OSM CR boundaries, GeoNames coords × GADM polygons; Keur Massar 2021 split applied; Palmarin/Dionewar/Paoskoto misfiles corrected; Same Kanta attested Sédhiou; Nghoye unattested, non-Diourbel either reading; postcodebase transcription-only, its dept column disproven) + 14 M2 link fixes (12 stale secondaries dropped, 24027 → Fatick, 20600 + Tivaouane; 19 shared codes, quartier-majority primary; 32800 9-9 tie keeps Dagana) |
| Saint Helena | SH | 3 | 10 | UPU single-code list (STHL/ASCN/TDCU 1ZZ; Jamestown primary for STHL); compact input gains the official space at lookup |
| Saint Kitts and Nevis | KN | 32 | 39 | post.kn zone/district PDF (KN0101–KN1202 + KN7000 SEP; 7 cross-parish codes dual-linked; Nevis 08–12 named by parish; village→parish per parish articles, Lodge to Christ Church); bare domestic input gains the KN prefix at lookup |
| Saint Lucia | LC | 47 | 48 | Government of Saint Lucia postcode table (LC01 101–LC18 101; 7 private-box codes excluded; Marisule dual-linked); compact input gains the official space at lookup |
| Saint Pierre and Miquelon | PM | 1 | 1 | UPU addressing (97500 both communes) |
| Saint Vincent and the Grenadines | VC | 56 | 56 | SVG Postal Corp official list (VC0110–VC0472; VC0100 box-only + VC0292 disputed Mesopotamia omitted); bare domestic input gains the VC prefix at lookup |
| Saint-Barthélemy | BL | 1 | 1 | UPU addressing (97133) |
| Saint-Martin | MF | 1 | 1 | UPU addressing (97150) |
| San Marino | SM | 10 | 10 | UPU SMR profile (47890–47899; Serravalle holds 47891+47899) |
| Samoa | WS | 223 | 240 | Samoa Post official list (224 pairs) × SBS 2021 Village Directory PDF (341 villages + constituency + pop): 187 L2 single, 13 L2-split (primary = largest census pop), 1 L2-multi (Manono Tai WS1190 → 4 island villages per UNESCO), 22 L1 district links (townships Falelatai/Salelologa/Satupaitea/Mulifanua + block-pure micros); 4 naive-match traps fixed (Vaiala WS1332→Tuamasaga split, Safune WS2386→Gagaifomauga L1, Saletele WS2373→Gagaifomauga L1 via Photon hamlet, Papa WS2482→Papa Uta Vaisigano); GeoPostcodes disregarded (wrong districts + coords); Lolua WS2374 omitted (no census/OSM/web presence; WS237x straddles Gagaemauga/Gagaifomauga boundary); B18: itumalo-over-SBS → 5 village re-parents + WS1434 → Atua / WS2491 → Vaisigano district links |
| Serbia | RS | 1341 | 1411 | Mapanet municipality pages (145 munis, 4281 locality rows; Belgrade at city-municipality level) + 104 GN-only town/village codes (muni inherited from mapanet locality, 32 via Nominatim with Đurđevo→Žabalj + Kaluđerske Bare→Bajina Bašta fixes) + 100 courier-list Belgrade branch codes (generic at city); Kosovo r1 rows excluded (Posta e Kosovës system, XK overlaid); B15: +4 cities (Niš/Vranje/Požarevac/Užice), 45 district→city moves, 8 primary flips, 11102 +Savski Venac, 11150/11167 off L1-generic, 7 fills (Niš 18101/103/104/105, Užice 31109, 11042, 11197); 2026-10-06 retry: 11040→SV, 11050→ZV, 17508→Vranje, 18110→Niš, 18251→Niš, 18252/18411 sole, 91/92 L1→munis; 67 shared codes dual-linked (operator + OSM primaries) |
| Slovakia | SK | 3480 | 3514 | GeoNames dump at district level (010 01–992 14; office-number rows resolved via town→district from street rows, Rajec→Žilina; Bratislava blanks via 2nd-digit district rule anchored on street rows + verified 851 01 Petržalka-V / 841 04 Karlova Ves-IV via orsr.sk + Wikipedia street list; B11: 114 KI office codes Trebišov-town/Kráľovský Chlmec→trebišov, Spišská Nová Ves, Michalovce, Rožňava, Sobrance→own districts, Moldava nad Bodvou→Košice-okolie, 044 54 železiarne→Košice II; Košice-city 215 office codes stay at region; 33 cross-district dual-linked, majority primary with prefix/post-office tiebreaks incl. 906 35 Malacky, 985 42 Lučenec, 985 45 Detva, 094 06 Vranov / 916 13+916 16 NMnV / 930 28 DS / 976 81 Brezno / 980 33 RS / 985 22 Poltár on prefix, 067 82 Snina, 040 16 KE-II) |
| Slovenia | SI | 468 | 469 | Pošta Slovenije official list Aug-2025 via archive (1000–9503; 76 PO-box/large-user/internal excluded; 3231 Grobelno dual-linked Šentjur primary) |
| Saudi Arabia | SA | 9256 | 9256 | Mapanet full pull (218,705 rows, 190k ZIP+4 codes → 9,256 5-digit bases, all 13 regions; Wasel -XXXX suffixes strip to base at lookup via new SA normalizer): L1 region links; 60 multi-region bases adjudicated single by Nominatim reverse-geocode (all tight clusters = Mapanet duplication: 20 Bahah inc. 28769 Mikhwah, 31 Makkah inc. 3 Ghamid-Az-Zinad rows both sources misfiled, 5 Asir, 58276 Qassim, 58459 Riyadh/Dawadmi, 89799/89934 Jazan); 3 missed r2 groups backfilled (Diriyah/Al-Ardah/Al-Mikhwah, 307 rows); GAPS: Riyadh city 11xxx entirely absent from source + Arar city missing (N.Borders 74 rural rows) — documented codeless (PG precedent); L2 governorate not attempted (98 r2 zones ≠ 139 governorates 1:1 in 8/13 regions, no village gazetteer). M5 revisit: 86365/86366/86369 Madinah→Jizan (Samtah block; per-region now Madinah 651 / Jizan 948); 11xxx absence reclassified correct (POB space per UPU, ex. 11564); Arar-city gap scoped to ~59 codes + NEW Riyadh-center 12–14xxx hole (~990, incl. Olaya 12211–12214) on current Mapanet — both await full re-pull; `gate_sa.py` ALL PASS |
| South Africa | ZA | 3277 | 3318 | B9 revisit 2026-10-03 (fix-and-fill): SAPO postalcodes.txt live pull (15,373 rows, 3,984-code union, set-identical mirror) + GeoNames ZA.zip postal (3,266 = old set) + gazetteer (103k) + Blaauwberg live scrape (16,735 rows) + Treasury demarcation API (52 L2 set-identical) + ISO 3166-2:ZA + UPU ZAF anchors; street-first re-attribution fixes 101 postal-town-inheritance primaries (Temba/Hammanskraal->Bojanala 0418/0419, Siyabuswa->Nkangala 0472, Edenvale/Tembisa->EKU 1609/1689, Naledi->JHB 1861, Thohoyandou->Vhembe 0950, Taung block->DC39, Kranskop block->DC24, Dordrecht block->DC13, Bethulie->Xhariep 9992, Vredendal->West Coast 8160, Colchester->NMA 6175); 7 bundled duals kept w/ 2+ signals each (5 swap legs), +33 new secondaries (Dalton/Greytown 3236, Klipdale/Klipfontein 7283, Rabie Ridge 1632, Ballito 4399, Vivo-office 0924, Campbell 8306/8360) = 41 duals; +11 street-distinct SAPO+BB fills (0180/0321/0323/0359/0880/1682+EKU/2310/2539/6445/6750/9423); stability keeps 5900 DC13 (2016-boundary), 1693 JHB, 2778/2779 DC38, 9323 MAN; 178 SAPO-only street + 496 box-only excluded, 21 GN-backed SAPO-missing kept; tree verify-only 61 areas; gate_za.py ALL PASS, Pest 204 assertions |
| South Korea | KR | 34249 | 34249 | B17 worker (VERIFIED-STALE, zero changes; gate_kr ALL PASS): set == GN Oct-2026 exactly, 452/452 p3 blocks agree with en.wiki postal table, 29/29 Nominatim agree; Jeonnam-Gwangju merger + Incheon reorg (both effective 2026-07-01) held pending structural decision + Korea Post mapping. Original bundling: GeoNames 5-digit dump joined on si/gun/gu via Revised Romanization + 29 phonetic-assimilation exceptions (Pyeongtaek/Buk/Jungnang/Gangneung/Jongno/Mokpo/Chilgok etc.); general-gu collapsed to parent si (Suwon/Seongnam/Goyang/Yongin/Changwon/Cheongju/Cheonan/Jeonju/Pohang/Ansan/Anyang); Sejong 142 codes linked at city (no L2); no cross-area codes; 06076 Gangnam/03056 Jongno/63001 Jeju-Chuja match youbianku + Korea Post example |
| Spain | ES | 11068 | 11094 | Correos nuclei API full exact-hit sweep of all 11150 bundled codes (11063 exact; 79 nucleus-404 re-verified stable + 8 fuzzy-neighbour matches exposed by response audit; all 87 with zero holders across fresh 7861-muni all-province sweep) + INE Callejero (11051 codes) + 182 CartoCiudad leg queries + Nominatim + Wikidata P281 + UPU ESP profile via Wayback 2018 + GeoNames ES.zip (bundled == GN set): 87 removals (renumber supersessions incl. 34260→09117 Revilla Vallejera, 33692→33693/33694 Lena, 33837/33838→33830 Belmonte, 15591→15590 Ferrol, 03115→03110, 42175→42174/42181, 28419→28412, 33599→33579, 37608→37609, 42147→42146; dead apartados 30070/30071/30080; withdrawn city sectors 06012/08805/11200/11574/28870/34006/36281/36282/36339/47018; reservoir/station phantoms 10396/13434/29395), 5 fills (01070 VI, 09117 BU, 21431 H, 24359 LE, 50221 Z absorbing the 42269 Z-leg), 3 dropped legs (28189-GU, 28310-TO, 42269-Z), 7 new dual legs (13249-AB, 14113-SE, 16612-AB, 18312-CO, 26528-Z, 28600-TO, 45216-M, each Correos + independent second), 3 moved primaries (28310 TO→M Algodor, 42269 Z→SO, 06691 BA→CC Cíjara/Alía), 19 kept duals incl. Treviño trio + Tresviso/Lastrilla enclaves + 13110 CR / 22584 HU / 44591 TE prefix-home primaries, kept cross-prefix singles 14449 CR / 22806 Z / 26127 SO; holds: 18538-J / 23296-AB / 45217-M single-source legs, 28090 + 52901-52905 + 00000 Correos-internal excluded; `gate_es.py` ALL PASS |
| Sri Lanka | LK | 2121 | 2121 | Dept. of Posts Post Code Directory 2022 at district level (00100–91559; Colombo 01–15 zones incl. 10 from scanned p.iii table; APR+AR both Ampara) |
| Sudan | SD | 90 | 97 | Mapanet state leaves (326 locality rows, 16/18 states; 5-digit codes 11111–63314; r1→state via capital anchors: Khartoum, Kassala, Al Qadarif, Singa/Sinjah + Dinder, El Obeid + Bara + Er Rahad, Ad Damazin, Ad Douiem + Kawa, Argo, Buram, Kutum, Berber-area; 8 cross-state codes dual-linked with row-majority primaries: 21115 Jazirah, 25514 Blue Nile, 31116 Gedaref, 51111/51113/52221 North Kordofan, 13315 Khartoum + River Nile (16.0–16.3N border cluster: Al Jayli/Alquili/Esh Shaheinab/Ad Dawm south vs Abo Dawm/Ash Shubrab/Mount Kira north), 63314 West Darfur + Central Darfur (Fongfong ≈ Zalingei 25km); East Darfur codeless in source). M6 revisit: 13315 River Nile secondary dropped (SCC directory Khartoum-only + OSM border at ~16.42N puts the whole cluster in Khartoum; 7 duals remain); full SCC Oct-19 directory diff (90 codes exact, 6/6 agreed duals incl. primary order) + Fongfong-in-Central-Darfur keep for 63314; `gate_sd.py` ALL PASS |
| Sweden | SE | 18887 | 18887 | B17 worker (62 leg moves + Göteborg rename, zero code changes; gate_se ALL PASS): bundle set == GN set exactly (0 multis, 0 removals); Kinda 0→10, Ydre 1→5, Höör 6→26; ~3815 box/business codes held for contract decision. Original bundling: GeoNames 5-digit dump (18,887 codes, 1 row each, spaced print); locality→municipality join via gazetteer admin2 kommun codes + county filter with alternate names; 84% rows carry direct postal admin2 — cross-validated join (15,003 agree), 717 disagreements arbitrated by learned 3-digit prefix profiles (558 auto) + Nominatim/OSM coords + place evidence (159: postal noise like Västerhaninge→Nynäshamn, Alunda→Uppsala, Malmköping→Strängnäs overruled; gazetteer same-name errors like Vega→Huddinge, Enebyberg→Täby, Olofstorp→Tidaholm, Torsby→Hagfors overruled); 271-place manual alias table (archipelago islands, typos: Fasta→Farsta, Bällinge→Bälinge); zero cross-municipality codes; spaceless input gains official space at lookup |
| Switzerland | CH | 3177 | 3220 | B15 re-verification: code set = GeoNames ∩ swisstopo exact (185 box/firm + 13 LI 9485–9498 exclusions vindicated; 9000 St. Gallen geographic, included); Jura-Nord vaudois + Zürich (district) renames; 2740 Moutier-primary (2026 JU transfer, Roches sliver keeps JB secondary), 1595 See/Lac (Clavaleyres→Murten 2022), 1015 Ouest lausannois (campus); drops 1911 Conthey (Mayens-de-Chamoson=1955), 6825 Lugano (pre-2022 Rovio dupe), 2333 LCF (La Cibourg hamlet in Renan BE); flips 3994→Goms (Lax 312 vs Martisberg 19), 1958→Sierre (St-Léonard 2460 vs Uvrier ~1400); 42 multis; ~324 swisstopo-only slivers held (legs require GN corroboration); NE 6 pre-2018 districts + LU merge + Raron split + AI 5 kept as design |
| Taiwan | TW | 365 | 368 | Chunghwa Post 3-digit district table (PDF decoded via ToUnicode CMap, 368 rows) × twzipcode-data transcription (0 conflicts; district suffixes + Taoyuan city upgrade reconciled); 300 Hsinchu triple-linked North primary (city hall in North per city health-bureau doc + OSM polygon), 600 Chiayi dual-linked East primary (city hall in East per OSM); 817/819/290 island codes excluded (no bundled area, uninhabited/disputed); street-level 3+3 suffixes out of scope at L2; B19: links clean 0/368 moves, 31 tree reparents (CYI/CYQ + HSZ/HSQ) |
| Tanzania | TZ | 4096 | 4096 | B16 worker (tree NBS-2022: +Mlimba/+Mtama/+Kibiti, Mpanda→Tanganyika, −Kilombero/−Lindi; postal 124 fills + 73733 swap − 63 removes + 59 moves; gate_tz ALL PASS): TCRA gazette + NAPA scrapes + NBS ward lists + 100 live lookups; Zanzibar rebuilt (91 fills/60 phantoms); Mpimbwe-9/Madaba-8/Lindi MC-31 exact. Original bundling: TCRA postcode API full pull (85,110 locations; urban/rural + split-district wards resolved via council ward lists: Madaba from Songea, Tunduma from Momba, Mpimbwe/Nsimbo; Kibiti + Tanganyika wards unmapped — no bundled district; Mpanda rural + Mpimbwe have no API wards) |
| Thailand | TH | 789 | 930 | GeoNames 5-digit dump (903 rows → 771 codes); transliteration-heavy join verified province-by-province incl. 18 stale/wrong-admin1 manual fixes (Bueng Kan 2011 split from Nong Khai, GN Phetchaburi/bun confusion → Phetchabun, Lamai Beach → Ko Samui), Bang Sai split by geocode (13190→1413, 13270→1404 per infobox); 110 shared-office codes dual-linked with pop-majority primaries (citypopulation 2010; 4 near-ties <10% defer to Thailand-Post-derived Parcelforce office label: 10250 Prawet, 10510 Khlong Sam Wa, 10700 Bangkok Noi, 22160 Na Yai Am); PDF office label matches a GN member on all 110; B20: 2 remaps (67000, 43170) + Surin +13/+17 + BKK +1/+6 + 5 legs + 42190 + 3 moves (23170/42220/41280); cross-province 41220 killed (GN artifact); 0 codeless |
| Tunisia | TN | 969 | 982 | Fresh 4,860-row Mapanet TN re-pull (old gov codes remapped: Nabeul 15→21, Bizerte 17→23, Sfax 34→61, Gabès 51→81, Gafsa 61→71, …) code-set == Postal-codes-in-Tunisia JSON 969/969; WPC 918 subset; old 795-set under-linked at r2-match granularity (transliteration-mismatched r2s dropped). +174 fills / +176 links at L2 delegation (213→258 covered): seats 3000→Sfax Ville (r2 Médina + directories vs OSM Sud point), 4000→Sousse Médina + 8000→Nabeul (roster + Nominatim + addressed sightings); 12 namesake offices (Radès 2040, Ras Jebel 7070, Sidi Hassine 1095, Sbeïtla 1250, Sbiba 1270, Sbikha 3110, Tajerouine 7150, Soliman 8020, Menzel Temime 8080, Sousse Riadh 4023 + seats); attribution mp-r2 × WPC-town 167/167 mapped; WPC sfax-est≡Sfax Ouest via OSM post-office nodes 3023/3071 + echoes; 9 old multis re-adjudicated, primaries kept (1002 Khadra 2v2v1, 2035 Soukra 4v1 + Soukra-2 roster, 2052 Carthage 5v2, 3200 Sud 10v10 tie + Nominatim, 4100 Sud 16v7, 7029 Nord 5v3, 7050 Menzel Bourguiba 6v1 + roster, 8014 Béni Khalled 1v1 tie, 8100 Jendouba seat 8v1); 2 new multis (1008 Médina 5v1 + Béchir, 2089 Kram 3v1 + Goulette); singleton sweep 15 agree / 0 corroborated errors (2097 Bou Mhel + 4215 Douz Sud single-point holds); skips: 7 La Poste-newer codes single-signal (1049/2001/2002/2079/4050/4083/4162), UPU-only 8129 in no directory |
| Turks and Caicos | TC | 1 | 0 | UPU single-code list (TKCA 1ZZ); compact input gains the official space at lookup |
| Turkmenistan | TM | 49 | 73 | Mapanet 267 locality rows (49 distinct 6-digit codes, ~1 per district); Nominatim reverse zoom-10 for etrap attribution (all 267 hit); city/etrap pairs split by suffix (şäheri→plain id as city, etraby→prefixed id as etrap, documented assumption); Hazar→Balkanabat city and Garabogaz→Türkmenbaşy etrap verified via Nominatim hierarchy; 744000 Ashgabat-general quad-linked to 4 boroughs (Berkararlyk primary, central seat); 745220 Büzmeýin+Arkadag; 4-district 745160 reduced to Magtymguly seat (cross-region Bäherden rows = Mapanet noise); town-row singles dropped (Karabekaul, Khodzhambas, Tejen-Altyn), village border singles dual-linked (1 quad + 2 triples + 17 duals); M3 revisit: 2-cell wiki-bold fix on city names, 49-code set re-pulled from Mapanet (exact) + full Nominatim re-attribution, all primaries adjudicated keeps |
| Türkiye | TR | 2896 | 2903 | GeoNames dump at district level (01000–81950; all rows pass province plate-prefix check; central-district = Merkez + Mersin(İçel) alias; renames Kazan→Kahramankazan, Eyüp→Eyüpsultan, Aydınlar→Tillo, Ondokuzmayıs→19 Mayıs; Ereğli/Karadenizereğli + Doğubeyazit→Doğubayazıt + Çağliyancerit→Çağlayancerit spellings; 57 KKTC 99xxx codes excluded; 7 cross-district codes dual-linked, majority primary except 16270 Osmangazi on contiguous 160xx–162xx range + 44000 Yeşilyurt on higher coord accuracy with worldpostalcode confirming both sides; Çankaya/Konak/Malatya seat codes match worldpostalcode) |
| Ukraine | UA | 26579 | 26581 | B15 re-adjudication (1313 ops; 92 -> 2 multis; gate_ua ALL PASS): 85 worker multi drops applied after per-code reform-wholly + unique-anchor integrator check, 4 specials infobox-verified (Kurylivka-41671, Bubnivka-32011, Holoskiv-32340, Hrushiv-81016), 607 singles moves applied after 16/16 sample; HELD genuine cross-raion 82563 Stryi+Sambir (Matkiv-82563) + 47431 Ternopil+Kremenets (Pahinya/Karnachivka-47431); FLIPPED 90124 to Khust-sole (Irshavskyi councils, reform-wholly); row-error proofs Kalynove-Borshchuvate-93279, Mayak-53542, Luchka-42600/42547. Original bundling:  GeoNames 29571 rows joined to HDX COD-AB v05 KATOTTG settlements (29.7k admin4, name match within oblast; 22369 exact + fuzzy/aggr; GN coords 17% placeholder/wrong incl. oblast-level batches e.g. all-47xxx stamped Pidhaitsi coords, so names primary, coords only name-confirmed: pip4 3276, pip2c 1096, OSM settlement nodes 181, Nominatim 8, hromada/council 24, old-raion priors 1411, uk.wikipedia infobox+coords + postcode-neighbor tiebreaks for 38 residuals incl. Vatutine/Novomoskovsk/Katerynopil-class 2023-25 renames; 7 far-mismatch exacts fixed to neighbor raion: Stanyshivka→Vyshhorod, Makariv-08738→Obukhiv, Oleksiivka-37411→Lubny, Druzhba-town→Shostka, Rakovo→Tiachiv, Vynohradne→Kalmiuske, Yurkivtsi-30217→Shepetivka; 95 same-oblast boundary codes dual-linked, majority primary; no Crimea/Sevastopol rows in GN dump) |
| United Kingdom | GB | 2943 | 3750 | B13 re-verification: ONS NSPL Aug-2026 unit-postcode LAD/ward/usertype → ceremonial county/council via Lieutenancies Act Sch 1 (unitary table + Tees-centreline point-vs-river split: TS17/TS2 dual, TS15→NYorks, TS16/TS18–TS23→Durham, TS8→NYorks; London boroughs→Greater London, City separate, Scilly→Cornwall) × GeoNames GB place votes (3002 outwards) + 46 live postcodes.io arrays (reproduce new sets; old postcodes.io-derivation claim falsified — missing legs are pre-2020 units, builder bug); primaries = geocoded-live-unit plurality (358 flips); +E22 (Tower Hamlets)→GLondon, +MK20 (Milton Keynes)→Bucks fills; 61 drops vindicated (37 dead incl truncated EC1/W1/SW1/WC1 + retired W1M/WD1/WD2, BN91 ungeocoded-live, 23 Crown GY/IM/JE as own countries) + 10 NSPL-live holds (GIR/IM99/CH90/EN77/LS78/PO24/S94/SN80/SR43/TW98) + 33 micro-hold legs + 8 razor primaries (NG20/WA3/BT75/CW3/LA6/MK19/PH12/WV9) |
| United States | US | 40977 | 40977 | GeoNames USPS ZIP dump joined on county FIPS (00501–99950; DC 277 ZIPs linked at district; 11 stale-CT-county rows remapped to planning regions via CT OPM town crosswalk incl. Mansfield/Willington to Capitol; Yakutat 99689 to borough 02282; 96860/96863 kept on Honolulu over FPO dupes; 509 military APO/FPO/DPO ZIPs + 2 MH ZIPs excluded; cross-county secondaries need licensed USPS city file; 8-ZIP Nominatim spot-check incl. 99553/90210/10001/20001/60601/96860 matches). B21 (2026-10-06): verify-only, 0 fixes — 3143/3143 GEOIDs == Census 2024, 0 orphans, 0/40977 state-join mismatches, 33226/33642 county-join; holds: HUD 404/USPS wall, territory scope, 71 county-ambiguous |
| US Virgin Islands | VI | 16 | 16 | GeoNames USPS ZIP dump, island-attributed (00801–00851 at district level; subdistrict split + PO-only status need licensed USPS city file); ZIP+4 strips to 5-digit base at lookup |
| Uruguay | UY | 124 | 351 | Correo listadoCP (124 codes == bundled set exactly; 1943 locality rows) + GeoNames UY.zip (1964 rows, 122 codes; 20100 Punta del Este town + 27500 India Muerta zone are Correo-official, GN-stale) + UPU worked-address anchors (15600 Pando, 11600/12900 Montevideo, 70200 Rosario, 75000 Mercedes, 80300 Ecilda Paullier, 90000 Canelones, 15400 Santa Lucía del Este): 88 multis re-adjudicated by locality majority — 8 primaries flipped (37000 tupambae→CL-dept zero-support bug, 50200→Belén, 15700→Toledo, 30100→Solís de Mataojo, 91200→San Bautista, 91500→Sauce, 12400+12500 D→G), 11 zero-support legs dropped (55000→Barros Blancos cross-country join bug, 15000→Toledo, 15300/15900→Empalme Olmos, 15800→Nicolich/Pando, 37100→Las Cañas, 12800→D, 91500→CdC/EO, 12500→D), 14 legs added (11 new-municipio secondaries incl. paysandu:cerro-chato, 37000→Las Cañas per OSM 37000, 12400/12500→G); 15800+12800 collapse to singles, 12400+34100 go dual; open-log #15 retry Oct-2026 (IDEUy pip + CE circuits + INE): 5 flips (12100→F, 12000→D, 35200→Cerro Chato, 60200→Quebracho) + 7 drops (12000-E, 20500-Lascano/Maldonado, 33000-Vergara, 50000-Belén, 70000-Tarariras, 60200-dept) + 16 adds (8 MVD slivers, Soca, Conchillas, TT-dept, Chapicuy, CChato-PA); 351 legs, 93 multis; 3 holds stay (Tupambaé, San Jacinto, Carmelo) |
| Uzbekistan | UZ | 2140 | 2140 | Mapanet full crawl (205 leaves, 0 gaps after retries; 2137 codes 100000–231620 + 3 verified city mains: 140100 Samarqand + 190100 Termiz via my.gov.uz state portal, 230100 Nukus via regulation.gov.uz postal doc; all regions carry exactly one 2-digit prefix, Tashkent city 162 codes at L1 with own coding per UPU); zones mapped to tuman/city via district-center tables (Wikipedia region pages + statoids) with transliteration + splits: Kuyganyor→Andijon, Boʻz→Boʻston, Oqoltin→Ulugʻnor, Oqtosh→Narpay, Farhod→Xovos, Dehqonobod→Guliston-t, Paxtaobod→Sardoba-t (dual zones), Sayhun→Sayxunobod, Qarluq≈Korlik→Oltinsoy, Uxum→Forish, Muruntau→Tomdi (mine in Tamdy per Wikipedia), Kizil-tog→Angren city (part of city per mapcarta/OSM), Quvasoy villages→city (city includes rural communities per Wikipedia); Ingichka→Kattaqoʻrgʻon-t + Kogon/Xiva center rows split to cities; Karakuduk 120708 dropped (lone prefix anomaly in Navoiy); 71 L2 uncovered (12 Tashkent-city tumans by design; gaps: 8 cities incl. Shahrisabz/Ohangaron/Yangiyol/Xonobod/Shirin/Nurafshon/Gozgon/Zarafshon + 51 tumans incl. all of Fargʻona-t/Soʻx/Rishton/Oltiariq/Toshloq/Uchkoʻprik/Yozyovon/Oʻzbekiston/Buvayda, Termiz-t/Muzrabot/Shoʻrchi/Uzun, Urgut/Toyloq/Payariq/Paxtachi, Yakkabogʻ/Shahrisabz-t/Nishon/Mirishkor/Kokdala, Zomin/Zarbdor/Zafarobod, Paxtaobod/Shahrixon/Xoʻjaobod/Jalaquduq/Izboskan-An, Toʻrtkoʻl/Xoʻjayli/Taxtakoʻpir/Shumanay/Bozatov/Taxiatosh, Norin/Yangiqoʻrgʻon, Peshku/Qorovulbozor, Bekobod/Boʻstonliq/Parkent/Piskent/Qibray/Toshkent/Yangiyoʻl/Yuqorichirchiq/Oʻrtachirchiq, Yangiariq/Yangibozor/Xiva-t); UPU anchors 100000/100123/220605 present. M6 revisit: 120501–120505 Sardoba-t→Oqoltin-t (decree Sardoba PAB scope + OSM town containment; Oqoltin-t covered, 71 L2 uncovered); max-code + Mehnatobod errata fixed; 4-way code-set Venn (PCB 183-zone + MITC decree + Mapanet re-crawl) corroborates 2137/2140, 29/30 sampled attributions agree; gap list mostly falls (466 PCB+decree codes inventoried) but fills deferred to a voter-machinery pass; `gate_uz.py` ALL PASS |
| Venezuela | VE | 452 | 458 | Mapanet full pull (2,486 rows, 445 4-digit codes, all 25 regions; UPU anchors 1010 Caracas ×45 + 4001 Maracaibo ×77; IPOSTEL site unreachable): L1 state links with Gran Caracas metro codes assigned by physical locality — 1060 Chacao/1061 El Cafetal/1064/1073 Petare/1080 Baruta/1083 El Hatillo → Miranda, 1160/1162 → Vargas (La Guaira/Maiquetía/Catia La Mar), 1030 → Distrito Capital; Dependencias Federales section = 13 misfiled Miranda rows (Barlovento/Tuy coords) rescued to Miranda incl. 4 DF-only codes 1223/1224/1226/1241 (DF codeless in source); 5 shared codes: 2301 Guárico-p + Aragua-s (Barbacoas), 2334 Aragua-p + Guárico-s, 2350 Guárico-p + Anzoátegui-s (El Chaparro), 3101 Trujillo-p + Mérida-s + Zulia-s (Torondoy basin verified: zipcodehere 3101 Torondoy towns, 56ok Valera 3101), 3158 Zulia-p + Mérida-s (Caja Seca 3158 per postcode.info); 15 wrong-code rows dropped (2201 Barinas, 5101/5145 Zulia Catatumbo vs Mérida-city 5101, 6401 Cojedes filler vs Tucupita-capital Delta Amacuro, 7001 Guárico, 1030 Ciudad Tablita/Vargas/Colonia Tovar) + malformed 0; 2048/2061 Falcón + 5147/5148 Zulia + 8013 Anzoátegui kept single-state on odd prefixes; B18: all 5 shared codes reconfirmed vs fresh Mapanet pull, no changes; B19 r2: +8 codes/+8 state-only legs (2303/2304 Guárico, 3060 Lara, 3102/3108/3113/3115/3149 Trujillo; yb+pi each, Mapanet-conflicted admin2 HOLD), 3101-Zulia removal REJECTED (pi p3101: 3 Zulia towns) |
| Vietnam | VN | 3320 | 3320 | B21 re-verification (verify-only, zero changes; gate_vn ALL PASS): bundle set ⊆ true MOST (direct 3321-row recount incl. #VALUE! 05127; prior 3319 JSON was lossy); 3320/3320 legs MOST-correct, 68/68 hand sample; H1 22 wards held as 2026 conversions (live openapi 33/66 vs bundle 45/54, identical names); H2 05127 held (corrupt-row value); H5 GeoNames has no VN postal dump. Original bundling: MOST 2025 national postcode list (94pp; X./P./Đặc khu wards incl. 13 special zones; Tam Dương Bắc cell reads 152213, taken as 15221 sequential; Nghi Dương post-dates the list, omitted) |
| Wallis and Futuna | WF | 3 | 3 | La Poste Hexasmal (Sep 2026) |

Seed every bundled dataset with `PostalCodeSeeder`, or one country with
`address:seed-postal-codes {country?}` (seed countries and country geographies
first). For a custom dataset outside the bundle, import it with the generic
source directly:

```php
use AIArmada\Addressing\Actions\ImportPostalCodesAction;
use AIArmada\Addressing\Support\CsvPostalCodeSource;

$source = new CsvPostalCodeSource(
    countryCode: 'SM',
    codesPath: resource_path('geography/san-marino-postal-codes.csv'),
    linksPath: resource_path('geography/san-marino-postal-code-areas.csv'),
    areaSource: 'aiarmada_addressing_san_marino_v1',
);

$result = app(ImportPostalCodesAction::class)->execute($source);
```

## All-country verdicts

| Country | Code | Verdict | Finest tier (rows) |
|---|---|---|---|
| Afghanistan | AF | complete | L2: district (401) |
| Aland | AX | complete | L1: municipality (16) |
| Albania | AL | complete | L2: municipality (61) |
| Algeria | DZ | complete | L2: daira (548) |
| American Samoa | AS | complete | L2: county (15) |
| Andorra | AD | complete | L1: parish (7) |
| Angola | AO | none | L2: municipality (326) |
| Anguilla | AI | complete | L1: district (14) |
| Antigua and Barbuda | AG | none | L1: dependency,parish (8) |
| Argentina | AR | complete | L2: commune,department,partido (529) |
| Armenia | AM | complete | L2: district,municipality (82) |
| Aruba | AW | none | L1: capital_city,region (9) |
| Australia | AU | complete | L2: borough,city,council,municipality,region,rural_city,shire,town (539) |
| Austria | AT | complete | L2: district,statutory_city (93) |
| Azerbaijan | AZ | complete | L1: district,municipality (77) |
| Bahamas | BS | none | L1: district,island (32) |
| Bahrain | BH | complete | L1: governorate (4) |
| Bangladesh | BD | complete | L2: district (64) |
| Barbados | BB | complete | L1: parish (11) |
| Belarus | BY | complete | L2: district (118) |
| Belgium | BE | complete | L2: province (10) |
| Belize | BZ | none | L1: district (6) |
| Benin | BJ | none | L2: commune (77) |
| Bermuda | BM | complete | L1: parish (9) + L2: municipality (2) |
| Bhutan | BT | complete | L1: district (20) |
| Bolivia | BO | none | L2: province (112) |
| Bosnia and Herzegovina | BA | complete | L2: municipality (142) |
| Botswana | BW | none | L2: subdistrict (23) |
| Brazil | BR | complete | L2: district,municipality (5571) |
| Brunei | BN | complete | L2: mukim (39) |
| Bulgaria | BG | complete | L2: municipality (265) |
| Burkina Faso | BF | complete | L2: province (47) |
| Burundi | BI | none | L2: commune (42) |
| Cambodia | KH | complete | L2: district,municipality,section (210) |
| Cameroon | CM | none | L2: department (58) |
| Canada | CA | complete | L2: indigenous_reserve,municipality,unorganized (5028) |
| Cape Verde | CV | admin-ready | L2: parish (32); 2020 decree moved the full list to portal-only (codigopostal.cv, now dead + unarchived for data) — annex carries examples only; third parties still show the superseded pre-2020 4-digit system. B9 re-confirmed 2026-10-03: areas 56 verify-only (gate_cv ALL PASS); UPU POST*CODE Aug-2026 require-list format 9999/length 4; ARME July-2019 CPN `CCZZ-QQQ` doc Wayback-archived; the 32 annex examples deliberately NOT bundled (illustrative, would mislead); OSM x110/x600 values are the superseded pre-2019 system per the 04/2014 UPU compendium (7600 PRAIA) |
| Caribbean Netherlands | BQ | none | L1: special_municipality (3) |
| Cayman Islands | KY | none | box-only system (UPU: street address alone undeliverable, PO boxes only); codes pass through, nothing to import |
| Central African Republic | CF | none | L2: subprefecture (85) |
| Chad | TD | none | L2: department (63) |
| Chile | CL | complete | L2: province (56) |
| China | CN | complete | L2: autonomous_prefecture,league,prefecture,prefecture_city (333) |
| Colombia | CO | complete | L2: locality,municipality,non_municipalized_area (1141) |
| Comoros | KM | none | L2: prefecture (16) |
| Congo | CG | none | L2: district (92) |
| Costa Rica | CR | complete | L2: canton (84) |
| Croatia | HR | complete | L1: county (21) |
| Cuba | CU | complete | L2: municipality (168) |
| Cyprus | CY | complete | L2: locality (755) |
| Czech Republic | CZ | complete | L2: district (76) |
| DR Congo | CD | expansion | L2: territory (145) |
| Denmark | DK | complete | L2: municipality (98) |
| Djibouti | DJ | complete | L2: subprefecture (20) |
| Dominica | DM | none | L1: parish (10) |
| Dominican Republic | DO | complete | L2: district,province (32) + L3: municipality (158) |
| Ecuador | EC | complete | L2: canton (222) |
| Egypt | EG | expansion | L2: district (365) |
| El Salvador | SV | complete | L2: municipality (44) |
| Equatorial Guinea | GQ | none | L2: province (8) |
| Eritrea | ER | none | L2: subregion (58) |
| Estonia | EE | complete | L2: rural_municipality,urban_municipality (78) |
| Eswatini | SZ | complete | L1: region (4) |
| Ethiopia | ET | complete | L2: zone (54) |
| Faroe Islands | FO | complete | L2: municipality (29) |
| Fiji | FJ | none | L2: province (14) |
| Finland | FI | complete | L2: city,municipality (292) |
| France | FR | complete | L2: department (102) |
| French Guiana | GF | complete | L2: commune (22) |
| French Polynesia | PF | complete | L2: commune (48) |
| French Southern Territories | TF | none | L1: district (5) |
| Gabon | GA | none | L2: department (49) |
| Gambia | GM | none | L2: district (42) |
| Georgia | GE | complete | L2: city,district,municipality (85) |
| Germany | DE | complete | L2: rural_district,urban_district (401) |
| Ghana | GH | none | L2: district,metropolitan_city,municipality (261) |
| Greece | GR | complete | L2: municipality (332) |
| Greenland | GL | complete | L1: municipality (5) |
| Grenada | GD | none | L1: dependency,parish (7) |
| Guadeloupe | GP | complete | L2: commune (32) |
| Guam | GU | complete | L1: village (19) |
| Guatemala | GT | complete | L1: department (22) |
| Guernsey | GG | complete | L1: dependency,parish (12) |
| Guinea | GN | complete | L2: prefecture (33) |
| Guinea-Bissau | GW | complete | L2: sector (38) |
| Guyana | GY | admin-ready | L2: neighbourhood_democratic_council,town (75); live 7-digit system per UPU guyEn 08/2025 (e.g. Georgetown 4130106) but no code directory reachable — guypost.gy Cloudflare-walled, no GeoNames dump; overlay unbuilt, gate records the gap |
| Haiti | HT | complete | L2: arrondissement (42) |
| Honduras | HN | complete | L1: department (18) |
| Hong Kong | HK | none | L1: district (18) |
| Hungary | HU | complete | L2: district (197) |
| Iceland | IS | complete | L2: municipality (61) |
| India | IN | complete | L2: district (786) |
| Indonesia | ID | complete | L2: regency (514) |
| Iran | IR | complete | L1: province (30) |
| Iraq | IQ | complete | L1: governorate (19) |
| Ireland | IE | complete | L2: county (26) |
| Isle of Man | IM | complete | L2: district,parish,town,village (21) |
| Italy | IT | complete | L2: autonomous_province,decentralization_entity,free_municipal_consortium,metropolitan_city,province (109) |
| Ivory Coast | CI | none | L2: region (31) |
| Jamaica | JM | none | L1: parish (14) |
| Japan | JP | complete | L2: city,town,village,ward (1747) |
| Jersey | JE | complete | L1: parish (12) |
| Jordan | JO | complete | L2: liwa (51) |
| Kazakhstan | KZ | complete | L1: region (20) |
| Kenya | KE | complete | L2: constituency (290) |
| Kiribati | KI | complete | L2: council (24) |
| Kosovo | XK | complete | L2: municipality (38) |
| Kuwait | KW | expansion | L2: area (135) |
| Kyrgyzstan | KG | complete | L2: district (44) |
| Laos | LA | complete | L2: district (148) |
| Latvia | LV | complete | L1: municipality,state_city (42) |
| Lebanon | LB | complete | L2: caza (25) |
| Lesotho | LS | expansion | L2: constituency (80) |
| Liberia | LR | complete | L1: county (15) |
| Libya | LY | none | L2: baladiya (100) |
| Liechtenstein | LI | complete | L1: commune (11) |
| Lithuania | LT | complete | L2: city_municipality,district_municipality,municipality (60) |
| Luxembourg | LU | complete | L2: commune (100) |
| Madagascar | MG | complete | L3: district (114) |
| Malawi | MW | complete | L2: district (28) |
| Malaysia | MY | complete | L4: locality,subdistrict (292) |
| Maldives | MV | complete | L2: island (192) |
| Mali | ML | none | L2: cercle (159) |
| Malta | MT | complete | L1: local_council (68) |
| Marshall Islands | MH | complete | L2: municipality (24) |
| Martinique | MQ | complete | L2: commune (34) |
| Mauritania | MR | none | L2: department (63) |
| Mauritius | MU | complete | L2: city,town,village (142) |
| Mayotte | YT | complete | L1: commune (17) |
| Mexico | MX | complete | L2: borough,municipality (2479) |
| Micronesia | FM | complete | L2: city,municipality (75) |
| Moldova | MD | complete | L1: district,city (37) |
| Monaco | MC | complete | L1: quarter (17) |
| Mongolia | MN | complete | L1: province (21) + L2: duureg |
| Montenegro | ME | complete | L1: municipality (25) |
| Montserrat | MS | complete | L1: parish (3) |
| Morocco | MA | complete | L2: prefecture,province (75) |
| Mozambique | MZ | complete | L2: district (136) |
| Myanmar | MM | expansion | L2: district (126) |
| Namibia | NA | complete | L1: region (14) |
| Nauru | NR | complete | L1: district (14) |
| Nepal | NP | complete | L2: district (77) |
| Netherlands | NL | complete | L2: municipality (342) |
| New Caledonia | NC | complete | L2: commune (33) |
| New Zealand | NZ | complete | L2: city,council,district (67) + L3: locality (1229) |
| Nicaragua | NI | complete | L2: municipality (153) |
| Niger | NE | none | box-only system (UPU: deliveries to P.O. Boxes only); codes pass through, nothing to import |
| Nigeria | NG | complete | L1: state (37) |
| Niue | NU | complete | L1: village (14) |
| North Korea | KP | none | L2: district (179) |
| North Macedonia | MK | complete | L1: municipality (80) |
| Norway | NO | complete | L2: municipality (357) |
| Oman | OM | complete | L2: wilayat (63) |
| Pakistan | PK | complete | L2: district (178) |
| Palau | PW | complete | L1: state (16) |
| Palestine | PS | complete | L1: governorate (16) |
| Panama | PA | none | L2: district (81) |
| Papua New Guinea | PG | complete | L2: district (96) |
| Paraguay | PY | complete | L2: district (263) |
| Peru | PE | complete | L2: province (196) |
| Philippines | PH | complete | L1: province (82) |
| Poland | PL | complete | L2: city_county,land_county (380) |
| Portugal | PT | complete | L2: municipality (308) |
| Puerto Rico | PR | complete | L1: municipality (78) |
| Qatar | QA | none | L2: zone (90) |
| Reunion | RE | complete | L2: commune (24) |
| Romania | RO | complete | L1: department (41) |
| Russia | RU | complete | L1: autonomous_oblast,federal_city,krai,oblast,okrug,republic (83) |
| Rwanda | RW | none | L2: district (30) |
| Saint Barthelemy | BL | complete | L1: overseas_collectivity (1) |
| Saint Helena | SH | complete | L1: district,island (10) |
| Saint Kitts and Nevis | KN | complete | L2: parish (14) + L3: village (92) |
| Saint Lucia | LC | complete | L1: district (10) |
| Saint Martin | MF | complete | L1: overseas_collectivity (1) |
| Saint Pierre and Miquelon | PM | complete | L1: overseas_collectivity (1) |
| Saint Vincent and the Grenadines | VC | complete | L1: parish (6) |
| Samoa | WS | complete | L2: village (342) |
| San Marino | SM | complete | L1: municipality (9) |
| Sao Tome and Principe | ST | none | L1: autonomous_region,district (7) |
| Saudi Arabia | SA | complete | L1: region (13) |
| Senegal | SN | complete | L2: department (46) |
| Serbia | RS | complete | L2: city,city_municipality,municipality (161) |
| Seychelles | SC | none | L1: district (27) |
| Sierra Leone | SL | none | L2: district (16) |
| Singapore | SG | runtime | L2: planning_area,postal_sector (136) |
| Slovakia | SK | complete | L2: district (79) |
| Slovenia | SI | complete | L1: municipality,urban_municipality (212) |
| Solomon Islands | SB | none | L2: ward (183) |
| Somalia | SO | none | L2: district (89) |
| South Africa | ZA | complete | L2: city_municipality,district_municipality (52) |
| South Korea | KR | complete | L2: city,county,district (228) |
| South Sudan | SS | none | L2: county (84) |
| Spain | ES | complete | L2: province (50) |
| Sri Lanka | LK | complete | L2: district (25) |
| Sudan | SD | complete | L1: state (18) |
| Suriname | SR | none | L2: resort (63) |
| Sweden | SE | complete | L2: municipality (290) |
| Switzerland | CH | complete | L2: district (146) |
| Syria | SY | none | L2: district (66) |
| Taiwan | TW | complete | L2: county_administered_city,district,mountain_indigenous_district,mountain_indigenous_township,rural_township,urban_township (368) |
| Tajikistan | TJ | expansion | L2: city,district (69) |
| Tanzania | TZ | complete | L2: district (194) |
| Thailand | TH | complete | L2: amphoe,khet (928) |
| Timor-Leste | TL | admin-ready | L2: administrative_post (67) |
| Togo | TG | none | L2: prefecture (39) |
| Tonga | TO | none | L2: district (23) |
| Trinidad and Tobago | TT | expansion | L1: borough,city,region,ward (15) |
| Tunisia | TN | complete | L2: delegation (279) |
| Turkmenistan | TM | complete | L2: district (58) |
| Turks and Caicos | TC | complete | L1: district (6) |
| Tuvalu | TV | none | L1: island_council,town_council (8) |
| Türkiye | TR | complete | L2: district (973) |
| US Minor Outlying Islands | UM | none | L1: island (9) |
| US Virgin Islands | VI | complete | L1: district (3) |
| Uganda | UG | admin-ready | L2: city,district (146); 5-digit system adopted per UPU 1.2026 but no published allocation list — 2019 draft stale (22321 Bugalo draft vs Nabbingo adopted), E-Posta API auth-walled, no GeoNames dump |
| Ukraine | UA | complete | L2: raion (136) |
| United Arab Emirates | AE | none | L1: emirate (7) |
| United Kingdom | GB | complete | L2: council_area,county,county_borough,district (113) |
| United States | US | complete | L2: borough,census_area,city,county,municipality,parish,planning_region (3143) |
| Uruguay | UY | complete | L2: municipality (136) |
| Uzbekistan | UZ | complete | L2: city,tuman (206) |
| Vanuatu | VU | none | L2: area_council,municipality (67); B10: Penama East Ambae/North Maewo + Shefa South Epi + bare Whitesands + Torba 7 island councils (VNSO census + COD-AB over pre-2008 Statoids roster) |
| Venezuela | VE | complete | L1: capital_district,state (24) |
| Vietnam | VN | complete | L2: commune,special_zone,ward (3321) |
| Wallis and Futuna | WF | complete | L2: district (3) |
| Yemen | YE | none | L2: district (333) |
| Zambia | ZM | none | UPU profile: codes never assigned; NAPP incomplete (UNGEGN 2025); nothing to import |
| Zimbabwe | ZW | none | L2: district (64) |

## Queue scoping

`admin-ready` countries need only postcode→area compilation against
already-bundled rows. Done: Åland, Faroe Islands, Iceland, Saint
Lucia, Saint Vincent, Barbados, Kosovo, US Virgin Islands, Puerto
Rico, Moldova, Philippines, Maldives, Albania, Denmark, Guatemala,
Slovenia, Croatia, Mauritius, Norway, Luxembourg, Lithuania,
Latvia, Azerbaijan, Romania, Montenegro, North Macedonia, Bosnia
and Herzegovina, Eswatini, Brunei, Bhutan, Mongolia, Greece,
Vietnam, Saint Kitts and Nevis, Cyprus, Djibouti.
Still queued: the parked countries (see rules) and the
remaining `admin-ready` verdicts above.

`expansion` countries need doc-15 locality rows first. Street-level
systems additionally need sub-locality data or licensed files:
Guernsey/Jersey/Isle of Man (Royal Mail PAF, licensed),
Brazil (CEP), Argentina (CPA base level built; block-face
suffixes out of scope per Dataset rules), Mexico (colonias), Colombia
(6-digit), Netherlands (4+2), Sweden (PostNord), Canada
(FSA level built; LDU suffixes out of scope per Dataset rules),
United States (ZIP level built; cross-county secondaries need licensed USPS file), Japan (Japan Post open data), South Korea (5-digit level built), Australia (PAF licensed), Taiwan (district level built; 3+3 suffixes out of scope per Dataset rules), Portugal (street suffix), Palestine (locality areas), Russia
(subject level built; tier-2 raions pending a GAR extract — see
the Russia section in [country data](05-country-data.md)).
France can join Hexasmal
once its 35k communes are bundled; Great Britain via CodePoint
Open once post towns are modeled.

## Findings (not changed)

- Gabon: UPU lists no postcode system but the formatter prints
  supplied codes (pass-through). Verdict `none`; formatter behavior
  untouched. (Burkina Faso was removed from this bullet by the M3
  fill: the UPU BFA profile specifies a live 5-digit system and La
  Poste BF publishes the full directory — verdict `complete`.)
- Ghana: GhanaPost GPS/digital addresses are not postcodes.
  Verdict `none`; formatter passes supplied codes through.
- Panama: no national postcode system (Parcelforce agency codes are
  carrier-internal). Doc 05 already says so; verdict `none`.
- Malawi: UPU postcode-type table lists MW as N (no system);
  delivery is via P.O. boxes. (Removed by the B7 fill: the
  Government Gazette 2019 "Malawi Postcodes 2019" + MACRA +
  UPU MWI 01/2021 specify a live 6-digit system — 491 codes
  bundled, verdict `complete`.)
- Hong Kong 999077 and French Southern Territories codes are
  foreign-administered routing codes, not domestic systems.
- Tajikistan: 6-digit system live (UPU TJK sheet 09/2019: 735450
  GARM == operator Rasht header); Tajik Post index article
  (https://tajikpost.tj/ru/перечень-почтовых-индексов-таджикис/,
  sitemap lastmod 2025-09-16, Tajik edition identical, Wayback
  2026-06-13 identical) re-confirmed 2026-10-03 at 390 rows / 336
  codes but truncated mid-Khatlon: 22/69 L2 codeless (15 Khatlon
  districts + Levakant/Kulob cities + Istiqlol + all 4 Dushanbe
  districts), 753456 typo for 735456 in the Rasht block, 14
  cross-group shared codes (incl. the Hisor/Shahrinav/Tursunzoda
  735020-735026 tangle), Danghara/Norak/Yovon stale-grouped under
  RRP, legacy names Kurgan-Tyube=Bokhtar and Bokhtar
  district=Kushoniyon. Seat corroboration: ~29/47 headers reach 2+
  signals (Tajik Post + RU-wiki + OSM tie-breaks, incl. 3 RU
  refutations), 7 seats conflict with no third signal, all 336
  locality codes single-signal. No second directory (Mapanet
  paywalled $29/zero free rows, GeoNames 404, youbianku stub,
  worldpostalcode 404; operator city-indices article deleted, 404).
  Nothing shippable without misrepresenting coverage (Myanmar/Egypt
  fragment precedent). Still `expansion`; gap program in the M3
  verdict record.
- Israel has no geography provider yet; provider creation is
  separate work outside this overlay pass.
- Trinidad and Tobago: per-address 6-digit S-42 system (PP-RR-ZZ per
  UPU; 72 postal districts per Wikipedia) but no open code-level
  source (TTPost finder is email/WhatsApp-only, GeoNames has no TT
  dump, Mapanet 4 rows, Overpass unreachable). Still `expansion`.
- DR Congo: UPU addressing sheet codEn.pdf confirms a 7-digit
  postcode system (province/city/sector/office structure, e.g.
  1003071/1004131 Kinshasa, 3202011 Kwilu), so verdict is NOT
  `none`. No allocation source: GeoNames has no CD export (404),
  Mapanet CD rows carry empty codes. Still `expansion`.
- Timor-Leste: UPU sheet tlsEn.pdf (8/2026) proves a live TL+5-digit
  system (TL10001 Dili Central, TL42000 Ainaro), but no public
  allocation exists: Mapanet TL rows are codeless, GeoNames has no
  TL export, and the Correios TL site publishes only ministry org
  law. Still `expansion`.
