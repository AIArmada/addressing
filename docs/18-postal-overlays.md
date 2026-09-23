---
title: Postal overlays
---

# Postal overlays

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
  - Estonia: GeoNames dump misses 8 towns (Narva, Viljandi, Rakvere,
    Võru, Maardu, Keila, Sillamäe, Loksa) and uses pre-2019 EHAK
    codes for Kiili/Saku (bundled 0305/0719 are current). Needs
    Omniva or ADS data. Bundled areas also miss Toila vald.
  - Samoa: only third-party compilations found and they misattribute
    districts (Manono WS1190 listed under A'ana). Needs the Samoa
    Post official list.
  - Bulgaria: GeoNames admin2 attribution has homonym collisions
    (41 cross-district code conflicts). Needs Bulgarian Posts data.
  - Finland: GeoNames admin2 is sub-region, not municipality; code
    overlap causes false joins. Needs Posti data.
  - Uruguay: GeoNames prefixes scatter across departments and round
    codes attach to wrong localities. Needs Correo Uruguayo data.
  - Serbia: GeoNames dump carries no admin columns at all. Needs
    Pošta Srbije data or full locality mapping.
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
  - Cuba: correos.cu has no postcode finder and its office-search
    widget ships no loader (stuck on "Cargando..."); GeoNames
    publishes no CU postal export. Only third-party compilations
    found. Needs Correos de Cuba data.
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
  - El Salvador: correos.gob.sv publishes no postcode list
    (offices page and downloads checked); GeoNames publishes no
    SV postal export. Needs Correos de El Salvador data.
  - Honduras: HONDUCOR publishes no postcode list and its search
    is ModSecurity-blocked; the GeoNames HN postal export covers
    only 38 major towns (not the 298 municipalities). Needs
    HONDUCOR data.

## Built datasets

| Country | Code | Codes | Links | Source |
|---|---|---:|:---:|---|
| Åland | AX | 33 | 33 | Åland postcode register via Dörnbach enumeration + Postnord 2024 (AX-22100–AX-22950; 5 PO-box codes excluded) |
| Albania | AL | 489 | 490 | GeoNames dump at municipality level (1001–9706; 1029 spans Kamëz/Tirana, Kamëz primary) |
| American Samoa | AS | 1 | 0 | UPU single-code list (96799) |
| Andorra | AD | 7 | 7 | UPU AND profile + parish list (AD100–AD700) |
| Anguilla | AI | 1 | 0 | UPU single-code list (AI-2640) |
| Azerbaijan | AZ | 1186 | 1186 | GeoNames dump at district/city level (AZ 0100–AZ 8011; city branches split to city municipalities) |
| Barbados | BB | 1176 | 1185 | BPS finder JS dataset (BB11000–BB27193 incl. 15 office base codes; 9 BB23 splits dual-linked; BB190215 6-digit typo omitted) |
| Bhutan | BT | 38 | 38 | Bhutan Post legacy finder, all 20 dzongkhags queried (11001–46002; office base codes only; finder Gewog column holds office towns so links are district-level; Samdrupjongkhar/Trashiyangtse spelling variants) |
| Bosnia and Herzegovina | BA | 517 | 570 | BiH routing manual 2013 (7,363 settlements; 70101–89247; 55 cross-boundary codes dual-linked; 71335 Pržidi omitted) |
| Brunei | BN | 394 | 394 | post.gov.bn finder scrape (kampong→mukim; 23 Peti Surat PO-box + 87 ministry large-user rows excluded; Kampong Amo A/B/C unfetched; finder spells Burong Pinggai Ayer, Kampong Peramu = Peramu) |
| Denmark | DK | 1159 | 1159 | GeoNames dump joined on municipal codes (0800–9990) |
| Eswatini | SZ | 80 | 80 | Eswatini Post postcode page, region-grouped (H100–L317; H103 shared Eveni/Swazi Plaza, Swazi Plaza primary) |
| Faroe Islands | FO | 118 | 119 | Posta code tables via da/fo wiki (FO-100–FO-970; 12 postsmoga excluded; FO-485 dual-linked) |
| French Guiana | GF | 25 | 25 | La Poste Hexasmal (Sep 2026) |
| French Polynesia | PF | 83 | 93 | La Poste Hexasmal (Sep 2026); shared: 98732 Huahine, 98735 Uturoa, 98790 Rangiroa, 98796 Nuku-Hiva |
| Greenland | GL | 27 | 27 | Post Greenland + postcode lists (town→municipality mapping) |
| Croatia | HR | 1094 | 1094 | Hrvatska pošta live finder scrape (10000–53534; customs-only 10004 + 36 retired codes excluded) |
| Guadeloupe | GP | 33 | 33 | La Poste Hexasmal (Sep 2026) |
| Guam | GU | 21 | 21 | USPS village ZIPs (Chalan Pago-Ordot has no asserted code) |
| Guatemala | GT | 548 | 548 | GeoNames dump at department level (01001–22220) |
| Greece | GR | 974 | 984 | ELTA street register (72k rows, 497 localities) joined via GeoNames dimos codes (10442–85800; 10 cross-municipality codes dual-linked, street-majority primary; 2019 split dimos mapped to current municipalities; Athos codes to Mount Athos region; bundled Agioi Deka corrected to Gortyna) |
| Iceland | IS | 174 | 178 | Pósturinn register via is.wiki (101–900; 20 PO-box/special omitted; 512 omitted, see rules; 4 neighbour-served communes dual-linked) |
| Kiribati | KI | 25 | 25 | MICTTD official 37-code table via archive (KI0101–KI0303; 12 uninhabited-island codes excluded) |
| Kosovo | XK | 127 | 127 | Posta e Kosovës regional lists via archive.org (10000–73000; 10020 transit centre excluded; post-split offices mapped to current municipalities; Parteš/Ranilug/North Mitrovica have no office code) |
| Latvia | LV | 697 | 719 | GeoNames dump at novads level (LV-1001–LV-5752; city-edge splits dual-linked; stale Varakļāni mapped to Rēzekne) |
| Liechtenstein | LI | 13 | 13 | Swiss Post PLZ list (9489 omitted, see rules) |
| Lithuania | LT | 2023 | 2068 | GeoNames dump at municipality level (00001–99069; 3 cross-county stray links dropped; same-county splits dual-linked) |
| Luxembourg | LU | 4330 | 4407 | GeoNames dump at commune level (L-1111–L-9999; pre-2018 communes mapped to merged names; border street codes dual-linked) |
| Malaysia | MY | 3025 | 3696 | Pos Malaysia (existing dataset) |
| Maldives | MV | 199 | 202 | Postcodebase + 56ok cross-validated island lists (00010–23000; resort/uninhabited codes + Malé/Villingili street ranges excluded; 05020 dual-linked) |
| Marshall Islands | MH | 2 | 2 | USPS (96960 Majuro, 96970 Ebeye; outer atolls route via hubs) |
| Mauritius | MU | 1990 | 1990 | Mauritius Post finder exhaustive scrape (11101–91710 + 182 Rodrigues R-codes at dependency + 4 Agalega A-codes) |
| Martinique | MQ | 30 | 35 | La Poste Hexasmal (Sep 2026); shared: 97218 Basse-Pointe, 97222 Bellefontaine, 97250 Saint-Pierre |
| Mayotte | YT | 11 | 18 | La Poste Hexasmal (Sep 2026); shared primaries: Mamoudzou, Dzaoudzi, Chirongui, Mtsamboro, Bandraboua, Dembeni, Ouangani |
| Micronesia | FM | 4 | 4 | USPS via FSM government (96941 Pohnpei, 96942 Chuuk, 96943 Yap, 96944 Kosrae) |
| Moldova | MD | 1214 | 1220 | GeoNames dump at district/city level (MD-2000–MD-7843; MD-5219 omitted on district conflict; shared-code primary = majority-village district) |
| Montenegro | ME | 149 | 149 | Pošta CG branch network via API (81000–85530; 80000 service code + retired 81122 excluded; 85333→Tivat) |
| Monaco | MC | 1 | 0 | UPU MCO profile (98000 delivery; 01–99 are delivery-type) |
| Mongolia | MN | 39 | 39 | Mongol Post branch directory (21 aimag posts + UB horoo branches; EasyBox lockers carry no codes; 3 codeless service branches omitted; per-sum codes not published) |
| Montserrat | MS | 8 | 8 | Government of Montserrat postcode pamphlet |
| Nauru | NR | 1 | 0 | UPU single-code list (NRU68) |
| New Caledonia | NC | 50 | 50 | La Poste Hexasmal (Sep 2026) |
| North Macedonia | MK | 326 | 326 | Makedonska Pošta 2016 unit list + settlement directory (1000–7550; 1137 uncertain commune + retired/stale codes excluded) |
| Norway | NO | 5136 | 5136 | GeoNames dump joined on municipal codes + Posten.no Svalbard/Jan Mayen codes (0001–9991; PO-only status per code unverified) |
| Niue | NU | 1 | 0 | UPU single-code list (9974) |
| Palau | PW | 2 | 16 | USPS bulletin (96939 Ngerulmud/Melekeok, 96940 rest, Koror primary) |
| Philippines | PH | 1933 | 1933 | GeoNames dump at province level (1000–9811; 258 facility/PO-box codes excluded; NCR linked at region) |
| Puerto Rico | PR | 177 | 177 | GeoNames USPS ZIP dump joined on municipio FIPS (00601–00962; PO-only status per ZIP unverified) |
| Réunion | RE | 37 | 37 | La Poste Hexasmal (Sep 2026) |
| Romania | RO | 37914 | 37914 | GeoNames dump at department level (010011–927250; street codes linked to county) |
| Saint Helena | SH | 3 | 10 | UPU single-code list (STHL/ASCN/TDCU 1ZZ; Jamestown primary for STHL) |
| Saint Lucia | LC | 47 | 48 | Government of Saint Lucia postcode table (LC01 101–LC18 101; 7 private-box codes excluded; Marisule dual-linked) |
| Saint Pierre and Miquelon | PM | 1 | 1 | UPU addressing (97500 both communes) |
| Saint Vincent and the Grenadines | VC | 56 | 56 | SVG Postal Corp official list (VC0110–VC0472; VC0100 box-only + VC0292 disputed Mesopotamia omitted) |
| Saint-Barthélemy | BL | 1 | 1 | UPU addressing (97133) |
| Saint-Martin | MF | 1 | 1 | UPU addressing (97150) |
| San Marino | SM | 10 | 10 | UPU SMR profile (47890–47899; Serravalle holds 47891+47899) |
| Slovenia | SI | 468 | 469 | Pošta Slovenije official list Aug-2025 via archive (1000–9503; 76 PO-box/large-user/internal excluded; 3231 Grobelno dual-linked Šentjur primary) |
| Turks and Caicos | TC | 1 | 0 | UPU single-code list (TKCA 1ZZ) |
| US Virgin Islands | VI | 16 | 16 | GeoNames USPS ZIP dump, island-attributed (00801–00851 at district level; subdistrict split + PO-only status need licensed USPS city file) |
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
| Afghanistan | AF | expansion | L2: district (401) |
| Aland | AX | complete | L1: municipality (16) |
| Albania | AL | complete | L2: municipality (61) |
| Algeria | DZ | expansion | L2: daira (548) |
| American Samoa | AS | complete | L2: county (15) |
| Andorra | AD | complete | L1: parish (7) |
| Angola | AO | none | L2: municipality (326) |
| Anguilla | AI | complete | L1: district (14) |
| Antigua and Barbuda | AG | none | L1: dependency,parish (8) |
| Argentina | AR | expansion | L2: commune,department,partido (527) |
| Armenia | AM | expansion | L2: district,municipality (81) |
| Aruba | AW | none | L1: capital_city,region (9) |
| Australia | AU | expansion | L2: borough,city,council,municipality,region,rural_city,shire,town (537) |
| Austria | AT | expansion | L2: district,statutory_city (93) |
| Azerbaijan | AZ | complete | L1: district,municipality (77) |
| Bahamas | BS | none | L1: district,island (32) |
| Bahrain | BH | expansion | L1: governorate (4) |
| Bangladesh | BD | expansion | L2: district (64) |
| Barbados | BB | complete | L1: parish (11) |
| Belarus | BY | expansion | L2: district (118) |
| Belgium | BE | expansion | L2: province (10) |
| Belize | BZ | none | L1: district (6) |
| Benin | BJ | none | L2: commune (77) |
| Bermuda | BM | expansion | L2: municipality (2) |
| Bhutan | BT | complete | L1: district (20) |
| Bolivia | BO | none | L2: province (112) |
| Bosnia and Herzegovina | BA | complete | L2: municipality (142) |
| Botswana | BW | none | L2: subdistrict (23) |
| Brazil | BR | expansion | L2: district,municipality (5571) |
| Brunei | BN | complete | L2: mukim (39) |
| Bulgaria | BG | admin-ready | L2: municipality (265) |
| Burkina Faso | BF | none | L2: province (47) |
| Burundi | BI | none | L2: commune (42) |
| Cambodia | KH | expansion | L2: district,municipality,section (210) |
| Cameroon | CM | none | L2: department (58) |
| Canada | CA | expansion | L2: indigenous_reserve,municipality,unorganized (5028) |
| Cape Verde | CV | admin-ready | L2: parish (32) |
| Caribbean Netherlands | BQ | none | L1: special_municipality (3) |
| Cayman Islands | KY | expansion | L2: district (7) |
| Central African Republic | CF | none | L2: subprefecture (80) |
| Chad | TD | none | L2: department (63) |
| Chile | CL | expansion | L2: province (56) |
| China | CN | expansion | L2: autonomous_prefecture,league,prefecture,prefecture_city (333) |
| Colombia | CO | expansion | L2: locality,municipality,non_municipalized_area (1140) |
| Comoros | KM | none | L2: prefecture (16) |
| Congo | CG | none | L2: district (89) |
| Costa Rica | CR | expansion | L2: canton (84) |
| Croatia | HR | complete | L1: county (21) |
| Cuba | CU | admin-ready | L2: municipality (168) |
| Cyprus | CY | expansion | L1: district (6) |
| Czech Republic | CZ | expansion | L2: district (76) |
| DR Congo | CD | expansion | L2: territory (145) |
| Denmark | DK | complete | L2: municipality (98) |
| Djibouti | DJ | expansion | L2: subprefecture (20) |
| Dominica | DM | none | L1: parish (10) |
| Dominican Republic | DO | expansion | L2: district,province (32) |
| Ecuador | EC | expansion | L2: canton (222) |
| Egypt | EG | expansion | L2: district (365) |
| El Salvador | SV | admin-ready | L2: municipality (44) |
| Equatorial Guinea | GQ | none | L2: province (8) |
| Eritrea | ER | none | L2: subregion (58) |
| Estonia | EE | admin-ready | L2: rural_municipality,urban_municipality (78) |
| Eswatini | SZ | complete | L1: region (4) |
| Ethiopia | ET | expansion | L2: woreda,zone (127) |
| Faroe Islands | FO | complete | L2: municipality (29) |
| Fiji | FJ | none | L2: province (14) |
| Finland | FI | admin-ready | L2: city,municipality (292) |
| France | FR | expansion | L2: department (102) |
| French Guiana | GF | complete | L2: commune (22) |
| French Polynesia | PF | complete | L2: commune (48) |
| French Southern Territories | TF | none | L1: district (5) |
| Gabon | GA | none | L2: department (49) |
| Gambia | GM | none | L2: district (42) |
| Georgia | GE | expansion | L2: city,district,municipality (85) |
| Germany | DE | expansion | L2: rural_district,urban_district (401) |
| Ghana | GH | none | L2: district,metropolitan_city,municipality (261) |
| Greece | GR | complete | L2: municipality (332) |
| Greenland | GL | complete | L1: municipality (5) |
| Grenada | GD | none | L1: dependency,parish (7) |
| Guadeloupe | GP | complete | L2: commune (32) |
| Guam | GU | complete | L1: village (19) |
| Guatemala | GT | complete | L1: department (22) |
| Guernsey | GG | expansion | L1: dependency,parish (12) |
| Guinea | GN | expansion | L2: prefecture (33) |
| Guinea-Bissau | GW | expansion | L2: sector (38) |
| Guyana | GY | admin-ready | L2: neighbourhood_democratic_council,town (76) |
| Haiti | HT | expansion | L2: arrondissement (42) |
| Honduras | HN | admin-ready | L2: municipality (298) |
| Hong Kong | HK | none | L1: district (18) |
| Hungary | HU | expansion | L2: district (197) |
| Iceland | IS | complete | L2: municipality (61) |
| India | IN | expansion | L2: district (786) |
| Indonesia | ID | expansion | L3: district (7285) |
| Iran | IR | expansion | L2: county (429) |
| Iraq | IQ | expansion | L2: district (119) |
| Ireland | IE | expansion | L2: county (26) |
| Isle of Man | IM | expansion | L2: district,parish,town,village (21) |
| Italy | IT | expansion | L2: autonomous_province,decentralization_entity,free_municipal_consortium,metropolitan_city,province (109) |
| Ivory Coast | CI | none | L2: region (31) |
| Jamaica | JM | none | L1: parish (14) |
| Japan | JP | expansion | L2: city,town,village,ward (1747) |
| Jersey | JE | expansion | L2: canton,cueillette,vingtaine (56) |
| Jordan | JO | expansion | L2: liwa (51) |
| Kazakhstan | KZ | expansion | L2: district (170) |
| Kenya | KE | expansion | L2: constituency (290) |
| Kiribati | KI | complete | L2: council (24) |
| Kosovo | XK | complete | L2: municipality (38) |
| Kuwait | KW | expansion | L2: area (135) |
| Kyrgyzstan | KG | expansion | L2: district (44) |
| Laos | LA | expansion | L2: district (148) |
| Latvia | LV | complete | L1: municipality,state_city (42) |
| Lebanon | LB | expansion | L2: caza (25) |
| Lesotho | LS | expansion | L2: constituency (80) |
| Liberia | LR | expansion | L2: district (127) |
| Libya | LY | none | L2: baladiya (100) |
| Liechtenstein | LI | complete | L1: commune (11) |
| Lithuania | LT | complete | L2: city_municipality,district_municipality,municipality (60) |
| Luxembourg | LU | complete | L2: commune (100) |
| Madagascar | MG | expansion | L2: region (24) |
| Malawi | MW | expansion | L2: district (28) |
| Malaysia | MY | complete | L4: locality,subdistrict (292) |
| Maldives | MV | complete | L2: island (192) |
| Mali | ML | none | L2: cercle (159) |
| Malta | MT | expansion | L1: local_council (68) |
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
| Nepal | NP | expansion | L2: district (77) |
| Netherlands | NL | expansion | L2: municipality (342) |
| New Caledonia | NC | complete | L2: commune (33) |
| New Zealand | NZ | expansion | L2: city,council,district (67) |
| Nicaragua | NI | admin-ready | L2: municipality (153) |
| Niger | NE | none | box-only system (UPU: deliveries to P.O. Boxes only); codes pass through, nothing to import |
| Nigeria | NG | expansion | L2: area_council,lga (774) |
| Niue | NU | complete | L1: village (14) |
| North Korea | KP | none | L2: district (179) |
| North Macedonia | MK | complete | L1: municipality (80) |
| Norway | NO | complete | L2: municipality (357) |
| Oman | OM | expansion | L2: wilayat (63) |
| Pakistan | PK | expansion | L2: district (178) |
| Palau | PW | complete | L1: state (16) |
| Palestine | PS | expansion | L1: governorate (16) |
| Panama | PA | none | L2: district (81) |
| Papua New Guinea | PG | expansion | L2: district (96) |
| Paraguay | PY | expansion | L2: district (263) |
| Peru | PE | expansion | L2: province (196) |
| Philippines | PH | complete | L1: province (82) |
| Poland | PL | expansion | L2: city_county,land_county (380) |
| Portugal | PT | expansion | L2: municipality (308) |
| Puerto Rico | PR | complete | L1: municipality (78) |
| Qatar | QA | none | L2: zone (90) |
| Reunion | RE | complete | L2: commune (24) |
| Romania | RO | complete | L1: department (41) |
| Russia | RU | expansion | L1: autonomous_oblast,federal_city,krai,oblast,okrug,republic (83) |
| Rwanda | RW | none | L2: district (30) |
| Saint Barthelemy | BL | complete | L1: overseas_collectivity (1) |
| Saint Helena | SH | complete | L1: district,island (10) |
| Saint Kitts and Nevis | KN | expansion | L2: parish (14) |
| Saint Lucia | LC | complete | L1: district (10) |
| Saint Martin | MF | complete | L1: overseas_collectivity (1) |
| Saint Pierre and Miquelon | PM | complete | L1: overseas_collectivity (1) |
| Saint Vincent and the Grenadines | VC | complete | L1: parish (6) |
| Samoa | WS | admin-ready | L2: village (342) |
| San Marino | SM | complete | L1: municipality (9) |
| Sao Tome and Principe | ST | none | L1: autonomous_region,district (7) |
| Saudi Arabia | SA | expansion | L2: governorate (139) |
| Senegal | SN | expansion | L2: department (46) |
| Serbia | RS | admin-ready | L2: city,city_municipality,municipality (157) |
| Seychelles | SC | none | L1: district (27) |
| Sierra Leone | SL | none | L2: district (16) |
| Singapore | SG | runtime | L2: planning_area,postal_sector (136) |
| Slovakia | SK | expansion | L2: district (79) |
| Slovenia | SI | complete | L1: municipality,urban_municipality (212) |
| Solomon Islands | SB | none | L2: ward (183) |
| Somalia | SO | none | L2: district (89) |
| South Africa | ZA | expansion | L2: city_municipality,district_municipality (52) |
| South Korea | KR | expansion | L2: city,county,district (228) |
| South Sudan | SS | none | L2: county (88) |
| Spain | ES | expansion | L2: province (50) |
| Sri Lanka | LK | expansion | L2: district (25) |
| Sudan | SD | expansion | L2: district (188) |
| Suriname | SR | none | L2: resort (63) |
| Sweden | SE | expansion | L2: municipality (290) |
| Switzerland | CH | expansion | L2: district (146) |
| Syria | SY | none | L2: district (66) |
| Taiwan | TW | expansion | L2: county_administered_city,district,mountain_indigenous_district,mountain_indigenous_township,rural_township,urban_township (368) |
| Tajikistan | TJ | expansion | L2: city,district (69) |
| Tanzania | TZ | expansion | L2: district (193) |
| Thailand | TH | expansion | L2: amphoe,khet (928) |
| Timor-Leste | TL | admin-ready | L2: administrative_post (67) |
| Togo | TG | none | L2: prefecture (39) |
| Tonga | TO | none | L2: district (23) |
| Trinidad and Tobago | TT | expansion | L1: borough,city,region,ward (15) |
| Tunisia | TN | expansion | L2: delegation (279) |
| Turkmenistan | TM | expansion | L2: district (58) |
| Turks and Caicos | TC | complete | L1: district (6) |
| Tuvalu | TV | none | L1: island_council,town_council (8) |
| Türkiye | TR | expansion | L2: district (973) |
| US Minor Outlying Islands | UM | none | L1: island (9) |
| US Virgin Islands | VI | complete | L1: district (3) |
| Uganda | UG | admin-ready | L2: city,district (146) |
| Ukraine | UA | expansion | L2: raion (136) |
| United Arab Emirates | AE | none | L1: emirate (7) |
| United Kingdom | GB | expansion | L2: council_area,county,county_borough,district (113) |
| United States | US | expansion | L2: borough,census_area,city,county,municipality,parish,planning_region (3143) |
| Uruguay | UY | admin-ready | L2: municipality (125) |
| Uzbekistan | UZ | expansion | L2: city,tuman (206) |
| Vanuatu | VU | none | L2: area_council,municipality (63) |
| Venezuela | VE | admin-ready | L2: municipality (335) |
| Vietnam | VN | complete | L2: commune,special_zone,ward (3321) |
| Wallis and Futuna | WF | complete | L2: district (3) |
| Yemen | YE | none | L2: district (333) |
| Zambia | ZM | expansion | L2: district (116) |
| Zimbabwe | ZW | none | L2: district (64) |

## Queue scoping

`admin-ready` countries need only postcode→area compilation against
already-bundled rows. Done: Åland, Faroe Islands, Iceland, Saint
Lucia, Saint Vincent, Barbados, Kosovo, US Virgin Islands, Puerto
Rico, Moldova, Philippines, Maldives, Albania, Denmark, Guatemala,
Slovenia, Croatia, Mauritius, Norway, Luxembourg, Lithuania,
Latvia, Azerbaijan, Romania, Montenegro, North Macedonia, Bosnia
and Herzegovina, Eswatini, Brunei, Bhutan, Mongolia, Greece,
Vietnam.
Still queued: the parked countries (see rules) and the
remaining `admin-ready` verdicts above.

`expansion` countries need doc-15 locality rows first. Street-level
systems additionally need sub-locality data or licensed files:
Guernsey/Jersey/Isle of Man (Royal Mail PAF, licensed), Malta
(MaltaPost full list), Cayman Islands, Trinidad and Tobago,
Brazil (CEP), Argentina (CPA), Mexico (colonias), Colombia
(6-digit), Netherlands (4+2), Sweden (PostNord), Canada
(full codes; FSA-level possible), United States (USPS licensed,
GeoNames fallback), Japan (Japan Post open data), South Korea
(Juso open API), Australia (PAF licensed), New Zealand, Taiwan
(6-digit), Portugal (street suffix), Saint Kitts and Nevis
(town codes, no parish mapping yet), Cyprus, Palestine, Russia
(pending a GAR extract — see the Russia section in
[country data](05-country-data.md)). France can join Hexasmal
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
- Hong Kong 999077 and French Southern Territories codes are
  foreign-administered routing codes, not domestic systems.
  Verdict `none`.
- Israel has no geography provider yet; provider creation is
  separate work outside this overlay pass.
