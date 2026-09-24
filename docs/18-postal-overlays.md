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
  (enforced by `PostalCodeCsvImportTest`).
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
  (Clipperton 98799, Greenland 3982/3984/3985), and private
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
  - Bahrain: overturned — SLRB states 478 Logical Map Boundaries
    following the administrative blocks, matching the shipped 479
    (the earlier ~1000+ was the number range, not the used count).
  - Guernsey/Jersey/Isle of Man: UK-style street-level codes
    (ONSPD enumerates them but carries no CI/IOM coordinates;
    Guernsey finder API works per full code but caps broader
    queries). Needs a polite per-code attribution batch (~9k
    finder queries) or PAF access.
  - Egypt: 5-digit office codes (first-two = governorate) but no
    open-data office list (no GeoNames dump; egyptpost.gov.eg
    Cloudflare-blocked; MapAnet EG thin at 371 rows / 81 codes;
    only spam-mirror district lists). Needs Egypt Post data or a
    full ~4k office-page crawl with URL discovery.
  - Trinidad and Tobago: 6-digit codes are assigned per address
    (building-level, TTPost + municipal addressing project) and
    no town↔code table is published; TTPost site has no finder
    and GeoNames publishes no TT postal export.
  - Lebanon: 4-digit zone system (2017, low adoption) has no
    published complete list (LibanPost site has no finder,
    GeoNames publishes no LB postal export, third parties
    carry fragments). Needs LibanPost data.
  - Samoa: only third-party compilations found and they misattribute
    districts (Manono WS1190 listed under A'ana). Needs the Samoa
    Post official list.
  - Kuwait: Mapanet pull (349 rows) is unattributable at L2 area
    level (Jahra 92 rows lumped under one locality with 000xx–040xx
    internal-style codes; Ahmadi only 9 rows; empty block suburbs);
    MOC postcode finder is JS-gated, PACI Kuwait Finder unreachable,
    GeoNames publishes no KW postal export. Needs MOC/PACI data.

  - Timor-Leste: TL+5 system is brand new (UPU profile 8/2026);
    CTL site unreachable and no published code list found yet.
    Revisit once Correios de Timor-Leste publishes the list.
  - Uganda: 5-digit system exists (UPU profile 1/2026) but no
    official allocation published; third-party lists conflict with
    UPU examples. Needs Posta Uganda data.
  - Cape Verde: CPN list lives only on www.codigopostal.cv (per
    Decreto-Regulamentar 5/2020), which is unreachable and whose
    archived copies have a broken database; GeoNames publishes no
    CV postal export and correios.cv has no finder. Revisit once
    the official finder is back online.
  - Guyana: 7-digit system confirmed by UPU profile (08/2025;
    district/region/sub-region/office/locality structure) but the
    allocation list lives only in guypost.gy's "Find Your
    Postcode" page, which is Cloudflare-walled live and captured
    in no web archive. Revisit once the list is reachable.
  - Venezuela: IPOSTEL site unreachable (live times out, no
    finder captured in archives) and GeoNames publishes no VE
    postal export. Only third-party compilations found. Needs
    IPOSTEL data.
  - Nicaragua: correos.gob.ni is Cloudflare-walled and no
    postcode pages are captured in archives; GeoNames publishes
    no NI postal export. Needs Correos de Nicaragua data.
  - Honduras: HONDUCOR publishes no postcode list and its search
    is ModSecurity-blocked; the GeoNames HN postal export covers
    only 38 major towns (not the 298 municipalities). Needs
    HONDUCOR data.
  - Palestine: PalCode assigns building-level P+7 codes and the
    town-zone endpoint returns empty, so no town↔sector table is
    published; reverse-geocoding 833 GeoNames points surfaced 435
    localities but 56 span up to 7 sectors with no dominant
    prefix, making any sampled town table provably incomplete.
    Gaza Strip has no PalCode coverage at all. Needs a PalCode
    town list or a working town-zone endpoint.
  - Trinidad and Tobago: 6-digit street/community codes exist but
    TTPost publishes no allocation list; lookup is a manual query
    form only. Needs a TTPost code list.
  - Lesotho: lesothopost.org.ls 403s, lps.org.ls is dead, UPU profile
    lists no codes, and GeoNames publishes no LS postal export;
    third-party lists conflict (Leribe 300 vs 9730). Needs Lesotho
    Post data.
  - Oman: the official Oman Post office locator (213 codes) omits
    known offices (Muttrah 125, Khoula 127, Seeb Airport 128) and
    conflicts with the established 2019-2024 numbering (Mutrah 169,
    Airport Heights 111); no authoritative current list found. Needs
    Oman Post data.
  - Bahrain: the open data portal publishes no postcode/block
    dataset (mailbox/PO-box stats only) and block-level codes need
    city/block modeling first. Needs a block list with governorate
    mapping.
  - Morocco: the GeoNames dump (1325 rows) has no Casablanca or
    Rabat city codes at all, so it cannot serve as the source.
    Needs Barid Al-Maghrib data.

## Built datasets

| Country | Code | Codes | Links | Source |
|---|---|---:|:---:|---|
| Afghanistan | AF | 1406 | 1406 | Afghan Post official finder GeoJSON /client_postal_code (1,408 six-digit zones PPDDZZ: province 10–43 + city 01–50/rural 51–99 + delivery zone; rural_dist/city_distr + post office + centroid per zone); rural zones join districts province-scoped + transliteration aliases, city zones join capital districts (Herat→Hirat, Mehtarlam, Matun, Taloqan, Maydan Shahr, Shiberghan, Maymana, Parun); 50 ambiguous centroids adjudicated by Nominatim reverse-geocode (Gershk/Marja/Babajee→Nahr-e-Saraj/Nad-e-Ali, Takhtapul→Spin Boldak, Dand→Kandahar, Lija Mangal→Lija Ahmad Khel, Pamir→Wakhan, Sheltan→Shigal, Gizab/Dishu have no COD-AB district); 6 province-tag-vs-centroid conflicts resolved by location (Nawa-i-Mesh→Kiti/Daykundi, Pusht-i-Koh→Anar Dara, Alfaroq/Allahyar→Qala-e-Naw, Chehelgazi→Ghormach, Want→Dara-e-Pech); Gizab 425201 + Dishu/Baramcha 396601 dropped as non-COD-AB districts |
| Åland | AX | 33 | 33 | Åland postcode register via Dörnbach enumeration + Postnord 2024 (AX-22100–AX-22950; 5 PO-box codes excluded); bare domestic input gains the AX- prefix at lookup |
| Albania | AL | 489 | 490 | GeoNames dump at municipality level (1001–9706; 1029 spans Kamëz/Tirana, Kamëz primary) |
| Algeria | DZ | 3908 | 3908 | Algérie Poste office codes via geoalgeria/baridimap (3,908 offices, 1:1 code↔office incl. 49–69 new wilayas; commune→daira via JO/frwiki; GN agrees on 2,918/3,162 codes; 119 renumbered-olds dropped as dead; 125 unverifiable GN-only dropped; Mapanet DZ codes discarded, 6/1045 office agreement — daira-constructed; BOD/Debdeb/Aïn Smara kept as communes per infoboxes+ONIL, mapped to In Amenas/El Khroub) |
| American Samoa | AS | 1 | 0 | UPU single-code list (96799); ZIP+4 strips to 5-digit base at lookup |
| Argentina | AR | 2501 | 3112 | Correo Argentino official finder backend wsFacade.php action=localidades (23,544 localities, cp+partido per province letter; 27 name aliases incl. Pueyrredón→La Capital-SL, J.F.Borges→Capital-SDE, Hucal→Huncal-areas-spelling) → code→partido majority primary (2,080 codes; 1,520 agree with GeoNames rollup, 108 decisive GN overruled, 69 ties single-primary-broken by GN-agreement else alphabetical); 109 GN-only codes kept (DPTO-hint/gazetteer/Photon+Nominatim state-constrained county map — unconstrained mapping caused Güemes-Chaco/San Antonio-Jujuy/Capital-Catamarca collisions, fixed); all 28 GN-unresolved + 18 GN-ties resolved officially; CABA 312 C-codes via Mapanet rows coord-clustered (largest 1.5km cluster) + Nominatim barrio→comuna, 15 official action=cpa street probes anchor/override (C1406/C1407/C1416→C10, C1405/C1414→C15, C1428/C1429→C13, C1440→C9, C1086→C1; C1137 C3-primary + C1-secondary, C1416 C10-primary + C11-secondary; C1223/C1480 single garbage rows dropped, official area probes contradict; C1163 lat sign-fixed); MapAnet interior final confirm (12,107 rows): 30-sample 14 agree, 5 conflicts all kept for built on decisive CA majorities (4313/4419/4504 minority-locality samples, 8373 Lácar 4v3), 4635 +Humahuaca secondary on 1v1 evidence tie, 6336 kept Catrilo (no Mapanet rows; GN letter L + 63xx range + proximity beat single OSM point-geocode); CPA base level only (interior 4-digit + CABA C+4-digit) — full 8-char block-face codes strip to base at lookup, suffixes out of scope |
| Armenia | AM | 779 | 779 | Haypost api.haypost.am postalIndex (official finder backend, 779 branch-index records with coords; city typos fixed via address village: Doxs→Doghs, Xursal→Ghursal, Mexri→Meghri); non-Yerevan via city→GeoNames new-municipality join + coord-nearest disambiguation (8 wiki/address manuals: Mkhchyan Artashat, Yerazgavors Akhuryan, Nor Kyank ×2 Vedi/Artik, Nerkin Sasnashen Talin, Nor Geghi Nor Hachn, Verin Karmiraghbyur Berd, 4206=4010 city); 20 far-match audits fixed 10 (Artashar/Armavir-village/Mrgashat/Vardanashen Metsamor, Khoronk Araks, Getashens Martuni, Teghut-Lori Alaverdi, Yeghegnut-Lori Pambak, Kasakh Nairi; Hoktember=Sardarapat→Armavir; 3014 Dubai-coord + 4112 Tavush-region Haypost data errors noted); 93 Yerevan offices district-mapped via Photon reverse-geocode (0074 Shengavit + 0010 Kentron anchors match Spyur/postalcode.pro; 0022 Avan via Avan-Arinj address); 132 Mapanet-only codes dropped as office-number artefacts (Spyur: office №7 postcode is 0074, not 0007; city sub-office + Vardenis-village extras unverified) while 20 Haypost-only kept (9 Yerevan gaps + 0102–0108 + Teghut/Abovyan/Vayk); ADDED am:municipality:khoy to areas (8th Armavir community per Armstat 2025, 15 codes) |
| Andorra | AD | 7 | 7 | UPU AND profile + parish list (AD100–AD700); bare domestic input gains the AD prefix at lookup |
| Anguilla | AI | 1 | 0 | UPU single-code list (AI-2640); bare domestic input gains the AI- prefix at lookup |
| Azerbaijan | AZ | 1186 | 1186 | GeoNames dump at district/city level (AZ 0100–AZ 8011; city branches split to city municipalities); bare/spaceless input gains the AZ prefix + space at lookup |
| Austria | AT | 2501 | 2622 | GeoNames dump at district level (1000–9992; 19k street rows; 116 cross-district codes dual-linked, row-majority primary, secondaries need 2+ rows; 30 ties broken by PLZ-district map, 4550 seat-rule Kirchdorf; 124 admin2-less codes manual: Vienna specials to Vienna state, 89 statutory-city/office codes OSM-verified incl. 2127 Mistelbach + 5089 Salzburg-Umgebung + 4985 Ried; 3 PLZ-map-only codes 8471/8565/9104 excluded unverified) |
| Australia | AU | 3165 | 4182 | ABS ASGS Ed3 allocation join Mesh Block→POA 2021 × →LGA 2021 (368k blocks; GeoNames admin2 proven unreliable: 3585 Swan Hill misfiled Gannawarra, 3691 Kiewa misfiled Albury, 0872 Alice misfiled Laverton, 4825 Mt Isa misfiled Carpentaria); MB-count majority primary, secondaries need 2+ MBs (180 single-MB slivers dropped); 527 LVR/PO-box codes via place→street-code ABS primary + hand-mapped mail centres (Hunter MC Newcastle, New England MC Tamworth, Mid North Coast MC Kempsey, Gippsland MC Morwell, Sydney Gateway Granville→Cumberland, AFPO Sydney, Dangar→Newcastle, Winnellie→Darwin, Navy Warships→Rockingham, Antarctic 7151→Kingborough); 9 MB-ties adjudicated (2178 Penrith infobox-order, 2808 Cowra village, 4497 Balonne Thallon, 6420 Narembeen Muntadgin, 2721 Bland, 3572 Loddon, 5150 Burnside, 5273 Kingston-SA, 5310 Loxton Waikerie); tri-state 0872 7 links (MacDonnell P + Central Desert + Barkly + Ngaanyatjarraku + East Pilbara + SA/NT L1s); 4825 Mt Isa P + Boulia + Cloncurry + Barkly (Alpurrurulam) + Burke; 2406 Moree Plains P + Balonne + Goondiwindi straddle exception; 3586/3500 single (Mallan=2734, Paringi=2738 per AusPost — GN rows + wiki stale); ACT→L1 (no LGAs); unincorporated SA/NSW/NT/Vic + APY/Maralinga→L1s; JBT + externals 6798/6799/2899 + pseudo-POAs 9494/9797 unlinked (no areas); 3989 junk + 1419/2058 unlocatable MCs dropped; AusPost finder has LVR index gaps (2052/5005/1001 false zeros, mirror-confirmed) so zeros never drop |
| Bahrain | BH | 479 | 479 | Block number = postcode per UPU BHR.pdf (3–4 digits, first-two = region, 100–1299): youbianku governorate block table (479 blocks, zero cross-governorate; A'ali genuinely split Northern 732–744 / Southern 746+748); MapAnet BH full pull (150 rows) confirms 58/58 overlapping blocks with zero disagreements (zero-padded 0101=101; x00 area bases 300/500/600/700/900/1000/1200 excluded as synthetic multi-town aggregates); count corroborated by SLRB (478 Logical Map Boundaries following block areas); no open-data block list on data.gov.bh (ODS portal has stats only) |
| Bangladesh | BD | 1349 | 1349 | GeoNames dump at district level (1000–9461; all 64 districts; old spellings mapped to post-2018 names; GPO anchors 1000/1100/4000/6000/9000 verified) |
| Barbados | BB | 1176 | 1185 | BPS finder JS dataset (BB11000–BB27193 incl. 15 office base codes; 9 BB23 splits dual-linked; BB190215 6-digit typo omitted); bare domestic input gains the BB prefix at lookup |
| Bermuda | BM | 80 | 105 | ipostalcode enumeration cross-checked to researched prefix→parish map (CR/DD/DV/FL/GE/HM/HS/MA/PG/SB/SN/WK street ranges; HM 01–20 dual City of Hamilton + Pembroke, GE 01–05 dual Town + St George's parish, DD 01–03 parish-only; box codes XX + PB prefix omitted); compact input gains the official space at lookup |
| Belarus | BY | 3123 | 3123 | GeoNames dump at district level (3,133 rows; French-transliteration admin2 fuzzy-joined oblast-scoped + 18 explicit aliases incl. Gantsevitchi→Hantsavichy, Borissov→Barysaw, Tolotchin→Talachyn; Minsk city admin1-04 + 9 city rayons → L1 Minsk city; 5 1v1 ties adjudicated by range/block evidence with losers dropped as GN row errors — 211227→Liozno, 211657→Polotsk, 220024→Minsk city, 222374→Miadel 2223xx-block, 222834→Pukhavichy; Oev = truncated Loyew 2471xx-block; empty-admin Berestovitsa 231778 by district-center place) |
| Belgium | BE | 1146 | 1146 | GeoNames dump at province level (1000–9992; zero cross-province codes; 12 seat codes verified 1000 Bruxelles/2000 Antwerpen/3000 Leuven/4000 Liège/5000 Namur/6000 Charleroi/7000 Mons/8000 Brugge/9000 Gent/1300 Wavre/3500 Hasselt/6700 Arlon; Brussels 22 codes to Brussels-Capital region) |
| Bhutan | BT | 38 | 38 | Bhutan Post legacy finder, all 20 dzongkhags queried (11001–46002; office base codes only; finder Gewog column holds office towns so links are district-level; Samdrupjongkhar/Trashiyangtse spelling variants) |
| Bosnia and Herzegovina | BA | 517 | 570 | BiH routing manual 2013 (7,363 settlements; 70101–89247; 55 cross-boundary codes dual-linked; 71335 Pržidi omitted) |
| Brunei | BN | 394 | 394 | post.gov.bn finder scrape (kampong→mukim; 23 Peti Surat PO-box + 87 ministry large-user rows excluded; Kampong Amo A/B/C unfetched; finder spells Burong Pinggai Ayer, Kampong Peramu = Peramu) |
| Brazil | BR | 5526 | 5526 | GeoNames general-CEP dump (5,525 XXXXX-000 codes) joined EXACT on IBGE 7-digit codes, zero name matching; 18 DF satellite codes → Brasília municipality; 12 admin2-less rows ViaCEP-adjudicated (Amapari→Pedra Branca 1600154, Livramento do Brumado→Livramento de Nossa Senhora 2919504, GN mislabels Assis Chateaubriand→Riachão do Bacamarte + São Bento de Pombal→São Bentinho + Mosquito→Palmeiras do Tocantins corrected; Jamari 78937→Itapuã do Oeste by seat coords); 76861-000 Itapuã added via ViaCEP (GN gap); 10-code ViaCEP spot-check 9 direct + Fernão 17455-000 confirmed via municipal letterhead (ViaCEP has proven -000 gaps: 01000-000 also silent); 65 municipalities codeless (GN snapshot gaps); street-level suffixes out of scope (need licensed DNE; no prefix-strip normalizer — metro prefixes can span municipalities) |
| Bulgaria | BG | 4363 | 4375 | CRC/Bulgarian Posts 2016 directory (5358 settlement rows) + GeoNames screened vote-by-vote (598 fiction rows + 354 stale codes dropped; Malko Tarnovo 8162–8174 superseded by post-2016 8350–8370 renumber per mapanet/WPC/estate ads; 12 cross-municipality codes dual-linked; Sarnitsa post-2015 split + Gurkovo/Ognen/Ravninata admin-truth overrides; Byala Ruse/Varna + Dobrich city/municipality disambiguated) |
| Canada | CA | 1651 | 1660 | GeoNames FSA dump (1,651 FSAs: urban→CSD via admin2/place join + 445-code alias table for amalgamated-city sectors — Toronto/Ottawa/Hamilton boroughs, Montréal/Laval/Longueuil/Gatineau/Québec/Lévis/Saguenay/Trois-Rivières/Shawinigan/Sherbrooke boroughs, CBRM/Halifax/Dartmouth/Bedford/Sackville, North/West Vancouver, Brantford; 186 rural X0X→province); 63 NB places resolved to 2023 entities via GNB Socrata ward polygons + full-gazetteer coords (Coverdale→Salisbury, Kingsclear→Hanwell, Youngs Cove→Arcadia, Smiths Creek→Butternut Valley, Apohaqui→Kings RD, Kingston→Fundy RD, Debec→Lakeland Ridges, Inkerman→Shippagan, Deer Island→Southwest RD, Baie-Sainte-Anne→Kent RD; E7C Saint-Basile→Edmundston per 1998 amalgamation over off-point) + Wikipedia reform extracts; 6 NS rural towns via StatCan 2021 CSD point-in-polygon (Iona/Big Bras d'Or→Victoria Subd. B, Loch Lomond/Fourchu→Richmond Subd. B, Coldbrook→Kings Subd. C, Christmas Island→Cape Breton); 9 dual links (E1H Beausoleil P + Maple Hills, E2E Rothesay P + Quispamsis, E2H Saint John P + Rothesay, E3C Fredericton P + New Maryland, R1B St. Clements P + St. Andrews, V7J/V7P North Van District P + City, J6Z Bois-des-Filion P + Lorraine, J7T Saint-Lazare P + Les Cèdres); audit fixes — West Island cities (Hampstead/Westmount/Côte-Saint-Luc/Montréal-Ouest/Kirkland/Dorval/Pointe-Claire/Beaconsfield) + North/West Vancouver + Brantford + Red Deer County rescued from metro admin2, Montréal neighbourhoods (Saint-Michel/Mercier/Saint-Henri/Saint-Pierre) rescued from distant same-name cities; LDU strips to FSA at lookup |
| Cambodia | KH | 1633 | 1633 | Postcode = NIS commune code: cambodiapostalcode.com 205 district pages (1601 commune codes, NIS-exact incl. Aoral 0504 + 120209 Phsar Chas UPU anchor) + NIS stat.go.jp docs for 5 CPC-missing districts (Basedth 050101–050115, Chum Kiri 070401–070407, Ou Reang 110301–110302) + ybk Srei Santhor-correct blocks for Chantrea 200101–200106 + Preah Vihear city 130801–130802; 22/23/24 use postcode numbering Kep/Pailin/OM (UPU compendium Pailin 230200 + areacambodia Kaeb 220201 + Pailin 230201–230204 ×3 sources; OM=24 by elimination) so CPC's NIS-numbered Kep/Pailin/OM blocks mechanically remapped (district parts kept: ybk's OM 04/05 swap + Kampong Speu shift + Srei Santhor 02xxxx fabrication + 030301 dup all rejected as ybk corruption; zipcode.com.ng shown to copy ybk); 060705 Kraya reassigned Ballangk→Santuk (misfiled page); UPU 141006 = stale NIS-2009 Prey Veaeng numbering (current city block 141001–141004); Ou Krasar moved Damnak Chang'aeur→Kaeb post-2009 (230103 gap preserved); Bokor 0709 separate from Chum Kiri; XX000 province-base codes excluded (zone routing, commune system complete) |
| Denmark | DK | 1159 | 1159 | GeoNames dump joined on municipal codes (0800–9990) |
| Djibouti | DJ | 10 | 10 | UPU DJI profile code table (77101–77601; city districts 77102–77105 + 5 region capitals at subprefecture level) |
| Dominican Republic | DO | 528 | 530 | INPOSDOM codigo-postal data.json (1403 sector rows; 10100–94100 + Moca 53xxx + Santiago 58081 overflow; 2 junk rows excluded; DN 10100–10699 links L2; 71100 Pueblo Viejo/Guayabal + 81100 Cabral/Jaquimeyes dual-linked, largest primary) |
| Ecuador | EC | 1225 | 1225 | GeoNames dump at canton level (010101–900004; all 222 cantons; undelimited-zone codes to absorbing cantons: El Piedrero El Triunfo, Manga del Cura El Carmen, Las Golondrinas Cotacachi) |
| Eswatini | SZ | 80 | 80 | Eswatini Post postcode page, region-grouped (H100–L317; H103 shared Eveni/Swazi Plaza, Swazi Plaza primary) |
| El Salvador | SV | 262 | 262 | 262 district codes × 44 post-reform municipalities (gist/youbianku cross-checked, groupings verified against reform annex; upstream typos fixed via third sources: Candelaria 1302→1402, Alegría 3404→3402, Cacaopera 3216→3203, Chilanga 3203→3205 per mapanet/UPU compendium) |
| Estonia | EE | 5397 | 5415 | GeoNames dump joined on municipality names (5293 codes; Toila rows → Jõhvi post Nov-2025 merger) + 8 GeoNames-missing cities via mapanet/WPC town sets with business-register spot checks (112 town codes); 17 boundary codes dual-linked, primary = majority rows else town side |
| Finland | FI | 3576 | 3576 | GeoNames dump joined on admin3 municipality codes (00002–99999; Swedish/Finnish bilingual names mapped; Pertunmaa→Mäntyharju, Valtimo→Nurmes, Honkajoki→Kankaanpää post-merger mapping) |
| Faroe Islands | FO | 118 | 119 | Posta code tables via da/fo wiki (FO-100–FO-970; 12 postsmoga excluded; FO-485 dual-linked); bare domestic input gains the FO- prefix at lookup |
| French Guiana | GF | 25 | 25 | La Poste Hexasmal (Sep 2026) |
| French Polynesia | PF | 83 | 93 | La Poste Hexasmal (Sep 2026); shared: 98732 Huahine, 98735 Uturoa, 98790 Rangiroa, 98796 Nuku-Hiva |
| Greenland | GL | 27 | 27 | Post Greenland + postcode lists (town→municipality mapping) |
| Croatia | HR | 1094 | 1094 | Hrvatska pošta live finder scrape (10000–53534; customs-only 10004 + 36 retired codes excluded) |
| Chile | CL | 346 | 346 | GeoNames dump 1:1 at province level (346 rows, zero cross-province codes, all 56 provinces; 2018 Ñuble split handled via 21-commune map to Diguillín/Itata/Punilla; Aisén→Aysén, Ranco→El Ranco, trailing-PROVINCE aliases) |
| Cuba | CU | 785 | 788 | Correos de Cuba office-search API full pull (841 offices; 6 zero-code HQ rows excluded; 16 office-municipio aliases incl. Buenaventura→Calixto García, La Maya→Songo-La Maya, Cuatro Caminos→Najasa; San Luis split Pinar/Santiago by province; 3 shared codes dual-linked, majority primary) |
| Czechia | CZ | 2694 | 2723 | GeoNames dump at district level (100 00–798 62; 15k street rows; 29 cross-district codes dual-linked, row-majority primary, secondaries need 2+ rows; 384 01 Chlístovice row is a GeoNames error — real 284 01 — so Prachatice-only; ties 507 91 Jičín (Stará Paka office) + 544 43 Trutnov (Kuks office) + 569 94 Svitavy (Pošta Telecí branch) per OSM/firmy.cz; Prague codes to capital city); spaceless input gains the official space at lookup |
| Colombia | CO | 3676 | 3676 | GeoNames dump 1:1 at municipality level (3,681 rows, zero cross-municipality codes; department-scoped exact/containment/fuzzy join + 11 overrides incl. Tumaco, Buga, Los Robles La Paz, Providencia Islands, Sincé, Tolú, El Carmen-Santander, Togüí-Boyacá; containment traps Tolú Viejo→Toluviejo + Palmas del Socorro→Palmas Socorro overridden; Mapiripana→Barrancominas by Nominatim centroid; Bogotá 81 codes → L1 capital district; San Jacinto del Cauca 134060/134067/134068 + Santa Bárbara de Pinto 474001/474007 dropped as real municipalities missing from areas) |
| Costa Rica | CR | 492 | 492 | Ministerio de Salud official 492-district DIVTER list (= postal codes per UPU province+canton+district structure; 10101–70605); GeoNames 473-row dump used only for canton-prefix map (Valverde Vega→Sarchí 2019 rename + León Cortés variant mapped); 17 post-GN districts (Jaris/Quitirrisí/La Amistad/San Lorenzo/Labrador/Canalete/Birrisito/La Victoria/Puente Salas/Cabeceras/Matambú/Caldera/Bahía Drake/Gutiérrez Brown/Lagunillas/La Colonia/Reventazón) + 5 new-canton codes (Río Cuarto 21601–21603, Monteverde 61201, Puerto Jiménez 61301) added; superseded 20306/60109/60702 excluded |
| Cyprus | CY | 1125 | 1127 | GeoNames postal dump at locality level (1000–9999; 1036 Nicosia quarters + 4528 Akti Kyverniti/Pentakomo dual-linked, first-listed primary) |
| Guadeloupe | GP | 33 | 33 | La Poste Hexasmal (Sep 2026) |
| Guam | GU | 21 | 21 | USPS village ZIPs (Chalan Pago-Ordot has no asserted code); ZIP+4 strips to 5-digit base at lookup |
| Guatemala | GT | 548 | 548 | GeoNames dump at department level (01001–22220) |
| Guinea-Bissau | GW | 52 | 62 | Mapanet full pull (150 locality rows with coords) + OSM sector reverse-geocode per row (1160–9300; Bissau 12 codes to autonomous sector; Bolama single 9300 triple Uno-primary + Bubaque + Caravela; 5000 triple Bafatá-primary + Galomaro + Gamamundo; 3200/3300/3600/6400/8300/8400 dual seat-primary; Bigene/Catió/Komo/Bolama sectors codeless in source) |
| Guinea | GN | 58 | 60 | Mapanet full pull (544 locality rows) filed per prefecture + OSM prefecture reverse-geocode per code (001–460; UPU radical structure: digit-1 = natural region, Conakry 001 + Kindia 100 + Labé 200 examples match; 430 dual Kissidougou-primary + Kérouané; 232 dual Mali-primary + Koubia for Matakaou village; Guékédou spelling variant mapped) |
| Haiti | HT | 229 | 229 | GeoNames dump at locality level (HT1110–HT9310) joined via UPU 42-district prefix list (digit-1 = department, digits-1–2 = arrondissement; 75 split 3rd-digit 752 Baradères / 751+753+754 Anse-à-Veau; HT3408 excluded — prefix 34 has no UPU district, GeoNames-only); bare domestic input gains the HT prefix at lookup |
| Greece | GR | 974 | 984 | ELTA street register (72k rows, 497 localities) joined via GeoNames dimos codes (10442–85800; 10 cross-municipality codes dual-linked, street-majority primary; 2019 split dimos mapped to current municipalities; Athos codes to Mount Athos region; bundled Agioi Deka corrected to Gortyna); spaced input strips to bundled spaceless form at lookup |
| Germany | DE | 10812 | 10910 | GeoNames dump at district level (01067–99998; 19k street rows, state-scoped place join + local aliases incl. Calvörde→Calvoerde, Helgoland→Heligoland, Brühl→Bruehl, Hürth→Huerth, Straubing-Bogen surplus + 26 dispatched pairs; 98 cross-district codes dual-linked, within-state row-majority primary incl. 88348 matched via Bad Waldsee row; 99986 Recklinghausen comment-row tie + 12529 Dahme-Spreewald dual confirmed via places-in-germany.com; 13 junk codes excluded incl. Kreuzburg 838→1038, merged-Bissendorf 3000→21220, Weiden West 92640, Wilhelmshaven EC 46457/50735/51200/82396/83308/95326, Trier WC 52348, Hamburg-Pinneberg 22113/15834/20331) |
| Georgia | GE | 74 | 83 | Gpost.ge finder sweep of 4,167 GN villages (rural Georgia uses one code per municipality: 1800 = all Dusheti, 5700 = all Khashuri; 3,305 matched, strict + spaceless name-match incl. ზემო ოქროყანა = ზემოოქროყანა; 862 unmatched — 603 with no Gpost entry + 259 noise-buried, mostly occupied-territory villages; Azhara-Upper Abkhazia codeless, no Gpost service; 426 homonym villages vote their own card's code so code attribution stays clean; Tbilisi 8 village-attached codes → L1 city, pure-urban street codes out of scope; 7300 Tskhinvali-region 5 links Akhalgori P + Java + Eredvi + Kurta + Tighva; Akhalgori town filed under ცხინვალი region) |
| Hungary | HU | 3045 | 3065 | Magyar Posta + KSH gazetteer joined dataset IrszHnk (Feb 2026; 3569 settlement rows; 1007–9985); districts via KSH seat names + Budapest kerület map (Hegyháti→Hegyhát the only non-seat name); 20 cross-district codes dual-linked, population-majority primary (7814 Siklósi 356 vs Pécsi 352 + 9375 Soproni 258 vs Kapuvári 241 closest) |
| Iceland | IS | 174 | 178 | Pósturinn register via is.wiki (101–900; 20 PO-box/special omitted; 512 omitted, see rules; 4 neighbour-served communes dual-linked) |
| Ireland | IE | 139 | 141 | Eircode routing keys: GeoNames dump (139 keys with towns) cross-checked to Wikipedia routing-area table (136 keys; A94 = Blackrock Dublin confirmed via property records; A82/A92 dual Meath+Cavan / Louth+Meath, first-listed primary; K67/T12/T23 GN-only); full 7-char eircodes strip to 3-char routing key at lookup |
| Kenya | KE | 949 | 949 | PCK office directory (951 codes; office towns × GeoNames admin1, 877 exact joins, rest via block/neighbour evidence; 00127 Ruaraka + 08010 typos fixed against PCK codes 00618/80100) |
| Jordan | JO | 351 | 352 | Mapanet 354 rows → GeoNames fuzzy (GeoNames JO PPLs carry no admin2) → Photon reverse-geocode qada (328/354; Nominatim only 92/321, GeoNames admin2 empty) × DoS 2015 census liwa↔qada table + ar.wiki liwa pages (decisive: Udhruh→Ma'an Qasabah not Petra, Orjan→Ajloun Qasabah, Rajm Shami→Mowaqqar, Umm Rasas→Jizah, Hisban/Umm Basatin→Naour, Mujib→Qasr, Irhab→Mafraq Qasabah, Hawsha→Badiah Gharbiyah, Umm Jamal/Deir Kahf/Salhiyah→Badiah Shamaliyah, Hawwara→Irbid Qasabah, Waqqas→Aghwar Shamaliyah despite OSM Janoobiyah label); 85/88 OSM cross-check agree (3 border adjudications: 71221 Ajloun, 71228 Jerash via Burmah, 11152 Quaismeh via Wehdat camp); overrides: 71910 Shobak (719xx), 61258 Sahab (r1=16 Industrial City), 64710 Hasa (coords+revgeo beat r1 misfile), 25710 Mafraq Qasabah (DoS Bal'ama; r1+geoname garbage); 11121 dual Wadi Essier+Jami'ah, 11190 Amman Qasabah (Al Abdali 0.947 beats Qaser Al Adel 0.522), 11134 Marka agreed; JO codes do not block by governorate (61256 Quaismeh) |
| Kyrgyzstan | KG | 893 | 893 | Mapanet full pull (891 locality rows, 49 district/city leaves) + RU-wiki Toktogul town codes 721600/721615/721616 (720000–725032; 13 seat codes cross-checked: state-archives Naryn 722900 + Ak-Talaa 722500, Nominatim Tash-Kumyr 721400 + Balykchy 721900, RU-wiki Bishkek/Osh/Karakol/Jalal-Abad/Batken/Özgön/Kyzyl-Kiya/Mailuu-Suu; Tokmok 724200 kept over stale Soviet 722000 now live at Kyzylsuu; Naryn 722600 likewise stale; enclave cities to districts, Balykchy/Tash-Kumyr/Kara-Köl to regions; Toguz-Toro codeless in source) |
| Kiribati | KI | 25 | 25 | MICTTD official 37-code table via archive (KI0101–KI0303; 12 uninhabited-island codes excluded); bare domestic input gains the KI prefix at lookup |
| Kosovo | XK | 127 | 127 | Posta e Kosovës regional lists via archive.org (10000–73000; 10020 transit centre excluded; post-split offices mapped to current municipalities; Parteš/Ranilug/North Mitrovica have no office code) |
| Laos | LA | 25 | 148 | 56ok district pages (102 districts incl. 9 Vientiane prefecture codes 01000–01170 with Sangthong 01080 + Pakngum 01090) + zone lists + UPU parcel compendium snippets confirming 02000–08000 blocks + postalcoder village checks (Bokeo 05000, Phongsaly 02000); province codes shared by all districts, capital-district primary (Sekong capital La Mam; Champasak 16010 per 56ok + hotel majority, 16000 excluded unverified; Xaisomboun shares 10000; office sub-codes like 13030/11010 noted but unmapped) |
| Latvia | LV | 697 | 719 | GeoNames dump at novads level (LV-1001–LV-5752; city-edge splits dual-linked; stale Varakļāni mapped to Rēzekne); bare domestic input gains the LV- prefix at lookup |
| Liechtenstein | LI | 13 | 13 | Swiss Post PLZ list (9489 omitted, see rules) |
| Lithuania | LT | 2023 | 2068 | GeoNames dump at municipality level (00001–99069; 3 cross-county stray links dropped; same-county splits dual-linked) |
| Luxembourg | LU | 4330 | 4407 | GeoNames dump at commune level (L-1111–L-9999; pre-2018 communes mapped to merged names; border street codes dual-linked); bare domestic input gains the L- prefix at lookup |
| Madagascar | MG | 110 | 114 | 114 districts modelled (INSTAT/Wikipedia list; French↔Malagasy name variants mapped; Maroantsetra→Ambatosoa post-2021 regions); mapanet district pages (103) + youbianku Urban codes (101/110/201/301/401/501/601) + wiki-infobox new-split codes (Mandoto/Isandra/Lalangina/Vohibato share 113/314/303/305, dual-linked established-primary) |
| Malaysia | MY | 3025 | 3696 | Pos Malaysia (existing dataset) |
| Malta | MT | 27823 | 27823 | MaltaPost postcode finder API exhaustive sweep (89 towns incl. 3 CBD + Comino; street codes ATM 2000–ZBK 5100; sub-localities to parent councils: Kappara San Ġwann, Gwardamanġa Pietà, Baħrija Rabat; Victoria/Rabat disambiguated); compact input gains the official space at lookup |
| Maldives | MV | 199 | 202 | Postcodebase + 56ok cross-validated island lists (00010–23000; resort/uninhabited codes + Malé/Villingili street ranges excluded; 05020 dual-linked) |
| Marshall Islands | MH | 2 | 2 | USPS (96960 Majuro, 96970 Ebeye; outer atolls route via hubs); ZIP+4 strips to 5-digit base at lookup |
| Mauritius | MU | 1990 | 1990 | Mauritius Post finder exhaustive scrape (11101–91710 + 182 Rodrigues R-codes at dependency + 4 Agalega A-codes) |
| Martinique | MQ | 30 | 35 | La Poste Hexasmal (Sep 2026); shared: 97218 Basse-Pointe, 97222 Bellefontaine, 97250 Saint-Pierre |
| Mayotte | YT | 11 | 18 | La Poste Hexasmal (Sep 2026); shared primaries: Mamoudzou, Dzaoudzi, Chirongui, Mtsamboro, Bandraboua, Dembeni, Ouangani |
| Micronesia | FM | 4 | 4 | USPS via FSM government (96941 Pohnpei, 96942 Chuuk, 96943 Yap, 96944 Kosrae); ZIP+4 strips to 5-digit base at lookup |
| Moldova | MD | 1214 | 1220 | GeoNames dump at district/city level (MD-2000–MD-7843; MD-5219 omitted on district conflict; shared-code primary = majority-village district); bare domestic input gains the MD- prefix at lookup |
| Montenegro | ME | 149 | 149 | Pošta CG branch network via API (81000–85530; 80000 service code + retired 81122 excluded; 85333→Tivat) |
| Monaco | MC | 1 | 0 | UPU MCO profile (98000 delivery; 01–99 are delivery-type) |
| Mongolia | MN | 39 | 39 | Mongol Post branch directory (21 aimag posts + UB horoo branches; EasyBox lockers carry no codes; 3 codeless service branches omitted; per-sum codes not published) |
| Montserrat | MS | 8 | 8 | Government of Montserrat postcode pamphlet; bare domestic input gains the MSR prefix at lookup |
| Nauru | NR | 1 | 0 | UPU single-code list (NRU68) |
| Netherlands | NL | 4071 | 4091 | 4-digit prefixes (IE routing-key precedent; full 6-char needs licensed PostNL file): GeoNames dump joined on CBS gemeentecode + Voorne aan Zee 2023-merger fix (20 codeless rows), every prefix verified live in PDOK/BAG LocatieServer (15 phantoms dropped: 1099/1234/1940/1954/3160/4200/4857/5100/5217/5442/6060/6817/7146/7379/7940, zero in adres + postcode indexes); 55 GeoNames multis adjudicated by BAG address counts (19 pop-pick flips incl. 2324 Leiden, 3981 Bunnik, 8611 Gaastmeer, 1431 Aalsmeer, 2761 Zevenhuizen, 4043 Opheusden, 1906 Limmen, 1755 Petten, 1616 Hoogkarspel, 3633 Vreeland, 5091 Middelbeers, 8465/8466 De Fryske Marren, 9354 Zevenhuizen-Gr, 7233 Vierakker, 6574 Berg en Dal, 3651 Nieuwkoop, 3366 Molenlanden, 1695 Hoorn; 9617/9623 Midden-Groningen by gemeentecode after token-match tie; 38 zero-address sides dropped, 20 genuine cross-municipality duals kept incl. 9999 Het Hogeland single after 4 big-city postbus-artefact drops); full NNNN LL strips to 4-digit prefix at lookup |
| New Caledonia | NC | 50 | 50 | La Poste Hexasmal (Sep 2026) |
| New Zealand | NZ | 1737 | 1738 | GeoNames postal dump at locality level (0110–9893; 1081 Ostend/Surfdale dual-linked, Ostend primary; 58 places resolved via coords + council/NZ Post maps, Waioruarangi to Kaikōura on 7300 delivery) |
| Nepal | NP | 753 | 753 | 2025 federal palika system (digit-1 = province; old 1991 district/office codes superseded): postcodenepal.com 77 district pages (748 palika codes, all districts sequential XX01–XX0N single-block; Kaski/Pokhara dup page deduped; prabat-URL page = Parbat; 18 slug spelling variants mapped; nawalparasi→Nawalpur east) + Khotang 10501–10510 derived (only free Koshi slot 101–114, 10 palikas per district page, district-level only); anchors: gov local-level PDF (40504 Pokhara/40710 Aabukhaireni/10707 Chaubise/11101 Mechinagar all match) + nepalish KMC 30608 + ward-pattern 30608-01–32; GPO site unreachable |
| North Macedonia | MK | 326 | 326 | Makedonska Pošta 2016 unit list + settlement directory (1000–7550; 1137 uncertain commune + retired/stale codes excluded) |
| Norway | NO | 5136 | 5136 | GeoNames dump joined on municipal codes + Posten.no Svalbard/Jan Mayen codes (0001–9991; PO-only status per code unverified) |
| Niue | NU | 1 | 0 | UPU single-code list (9974) |
| Palau | PW | 2 | 16 | USPS bulletin (96939 Ngerulmud/Melekeok, 96940 rest, Koror primary); ZIP+4 strips to 5-digit base at lookup |
| Pakistan | PK | 3114 | 3121 | Pakistan Post office directory (3130 delivery offices; GPO service areas × GeoNames admin2 × post-split crosswalk; 7 NPO code collisions dual-linked incl. 07529 Dumba Goth/Gulistan-e-Jauhar; Quetta East/West by railway line; 12 districts with no table office: Allai, Darel, Haveli, Kolai-Palas, Lower South Waziristan, Mohmand, Rondu, Shigar, Sohbatpur, Surab, Upper Dera Bugti, Wadh) |
| Peru | PE | 2669 | 2671 | GeoNames dump at province level (01000–25701; all 196 admin2 join clean; 2 cross-province codes dual-linked: 14000 Chiclayo-primary + Lambayeque, 14013 Lambayeque-primary + Chiclayo) |
| Philippines | PH | 1933 | 1933 | GeoNames dump at province level (1000–9811; 258 facility/PO-box codes excluded; NCR linked at region) |
| Poland | PL | 20299 | 20642 | Poczta Polska SPNA official file at powiat level (00-002–99-742; 332 multi-powiat codes dual-linked); attribution verified against independent MapAnet crawl (34,770 locality rows, 17,917 codes) via gazetteer TERYT: multis 21 both-sides + 174 one-side confirmed, 136 Mapanet-silent; singles spot-sample 150: 127 agree, 0 contradicted, 23 silent; sole contra 14-120 adjudicated script artifact (single-row Jankowice gazetteer tie 2808/2803, both range-absurd for 14-1xx Ostróda area — staged ostródzki P + Olsztyn s stands); dashless input gains the official dash at lookup |
| Portugal | PT | 197772 | 197772 | GeoNames dump at municipality level (206,942 street rows → 197,772 distinct 7-digit codes 1000-001–9980-999, code set exact; zero cross-municipality codes so 1:1 links; all 308 municipalities covered, none codeless; spelling-variant joins Lisboa→Lisbon 9165 + Vila da Praia da Vitória→Praia da Vitória 397) |
| Puerto Rico | PR | 177 | 177 | GeoNames USPS ZIP dump joined on municipio FIPS (00601–00962; PO-only status per ZIP unverified); ZIP+4 strips to 5-digit base at lookup |
| Réunion | RE | 37 | 37 | La Poste Hexasmal (Sep 2026) |
| Russia | RU | 43531 | 43531 | GeoNames 6-digit dump (43,538 rows → 43,531 codes at federal-subject L1; tier-2 raions stay parked per doc 05); stale GN names remapped (Chita Oblast → Zabaykalsky, Kamchatka Oblast → Kamchatka Krai); 5 okrug/subject gaps rescued by prefix+place (626/628→Khanty-Mansi, 629→Yamalo-Nenets, 166→Nenets, 679→Jewish AO, 689→Chukotka); Baikonur 468xxx dropped (Kazakhstan); 15 off-profile singletons verified as special-purpose (Moscow postal facilities, 901xxx mail-van codes kept at origin region); 3-digit prefix coherence clean otherwise |
| Romania | RO | 37914 | 37914 | GeoNames dump at department level (010011–927250; street codes linked to county) |
| Senegal | SN | 163 | 193 | La Poste commune table via gist mirror (4158 quartiers, 161 codes) + postcodebase same-upstream commune confirm + OSM/GeoNames department per commune (562 mapped; Keur Massar 2021 split applied; Palmarin/Dionewar/Paoskoto misfiles corrected; Nghoye/Same Kanta/Paoskoto-TB unattested but code-neutral) + La Poste official 10200 Dakar RP + 16500 Thiaroye (22300 Touba already in table; UPU 10000/27000 examples match; junk 0/159/36/41 dropped; 28 cross-department codes quartier-majority primary) |
| Saint Helena | SH | 3 | 10 | UPU single-code list (STHL/ASCN/TDCU 1ZZ; Jamestown primary for STHL); compact input gains the official space at lookup |
| Saint Kitts and Nevis | KN | 32 | 39 | post.kn zone/district PDF (KN0101–KN1202 + KN7000 SEP; 7 cross-parish codes dual-linked; Nevis 08–12 named by parish; village→parish per parish articles, Lodge to Christ Church); bare domestic input gains the KN prefix at lookup |
| Saint Lucia | LC | 47 | 48 | Government of Saint Lucia postcode table (LC01 101–LC18 101; 7 private-box codes excluded; Marisule dual-linked); compact input gains the official space at lookup |
| Saint Pierre and Miquelon | PM | 1 | 1 | UPU addressing (97500 both communes) |
| Saint Vincent and the Grenadines | VC | 56 | 56 | SVG Postal Corp official list (VC0110–VC0472; VC0100 box-only + VC0292 disputed Mesopotamia omitted); bare domestic input gains the VC prefix at lookup |
| Saint-Barthélemy | BL | 1 | 1 | UPU addressing (97133) |
| Saint-Martin | MF | 1 | 1 | UPU addressing (97150) |
| San Marino | SM | 10 | 10 | UPU SMR profile (47890–47899; Serravalle holds 47891+47899) |
| Serbia | RS | 1334 | 1407 | Mapanet municipality pages (145 munis, 4281 locality rows; Belgrade at city-municipality level) + 104 GN-only town/village codes (muni inherited from mapanet locality, 32 via Nominatim with Đurđevo→Žabalj + Kaluđerske Bare→Bajina Bašta fixes) + 100 courier-list Belgrade branch codes (generic at city); Kosovo r1 rows excluded (Posta e Kosovës system, XK overlaid); Niš/Užice/Požarevac/Vranje link district (cities unbundled); 68 shared codes dual-linked, majority-rows primary |
| Slovakia | SK | 3480 | 3514 | GeoNames dump at district level (010 01–992 01; office-number rows resolved via town→district from street rows, Rajec→Žilina; Bratislava blanks via 2nd-digit district rule anchored on street rows + verified 851 01 Petržalka-V / 841 04 Karlova Ves-IV via orsr.sk + Wikipedia street list; Košice-city 329 office codes at region (intra-city office→district needs Slovak Post branch data, CZ-Prague precedent); 33 cross-district dual-linked, majority primary with prefix/post-office tiebreaks incl. 906 35 Malacky (pop 741 + both-village Wikipedia infoboxes), 985 42 Lučenec (pošta Veľké Dravce per citypopulation), 985 45 Detva (985 45 = Látky), 094 06 Vranov / 916 13+916 16 NMnV / 930 28 DS / 976 81 Brezno / 980 33 RS / 985 22 Poltár on prefix, 067 82 Snina, 040 16 KE-II) |
| Slovenia | SI | 468 | 469 | Pošta Slovenije official list Aug-2025 via archive (1000–9503; 76 PO-box/large-user/internal excluded; 3231 Grobelno dual-linked Šentjur primary) |
| South Africa | ZA | 3266 | 3273 | GeoNames 3920 rows: municipal-boundary PIP + gazetteer placemun agreed 3536; Nominatim 425-place batch (filtered to settlement-class hits after 16 road-hits incl. Tongaat-Stellenbosch-road poison) + 3-digit prefix blocks adjudicated 29 place disputes incl. Middelburg-MP/EC pc-split (105x Nkangala vs 5900 Chris Hani), Richmond-NC 7090 Pixley, Greytown global Umvoti fix, Tokoza/Thokoza EKU, Lady Frere Chris Hani, Kranskop uMzinyathi; junk-province rows fixed to pc-area; ~70 anomalous pc/place pairs fixed to pc-area (Brits-0189, Krugersdorp-1928/1933, Umtata-4740 etc.); 7 genuine boundary duals (Hammanskraal TSH/Bojanala, JHB/EKU, Marble Hall/Siyabuswa); Nebo/Driekop/1060-1064 Sekhukhune enclaves kept |
| South Korea | KR | 34249 | 34249 | GeoNames 5-digit dump joined on si/gun/gu via Revised Romanization + 29 phonetic-assimilation exceptions (Pyeongtaek/Buk/Jungnang/Gangneung/Jongno/Mokpo/Chilgok etc.); general-gu collapsed to parent si (Suwon/Seongnam/Goyang/Yongin/Changwon/Cheongju/Cheonan/Jeonju/Pohang/Ansan/Anyang); Sejong 142 codes linked at city (no L2); no cross-area codes; 06076 Gangnam/03056 Jongno/63001 Jeju-Chuja match youbianku + Korea Post example |
| Spain | ES | 11150 | 11172 | GeoNames dump joined on province plate code (01001–52080; all rows pass INE 2-digit prefix check except 70 genuine cross-border deliveries; Ceuta 8 + Melilla 9 codes linked at autonomous city with ME→ML plate fix; 22 cross-province codes dual-linked incl. Treviño enclave 01118/01211/01427, majority primary with prefix-home tiebreak for 13110 Ciudad Real / 22584 Huesca / 44591 Teruel; orphan-prefix 26127 Montenegro de Cameros confirmed by SEUR carrier list + 14449 La Garganta by 5 sources) |
| Sri Lanka | LK | 2121 | 2121 | Dept. of Posts Post Code Directory 2022 at district level (00100–91559; Colombo 01–15 zones incl. 10 from scanned p.iii table; APR+AR both Ampara) |
| Sudan | SD | 90 | 96 | Mapanet state leaves (326 locality rows, 16/18 states; 5-digit codes 11111–63314; r1→state via capital anchors: Khartoum, Kassala, Al Qadarif, Singa/Sinjah + Dinder, El Obeid + Bara + Er Rahad, Ad Damazin, Ad Douiem + Kawa, Argo, Buram, Kutum, Berber-area; 6 cross-state codes dual-linked with row-majority primaries: 21115 Jazirah, 25514 Blue Nile, 31116 Gedaref, 51111/51113/52221 North Kordofan; Central + East Darfur codeless in source) |
| Sweden | SE | 18887 | 18887 | GeoNames 5-digit dump (18,887 codes, 1 row each, spaced print); locality→municipality join via gazetteer admin2 kommun codes + county filter with alternate names; 84% rows carry direct postal admin2 — cross-validated join (15,003 agree), 717 disagreements arbitrated by learned 3-digit prefix profiles (558 auto) + Nominatim/OSM coords + place evidence (159: postal noise like Västerhaninge→Nynäshamn, Alunda→Uppsala, Malmköping→Strängnäs overruled; gazetteer same-name errors like Vega→Huddinge, Enebyberg→Täby, Olofstorp→Tidaholm, Torsby→Hagfors overruled); 271-place manual alias table (archipelago islands, typos: Fasta→Farsta, Bällinge→Bälinge); zero cross-municipality codes; spaceless input gains official space at lookup |
| Switzerland | CH | 3177 | 3222 | GeoNames dump at district level (1000–9658; every code verified present in swisstopo Amtliches Ortschaftenverzeichnis; 185 PO-box/firm/city-base codes excluded incl. 3000/4000/6000/8000/9000/1200 + firm rows Uznach Vögele/Pizolpark/numbered branches; 13 Liechtenstein 94xx codes correctly absent; district-less cantons GE/BS/GR/UR/OW/NW/GL/ZG linked at canton; Raron split via municipality per vs.ch (3916–3949 Westlich, 3982–3994 Östlich); AI lump split via municipality with Rüte→Schwende-Rüte; Luzern city+land merged; 44 cross-area codes dual-linked (9050 triple, Appenzell primary) with swisstopo-municipality + citypopulation.de-population primaries; Saint-George confirmed Nyon post-2008) |
| Taiwan | TW | 365 | 368 | Chunghwa Post 3-digit district table (PDF decoded via ToUnicode CMap, 368 rows) × twzipcode-data transcription (0 conflicts; district suffixes + Taoyuan city upgrade reconciled); 300 Hsinchu triple-linked North primary (city hall in North per city health-bureau doc + OSM polygon), 600 Chiayi dual-linked East primary (city hall in East per OSM); 817/819/290 island codes excluded (no bundled area, uninhabited/disputed); street-level 3+3 suffixes out of scope at L2 |
| Tanzania | TZ | 4034 | 4034 | TCRA postcode API full pull (85,110 locations; urban/rural + split-district wards resolved via council ward lists: Madaba from Songea, Tunduma from Momba, Mpimbwe/Nsimbo; Kibiti + Tanganyika wards unmapped — no bundled district; Mpanda rural + Mpimbwe have no API wards) |
| Thailand | TH | 771 | 901 | GeoNames 5-digit dump (903 rows → 771 codes); transliteration-heavy join verified province-by-province incl. 18 stale/wrong-admin1 manual fixes (Bueng Kan 2011 split from Nong Khai, GN Phetchaburi/bun confusion → Phetchabun, Lamai Beach → Ko Samui), Bang Sai split by geocode (13190→1413, 13270→1404 per infobox); 110 shared-office codes dual-linked with pop-majority primaries (citypopulation 2010; 4 near-ties <10% defer to Thailand-Post-derived Parcelforce office label: 10250 Prawet, 10510 Khlong Sam Wa, 10700 Bangkok Noi, 22160 Na Yai Am); 1 cross-province code (41220 Nong Wua So P + Erawan, Loei); PDF office label matches a GN member on all 110 |
| Tunisia | TN | 795 | 806 | Mapanet 4,072 locality rows (1000–9183) → r2≡OSM codegeo (213/213) → OSM L5 name:fr → delegation (170 exact + 26 transliteration fuzzies + 17 manuals: Soukra→La Soukra, Ghezaz→Ghezèze, Jendouba Sud→Jendouba seat, Oum El Araies→Moularès alias per Mapcarta + delegation-list substitution, Gabès Ville→Médina center with Ouest/Sud on own r2s); old→new gov code map (Nabeul 15→21 etc.); Mapanet coords page-rounded garbage in Grand Tunis (Mnihla/Douar Hicher/Borj Louzir stamped Mornag-area points) so filing+names rule and the 795-code Nominatim sweep was discarded except 8111 (Ben Bechir truly Sud, Jendouba single); 9 multi-r2 codes dual/triple-linked (1002 Khadra-triple, 2035 Soukra, 2052 Carthage, 3200 Sud-primary on seat 10v10 tie + Smâr, 4100 Sud, 7029 Nord, 7050 Menzel Bourguiba, 8014 Béni Khalled-primary alphabetical 1v1, 8100 Jendouba); 6061 Chatt Essalem stays Médina via Ville filing (Mapanet point 40km off, Gabès filing otherwise delegation-clean); Dkhilet Toujane/Oudhref/Habib Thameur post-codegeo splits codeless |
| Turks and Caicos | TC | 1 | 0 | UPU single-code list (TKCA 1ZZ); compact input gains the official space at lookup |
| Turkmenistan | TM | 49 | 73 | Mapanet 267 locality rows (49 distinct 6-digit codes, ~1 per district); Nominatim reverse zoom-10 for etrap attribution (all 267 hit); city/etrap pairs split by suffix (şäheri→plain id as city, etraby→prefixed id as etrap, documented assumption); Hazar→Balkanabat city and Garabogaz→Türkmenbaşy etrap verified via Nominatim hierarchy; 744000 Ashgabat-general quad-linked to 4 boroughs (Berkararlyk primary, central seat); 745220 Büzmeýin+Arkadag; 4-district 745160 reduced to Magtymguly seat (cross-region Bäherden rows = Mapanet noise); town-row singles dropped (Karabekaul, Khodzhambas, Tejen-Altyn), village border singles dual-linked (19 duals + 2 triples) |
| Türkiye | TR | 2896 | 2903 | GeoNames dump at district level (01000–81950; all rows pass province plate-prefix check; central-district = Merkez + Mersin(İçel) alias; renames Kazan→Kahramankazan, Eyüp→Eyüpsultan, Aydınlar→Tillo, Ondokuzmayıs→19 Mayıs; Ereğli/Karadenizereğli + Doğubeyazit→Doğubayazıt + Çağliyancerit→Çağlayancerit spellings; 57 KKTC 99xxx codes excluded; 7 cross-district codes dual-linked, majority primary except 16270 Osmangazi on contiguous 160xx–162xx range + 44000 Yeşilyurt on higher coord accuracy with worldpostalcode confirming both sides; Çankaya/Konak/Malatya seat codes match worldpostalcode) |
| Ukraine | UA | 26579 | 26674 | GeoNames 29571 rows joined to HDX COD-AB v05 KATOTTG settlements (29.7k admin4, name match within oblast; 22369 exact + fuzzy/aggr; GN coords 17% placeholder/wrong incl. oblast-level batches e.g. all-47xxx stamped Pidhaitsi coords, so names primary, coords only name-confirmed: pip4 3276, pip2c 1096, OSM settlement nodes 181, Nominatim 8, hromada/council 24, old-raion priors 1411, uk.wikipedia infobox+coords + postcode-neighbor tiebreaks for 38 residuals incl. Vatutine/Novomoskovsk/Katerynopil-class 2023-25 renames; 7 far-mismatch exacts fixed to neighbor raion: Stanyshivka→Vyshhorod, Makariv-08738→Obukhiv, Oleksiivka-37411→Lubny, Druzhba-town→Shostka, Rakovo→Tiachiv, Vynohradne→Kalmiuske, Yurkivtsi-30217→Shepetivka; 95 same-oblast boundary codes dual-linked, majority primary; no Crimea/Sevastopol rows in GN dump) |
| United States | US | 40977 | 40977 | GeoNames USPS ZIP dump joined on county FIPS (00501–99950; DC 277 ZIPs linked at district; 11 stale-CT-county rows remapped to planning regions via CT OPM town crosswalk incl. Mansfield/Willington to Capitol; Yakutat 99689 to borough 02282; 96860/96863 kept on Honolulu over FPO dupes; 509 military APO/FPO/DPO ZIPs + 2 MH ZIPs excluded; cross-county secondaries need licensed USPS city file; 8-ZIP Nominatim spot-check incl. 99553/90210/10001/20001/60601/96860 matches) |
| US Virgin Islands | VI | 16 | 16 | GeoNames USPS ZIP dump, island-attributed (00801–00851 at district level; subdistrict split + PO-only status need licensed USPS city file); ZIP+4 strips to 5-digit base at lookup |
| Uruguay | UY | 124 | 339 | Correo Uruguayo listadoCP (124 codes, 117 dept/locality rows) joined to 125 municipalities via INAADS/Intendencia polygons + INE localidades + mapanet/WPC with decree-backed town fixes (Rincón/Estación Rincón, Minas de Corrales to Rivera; 35200 Cerro Chato dual-linked Florida-dept + TT Cerro Chato municipio) |
| Uzbekistan | UZ | 2140 | 2140 | Mapanet full crawl (205 leaves, 0 gaps after retries; 2137 codes 100000–230912 + 3 verified city mains: 140100 Samarqand + 190100 Termiz via my.gov.uz state portal, 230100 Nukus via regulation.gov.uz postal doc; all regions carry exactly one 2-digit prefix, Tashkent city 162 codes at L1 with own coding per UPU); zones mapped to tuman/city via district-center tables (Wikipedia region pages + statoids) with transliteration + splits: Kuyganyor→Andijon, Boʻz→Boʻston, Oqoltin→Ulugʻnor, Oqtosh→Narpay, Farhod→Xovos, Dehqonobod→Guliston-t, Paxtaobod→Sardoba-t (dual zones), Sayhun→Sayxunobod, Qarluq≈Korlik→Oltinsoy, Uxum→Forish, Muruntau→Tomdi (mine in Tamdy per Wikipedia), Kizil-tog→Angren city (part of city per mapcarta/OSM), Quvasoy villages→city (city includes rural communities per Wikipedia); Ingichka→Kattaqoʻrgʻon-t + Kogon/Xiva center rows split to cities; Karakuduk 120708 dropped (lone prefix anomaly in Navoiy); 72 L2 uncovered (12 Tashkent-city tumans by design; gaps: 8 cities incl. Shahrisabz/Ohangaron/Yangiyol/Xonobod/Shirin/Nurafshon/Gozgon/Zarafshon + 52 tumans incl. all of Fargʻona-t/Soʻx/Rishton/Oltiariq/Toshloq/Uchkoʻprik/Yozyovon/Oʻzbekiston/Buvayda, Termiz-t/Muzrabot/Shoʻrchi/Uzun, Urgut/Toyloq/Payariq/Paxtachi, Yakkabogʻ/Shahrisabz-t/Nishon/Mirishkor/Kokdala, Mehnatobod/Oqoltin-Si, Zomin/Zarbdor/Zafarobod, Paxtaobod/Shahrixon/Xoʻjaobod/Jalaquduq/Izboskan-An, Toʻrtkoʻl/Xoʻjayli/Taxtakoʻpir/Shumanay/Bozatov/Taxiatosh, Norin/Yangiqoʻrgʻon, Peshku/Qorovulbozor, Bekobod/Boʻstonliq/Parkent/Piskent/Qibray/Toshkent/Yangiyoʻl/Yuqorichirchiq/Oʻrtachirchiq, Yangiariq/Yangibozor/Xiva-t); UPU anchors 100000/100123/220605 present |
| Vietnam | VN | 3320 | 3320 | MOST 2025 national postcode list (94pp; X./P./Đặc khu wards incl. 13 special zones; Tam Dương Bắc cell reads 152213, taken as 15221 sequential; Nghi Dương post-dates the list, omitted) |
| Wallis and Futuna | WF | 3 | 3 | La Poste Hexasmal (Sep 2026) |

Import any dataset with the generic source (no per-country seeder):

```php
use AIArmada\Addressing\Actions\ImportPostalCodesAction;
use AIArmada\Addressing\Support\CsvPostalCodeSource;

$source = new CsvPostalCodeSource(
    countryCode: 'SM',
    codesPath: resource_path('geography/san-marino-postal-codes.csv'),
    linksPath: resource_path('geography/san-marino-postal-code-areas.csv'),
    areaSource: 'aiarmada_addressing_sanmarino_v1',
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
| Argentina | AR | complete | L2: commune,department,partido (527) |
| Armenia | AM | complete | L2: district,municipality (82) |
| Aruba | AW | none | L1: capital_city,region (9) |
| Australia | AU | complete | L2: borough,city,council,municipality,region,rural_city,shire,town (537) |
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
| Burkina Faso | BF | none | L2: province (47) |
| Burundi | BI | none | L2: commune (42) |
| Cambodia | KH | complete | L2: district,municipality,section (210) |
| Cameroon | CM | none | L2: department (58) |
| Canada | CA | complete | L2: indigenous_reserve,municipality,unorganized (5028) |
| Cape Verde | CV | admin-ready | L2: parish (32); 2020 decree moved the full list to portal-only (codigopostal.cv, now dead + unarchived for data) — annex carries 33 examples only; third parties still show the superseded pre-2020 4-digit system |
| Caribbean Netherlands | BQ | none | L1: special_municipality (3) |
| Cayman Islands | KY | none | box-only system (UPU: street address alone undeliverable, PO boxes only); codes pass through, nothing to import |
| Central African Republic | CF | none | L2: subprefecture (80) |
| Chad | TD | none | L2: department (63) |
| Chile | CL | complete | L2: province (56) |
| China | CN | expansion | L2: autonomous_prefecture,league,prefecture,prefecture_city (333) |
| Colombia | CO | complete | L2: locality,municipality,non_municipalized_area (1140) |
| Comoros | KM | none | L2: prefecture (16) |
| Congo | CG | none | L2: district (89) |
| Costa Rica | CR | complete | L2: canton (84) |
| Croatia | HR | complete | L1: county (21) |
| Cuba | CU | complete | L2: municipality (168) |
| Cyprus | CY | complete | L2: locality (752) |
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
| Ethiopia | ET | expansion | L2: woreda,zone (127) |
| Faroe Islands | FO | complete | L2: municipality (29) |
| Fiji | FJ | none | L2: province (14) |
| Finland | FI | complete | L2: city,municipality (292) |
| France | FR | expansion | L2: department (102) |
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
| Guernsey | GG | expansion | L1: dependency,parish (12) |
| Guinea | GN | complete | L2: prefecture (33) |
| Guinea-Bissau | GW | complete | L2: sector (38) |
| Guyana | GY | admin-ready | L2: neighbourhood_democratic_council,town (76) |
| Haiti | HT | complete | L2: arrondissement (42) |
| Honduras | HN | admin-ready | L2: municipality (298) |
| Hong Kong | HK | none | L1: district (18) |
| Hungary | HU | complete | L2: district (197) |
| Iceland | IS | complete | L2: municipality (61) |
| India | IN | expansion | L2: district (786) |
| Indonesia | ID | expansion | L3: district (7285) |
| Iran | IR | expansion | L2: county (429) |
| Iraq | IQ | expansion | L2: district (119) |
| Ireland | IE | complete | L2: county (26) |
| Isle of Man | IM | expansion | L2: district,parish,town,village (21) |
| Italy | IT | expansion | L2: autonomous_province,decentralization_entity,free_municipal_consortium,metropolitan_city,province (109) |
| Ivory Coast | CI | none | L2: region (31) |
| Jamaica | JM | none | L1: parish (14) |
| Japan | JP | expansion | L2: city,town,village,ward (1747) |
| Jersey | JE | expansion | L2: canton,cueillette,vingtaine (56) |
| Jordan | JO | complete | L2: liwa (51) |
| Kazakhstan | KZ | expansion | L2: district (170) |
| Kenya | KE | complete | L2: constituency (290) |
| Kiribati | KI | complete | L2: council (24) |
| Kosovo | XK | complete | L2: municipality (38) |
| Kuwait | KW | expansion | L2: area (135) |
| Kyrgyzstan | KG | complete | L2: district (44) |
| Laos | LA | complete | L2: district (148) |
| Latvia | LV | complete | L1: municipality,state_city (42) |
| Lebanon | LB | expansion | L2: caza (25) |
| Lesotho | LS | expansion | L2: constituency (80) |
| Liberia | LR | expansion | L2: district (127) |
| Libya | LY | none | L2: baladiya (100) |
| Liechtenstein | LI | complete | L1: commune (11) |
| Lithuania | LT | complete | L2: city_municipality,district_municipality,municipality (60) |
| Luxembourg | LU | complete | L2: commune (100) |
| Madagascar | MG | complete | L3: district (114) |
| Malawi | MW | none | L2: district (28) |
| Malaysia | MY | complete | L4: locality,subdistrict (292) |
| Maldives | MV | complete | L2: island (192) |
| Mali | ML | none | L2: cercle (159) |
| Malta | MT | complete | L1: local_council (68) |
| Marshall Islands | MH | complete | L2: municipality (24) |
| Martinique | MQ | complete | L2: commune (34) |
| Mauritania | MR | none | L2: department (63) |
| Mauritius | MU | complete | L2: city,town,village (142) |
| Mayotte | YT | complete | L1: commune (17) |
| Mexico | MX | expansion | L2: borough,municipality (2479) |
| Micronesia | FM | complete | L2: city,municipality (75) |
| Moldova | MD | complete | L1: district,city (37) |
| Monaco | MC | complete | L1: quarter (17) |
| Mongolia | MN | complete | L1: province (21) + L2: duureg |
| Montenegro | ME | complete | L1: municipality (25) |
| Montserrat | MS | complete | L1: parish (4) |
| Morocco | MA | expansion | L2: prefecture,province (75) |
| Mozambique | MZ | expansion | L2: district (136) |
| Myanmar | MM | expansion | L2: district (80) |
| Namibia | NA | expansion | L2: constituency (121) |
| Nauru | NR | complete | L1: district (14) |
| Nepal | NP | complete | L2: district (77) |
| Netherlands | NL | complete | L2: municipality (342) |
| New Caledonia | NC | complete | L2: commune (33) |
| New Zealand | NZ | complete | L2: city,council,district (67) + L3: locality (1201) |
| Nicaragua | NI | admin-ready | L2: municipality (153) |
| Niger | NE | none | box-only system (UPU: deliveries to P.O. Boxes only); codes pass through, nothing to import |
| Nigeria | NG | expansion | L2: area_council,lga (774) |
| Niue | NU | complete | L1: village (14) |
| North Korea | KP | none | L2: district (179) |
| North Macedonia | MK | complete | L1: municipality (80) |
| Norway | NO | complete | L2: municipality (357) |
| Oman | OM | expansion | L2: wilayat (63) |
| Pakistan | PK | complete | L2: district (178) |
| Palau | PW | complete | L1: state (16) |
| Palestine | PS | expansion | L1: governorate (16) |
| Panama | PA | none | L2: district (81) |
| Papua New Guinea | PG | expansion | L2: district (96) |
| Paraguay | PY | expansion | L2: district (263) |
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
| Samoa | WS | admin-ready | L2: village (342) |
| San Marino | SM | complete | L1: municipality (9) |
| Sao Tome and Principe | ST | none | L1: autonomous_region,district (7) |
| Saudi Arabia | SA | expansion | L2: governorate (139) |
| Senegal | SN | complete | L2: department (46) |
| Serbia | RS | complete | L2: city,city_municipality,municipality (157) |
| Seychelles | SC | none | L1: district (27) |
| Sierra Leone | SL | none | L2: district (16) |
| Singapore | SG | runtime | L2: planning_area,postal_sector (136) |
| Slovakia | SK | complete | L2: district (79) |
| Slovenia | SI | complete | L1: municipality,urban_municipality (212) |
| Solomon Islands | SB | none | L2: ward (183) |
| Somalia | SO | none | L2: district (89) |
| South Africa | ZA | complete | L2: city_municipality,district_municipality (52) |
| South Korea | KR | complete | L2: city,county,district (228) |
| South Sudan | SS | none | L2: county (88) |
| Spain | ES | complete | L2: province (50) |
| Sri Lanka | LK | complete | L2: district (25) |
| Sudan | SD | complete | L1: state (18) |
| Suriname | SR | none | L2: resort (63) |
| Sweden | SE | complete | L2: municipality (290) |
| Switzerland | CH | complete | L2: district (146) |
| Syria | SY | none | L2: district (66) |
| Taiwan | TW | complete | L2: county_administered_city,district,mountain_indigenous_district,mountain_indigenous_township,rural_township,urban_township (368) |
| Tajikistan | TJ | expansion | L2: city,district (69) |
| Tanzania | TZ | complete | L2: district (193) |
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
| United Kingdom | GB | expansion | L2: council_area,county,county_borough,district (113) |
| United States | US | complete | L2: borough,census_area,city,county,municipality,parish,planning_region (3143) |
| Uruguay | UY | complete | L2: municipality (125) |
| Uzbekistan | UZ | complete | L2: city,tuman (206) |
| Vanuatu | VU | none | L2: area_council,municipality (63) |
| Venezuela | VE | admin-ready | L2: municipality (335) |
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
United States (ZIP level built; cross-county secondaries need licensed USPS file), Japan (Japan Post open data), South Korea (5-digit level built), Australia (PAF licensed), Taiwan (district level built; 3+3 suffixes out of scope per Dataset rules), Portugal (street suffix), Palestine, Russia
(subject level built; tier-2 raions pending a GAR extract — see
the Russia section in [country data](05-country-data.md)).
France can join Hexasmal
once its 35k communes are bundled; Great Britain via CodePoint
Open once post towns are modeled.

## Findings (not changed)

- Burkina Faso and Gabon: UPU lists no postcode system but the
  formatters print supplied codes (pass-through). Verdict `none`;
  formatter behavior untouched.
- Ghana: GhanaPost GPS/digital addresses are not postcodes.
  Verdict `none`; formatter passes supplied codes through.
- Panama: no national postcode system (Parcelforce agency codes are
  carrier-internal). Doc 05 already says so; verdict `none`.
- Malawi: UPU postcode-type table lists MW as N (no system);
  delivery is via P.O. boxes. Verdict `none`.
- Hong Kong 999077 and French Southern Territories codes are
  foreign-administered routing codes, not domestic systems.
- Tajikistan: 6-digit system exists but no open district-level
  source found (GeoNames missing, Mapanet prefix-only 734/735/736,
  Tajik Post unreachable, scrapers empty). Still `expansion`.
  Verdict `none`.
- Israel has no geography provider yet; provider creation is
  separate work outside this overlay pass.
- Trinidad and Tobago: per-address 6-digit S-42 system (PP-RR-ZZ per
  UPU; 72 postal districts per Wikipedia) but no open code-level
  source (TTPost finder is email/WhatsApp-only, GeoNames has no TT
  dump, Mapanet 4 rows, Overpass unreachable). Still `expansion`.
