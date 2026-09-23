---
title: Provider Coverage Registry
---

# Provider Coverage Registry

At-a-glance depth and dataset status for every bundled geography
provider. Update the row whenever a provider gains a level, changes
counts, or changes scope. See [Country Data](./05-country-data.md)
for per-country narrative and [Provider Authoring](./13-provider-authoring.md)
for how to add a level.

Current shape: 228 providers — 2 dual-hierarchy, 1 depth-4, 1 depth-3, 178 depth-2,
46 depth-1.
All 228 providers ship a formatter: 180 print a postcode (←97 ← state1 →39 US →8 above6 below27 with country1 after country1), 48 codeless.

## Legend

- **States** — `State` rows the provider seeds (`stateDefinitions`).
- **Mapped** — state-to-area links (`stateAreaMappings`).
- **Rows** — data rows in the country's `*-address-areas.csv`.
- **Depth** — tiers in the administrative hierarchy (`dual a+b` lists
  each hierarchy for two-hierarchy providers).
- **Hierarchy** — hierarchy key with its level chain.
- **Roles** — assignable area roles the provider defines (`—` means the
  state *is* the area and there is nothing to assign).
- **Fmt** — postcode position the formatter implements: `←` code
  left of the locality, `→` code right of it, `US →` US-system
  `LOCALITY ST ZIP` line, `above`/`below` code on its own line
  above/below the locality, `← state` code left of the state or
  province (Afghanistan), `with country` code on the country line
  left of the country name (Japan), `after country` code after the
  country line (Singapore), `none` no postcode system (a supplied
  code prints on its own line).
- **Status** — `Complete` (at designed depth), `L1` (level-1 only),
  plus scope notes. `candidate` marks a researched next tier;
  `out of scope` marks a deliberate exclusion.

## Registry

| Country | Code | States | Mapped | Rows | Depth | Hierarchy | Roles | Fmt | Status |
|---|---|---:|:---:|:---:|:---:|---|---|---|---|
| Afghanistan | AF | 34 | 34 | 435 | 2 | administrative: province > district | district | ← state | Complete — 34 provinces + 401 districts (OCHA COD-AB v03, Jun 2025; UN p-codes; govt counts vary 398–407 by vintage) |
| Aland | AX | 16 | 16 | 16 | 1 | administrative: municipality | — | ← | L1 — no admin tier-2; L1 already municipal level |
| Albania | AL | 12 | 12 | 73 | 2 | administrative: county > municipality | municipality | above | Complete — 12 counties + 61 municipalities (12 county pages; main table lacks county column) |
| Algeria | DZ | 69 | 69 | 617 | 2 | administrative: wilaya > daira | daira | ← | Complete — 69 wilayas + 548 dairas per décrets 91-306/26-253; communes excluded |
| American Samoa | AS | 5 | 5 | 20 | 2 | administrative: district > county | county | US → | Complete — 3 districts + 2 atolls + 15 counties (5 per district; Rose/Swains childless) |
| Andorra | AD | 7 | 7 | 7 | 1 | administrative: parish | — | ← | Blocked — wiki table contradicts its own "44 official poblacions" claim (45 rows with figures + 8 n/a after Nov-2024 re-adds of unofficial places); only stat reference is a dead 2016 estadistica.ad link, stats office unreachable; rival all-villages lists run to 57 |
| Angola | AO | 21 | 21 | 347 | 2 | administrative: province > municipality | municipality | none | Complete — 21 provinces + 326 municipalities (Law 14/24 gazette annexes, one map page each; CCU retired; CUA/CUB/IEB/MLE provisional) |
| Anguilla | AI | 14 | 14 | 14 | 1 | administrative: district | — | below | L1 — no admin tier-2; 14 districts terminal|
| Antigua and Barbuda | AG | 8 | 8 | 8 | 1 | administrative: parish | — | none | L1 — no admin tier-2; parishes/dependencies terminal|
| Argentina | AR | 24 | 24 | 551 | 2 | administrative: province / city > department / partido / commune | department | ← | Complete — 24 provinces + 377 departments + 135 partidos + 15 comunas (Departments/Partidos/Communes of Buenos Aires) |
| Armenia | AM | 11 | 11 | 92 | 2 | administrative: region / city > municipality / district | municipality | ← | Complete — 10 regions + 1 city + 69 municipalities + 12 Yerevan districts (hy.wiki current tables, transliterated + en-verified; en pages mixed-vintage) |
| Aruba | AW | 9 | 9 | 9 | 1 | administrative: region | — | none | L1 — no admin tier-2; regions are statistical|
| Australia | AU | 8 | 8 | 545 | 2 | administrative: state > city / shire / town / region / borough / municipality / rural_city / council | lga | → | Complete — 8 states/territories + 537 LGAs (NSW 128, VIC 79, QLD 78, WA 137, SA 68, TAS 29, NT 18; ACT childless) |
| Austria | AT | 9 | 9 | 102 | 2 | administrative: state > district / statutory_city | district | ← | Complete — 9 states + 79 districts + 14 statutory cities (Districts of Austria; Vienna is its own city) |
| Azerbaijan | AZ | 78 | 78 | 763 | 2 | administrative: district > local_municipality | local_municipality | ← | Complete — 78 districts/cities + 685 local municipalities (SSC classification; liberated territories + Aghdara absent, childless) |
| Bahamas | BS | 32 | 32 | 32 | 1 | administrative: district | — | none | L1 — no admin tier-2; districts terminal|
| Bahrain | BH | 4 | 4 | 4 | 1 | administrative: governorate | — | → | L1; no admin tier-2, blocks are postal |
| Bangladesh | BD | 72 | 8 | 72 | 2 | administrative: division > district | district | → | Complete to district; upazilas out of scope |
| Barbados | BB | 11 | 11 | 11 | 1 | administrative: parish | — | → | L1 — no admin tier-2; 11 parishes terminal|
| Belarus | BY | 7 | 7 | 125 | 2 | administrative: oblast / city > district | district | ← | Complete — 7 regions + 118 districts (Districts of Belarus; Minsk-region rows under oblast not city) |
| Belgium | BE | 3 | 3 | 13 | 2 | administrative: region > province | province | ← | Complete — 3 regions + 10 provinces; Brussels childless |
| Belize | BZ | 6 | 6 | 6 | 1 | administrative: district | — | none | Blocked — councils exist but no consolidated district-mapped list; OCHA COD-AB stops at the 6 districts |
| Benin | BJ | 12 | 12 | 89 | 2 | administrative: department > commune | commune | none | Complete — 12 departments + 77 communes (per-department lists) |
| Bermuda | BM | 9 | 9 | 11 | 2 | administrative: parish > municipality | municipality | → | Complete — 9 parishes + City of Hamilton (under Pembroke) + Town of St George (under St George's) |
| Bhutan | BT | 20 | 20 | 225 | 2 | administrative: district > gewog | gewog | → | Complete — 20 districts + 205 gewogs (single table w/ Dzongkhag rowspan (ECB spellings)) |
| Bolivia | BO | 9 | 9 | 121 | 2 | administrative: department > province | province | none | Complete — 9 departments + 112 provinces (Source: Wikipedia Provinces of Bolivia department navbox templates (MediaWiki API, Sep 2026).) |
| Bosnia and Herzegovina | BA | 3 | 3 | 146 | 2 | administrative: district / entity > municipality | municipality | ← | Complete — 3 entities + 143 municipalities (79 FBiH + 64 RS flatlists; +Siroki Brijeg +Istocno Sarajevo; cantons skipped) |
| Botswana | BW | 17 | 17 | 40 | 2 | administrative: district > subdistrict | subdistrict | none | Complete — 10 districts + 2 cities + 5 towns + 23 subdistricts (Chobe/North-East childless; Serowe-Palapye split); OR provisional |
| Brazil | BR | 27 | 27 | 5598 | 2 | administrative: state > municipality | municipality | below | Complete — 27 + 5,571 municipios (IBGE codes); Noronha typed district |
| Brunei | BN | 4 | 4 | 43 | 2 | administrative: district > mukim | mukim | → | Complete; kampungs via components |
| Bulgaria | BG | 28 | 28 | 293 | 2 | administrative: district > municipality | municipality | ← | Complete — 28 provinces + 265 municipalities (Municipalities of Bulgaria) |
| Burkina Faso | BF | 64 | 64 | 64 | 2 | administrative: region > province | province | ← | Complete — 17 regions + 47 provinces L2 (July 2025 reform); region codes 14–17 + KAR/DYA provisional |
| Burundi | BI | 5 | 5 | 47 | 2 | administrative: province > commune | commune | none | Complete — 5 provinces + 42 communes (RGPH 2024 census tables; Karusi spelling); codes 01-05 provisional pending ISO |
| Cambodia | KH | 25 | 25 | 235 | 2 | administrative: province / municipality > district / municipality / section | district | → | Complete — 24 provinces + Phnom Penh + 163 districts + 33 municipalities + 14 sections (per-province NCDD tables w/ geocodes) |
| Cameroon | CM | 10 | 10 | 68 | 2 | administrative: region > department | department | none | Complete — 10 regions + 58 departments (per-region lists) |
| Canada | CA | 13 | 13 | 5041 | 2 | administrative: province > municipality | municipality | → | Complete — 13 + 5,028 CSDs (2024 SGC); reserves/unorganized typed |
| Cape Verde | CV | 24 | 24 | 56 | 2 | administrative: municipality > parish | parish | ← | Complete — 22 municipalities + 2 island groups + 32 parishes (island groups terminal) |
| Caribbean Netherlands | BQ | 3 | 3 | 3 | 1 | administrative: special_municipality | — | none | L1 — no admin tier-2; 3 municipalities terminal|
| Cayman Islands | KY | 3 | 3 | 10 | 2 | administrative: island > district | district | → | Complete — 3 islands + 7 districts (5 Grand Cayman; Sister Islands self-parented, supersedes cross-cut ruling) |
| Central African Republic | CF | 20 | 20 | 100 | 2 | administrative: prefecture / economic_prefecture > subprefecture | subprefecture | none | Complete — 20 prefectures + 80 subprefectures (per-prefecture lists (Bangui childless)) |
| Chad | TD | 23 | 23 | 86 | 2 | administrative: province > department | department | none | Complete — 23 provinces + 63 departments (per-region grouped tables incl. Ennedi split (N'Djamena childless)) |
| Chile | CL | 16 | 16 | 72 | 2 | administrative: region > province | province | ← | Complete — 16 regions + 56 provinces (Source: Wikipedia Provinces of Chile (MediaWiki API, Sep 2026).) |
| China | CN | 33 | 33 | 366 | 2 | administrative: province > prefecture | prefecture | ← | L2 333 prefectures; Taiwan separate (TW); Suzhou/Fuzhou disambiguated |
| Colombia | CO | 33 | 33 | 1173 | 2 | administrative: department / capital_district > municipality / locality / non_municipalized_area | municipality | → | Complete — 33 departments + 1101 municipalities + 20 Bogota localities + 19 non-municipalized areas (Municipalities of Colombia) |
| Comoros | KM | 3 | 3 | 19 | 2 | administrative: island > prefecture | prefecture | none | Complete — 3 islands + 16 prefectures (fr law-based island lists (Loi 11-006)) |
| Congo | CG | 15 | 15 | 104 | 2 | administrative: department > district | district | none | Complete — 15 departments + 89 districts (per-department lists w/ Oct-2024 moves applied (Brazzaville childless)) |
| Costa Rica | CR | 7 | 7 | 91 | 2 | administrative: province > canton | canton | below | Complete — 7 provinces + 84 cantons (Cantons of Costa Rica) |
| Croatia | HR | 21 | 21 | 577 | 2 | administrative: county > municipality / town | municipality | ← | Complete — 21 counties + 428 municipalities + 128 towns (Municipalities/List of cities and towns in Croatia; fixed meimurje L1 typo) |
| Cuba | CU | 16 | 16 | 184 | 2 | administrative: province / special_municipality > municipality | municipality | ← | Complete — 16 provinces + 168 municipalities (Source: Wikipedia Municipalities of Cuba (MediaWiki API, Sep 2026). Havana city rows parented to La Habana province row.) |
| Cyprus | CY | 6 | 6 | 758 | 2 | administrative: district + postal: district > locality | postal_locality | ← | Complete — 6 districts + 752 localities (GeoNames postal dump; Keryneia 57 codes included) |
| Czech Republic | CZ | 14 | 14 | 90 | 2 | administrative: region > district | district | ← | Complete — 13 regions + Praha + 76 districts; Praha childless |
| Denmark | DK | 5 | 5 | 103 | 2 | administrative: region > municipality | municipality | ← | Complete — 5 regions + 98 municipalities (List of municipalities of Denmark; LAU codes) |
| Djibouti | DJ | 6 | 6 | 26 | 2 | administrative: region > subprefecture | subprefecture | ← | Complete — 5 regions + Djibouti City + 20 sub-prefectures (3/2/4/1/4/6; town-article parenting; Adailou spelling) |
| Dominica | DM | 10 | 10 | 10 | 1 | administrative: parish | — | none | L1 — no admin tier-2; 10 parishes terminal|
| Dominican Republic | DO | 10 | 10 | 42 | 2 | administrative: region > province | province | ← | Complete — 10 regions + 31 provinces + DN; DN under Ozama |
| DR Congo | CD | 26 | 26 | 171 | 2 | administrative: province > territory | territory | ← | Complete — 26 provinces + 145 territories (post-2015 mapping; Kinshasa terminal) |
| Ecuador | EC | 24 | 24 | 246 | 2 | administrative: province > canton | canton | ← | Complete — 24 provinces + 222 cantons (Source: Wikipedia Cantons of Ecuador (MediaWiki API, Sep 2026); header counts sum to 222.) |
| Egypt | EG | 27 | 27 | 392 | 2 | administrative: governorate > district | district | below | Complete — 27 governorates + 365 districts (OCHA COD-AB, CAPMAS geography, Apr 2017; mixed qism/markaz; COD transliteration) |
| El Salvador | SV | 14 | 14 | 58 | 2 | administrative: department > municipality | municipality | ← | Complete — 14 departments + 44 municipalities (Source: Wikipedia List of municipalities and districts of El Salvador (MediaWiki API, Sep 2026). Post-May-2024 reform: 44 municipalities; former 262 are now districts (not modelled).) |
| Equatorial Guinea | GQ | 2 | 2 | 10 | 2 | administrative: region > province | province | none | Complete — 2 regions + 8 provinces |
| Eritrea | ER | 6 | 6 | 64 | 2 | administrative: region > subregion | subregion | none | Complete — 6 regions + 58 subregions (per-region bullets) |
| Estonia | EE | 15 | 15 | 93 | 2 | administrative: county > municipality | municipality | ← | Complete — 15 counties + 78 municipalities (Toila merged into Jõhvi 2025) |
| Eswatini | SZ | 4 | 4 | 59 | 2 | administrative: region > inkhundla | inkhundla | below | Complete — 4 regions + 55 inkhundlas (per-region table+bullets (+Mbabane West missing from Hhohho table)) |
| Ethiopia | ET | 14 | 14 | 141 | 2 | administrative: city / region > zone / woreda | zone | ← | Complete — 14 first-level + 118 zones + 9 Harari woredas (per-region lists; Dire Dawa zoneless; special woredas counted as zones) |
| Faroe Islands | FO | 6 | 6 | 35 | 2 | administrative: region > municipality | municipality | ← | Complete — 6 regions + 29 municipalities (main table (6 sýsla regions; Sunda ruled under Eysturoy, Hvalba under Suðuroy)) |
| Fiji | FJ | 5 | 5 | 19 | 2 | administrative: division > province | province | none | Complete — 4 divisions + Rotuma + 14 provinces; Rotuma standalone |
| Finland | FI | 18 | 18 | 310 | 2 | administrative: region > municipality / city | municipality | ← | Complete — 18 regions + 185 municipalities + 107 cities (fi.wiki Luettelo Suomen kunnista; 16 Aland rows excluded, covered by AX) |
| France | FR | 18 | 18 | 120 | 2 | administrative: region > department | department | ← | Complete — 18 regions + 101 departments + Lyon Metropolis (Departments of France; INSEE codes) |
| French Guiana | GF | 1 | 1 | 23 | 2 | administrative: overseas_region > commune | commune | ← | Complete — 1 overseas region + 22 communes (Source: Wikipedia Communes of French Guiana (MediaWiki API, Sep 2026); INSEE codes in code column.) |
| French Polynesia | PF | 5 | 5 | 53 | 2 | administrative: division > commune | commune | ← | Complete — 5 divisions + 48 communes (Administrative divisions of French Polynesia) |
| French Southern Territories | TF | 5 | 5 | 5 | 1 | administrative: district | — | none | L1 — uninhabited; 5 districts terminal|
| Gabon | GA | 9 | 9 | 58 | 2 | administrative: province > department | department | ← | Complete — 9 provinces + 49 departments (per-province bullets (Cap Esterias deleted 2013 excluded)) |
| Gambia | GM | 7 | 7 | 49 | 2 | administrative: city / region > district | district | none | Complete — 5 regions + Banjul + Kanifing + 42 districts (divisions renamed regions 2007; Kanifing first-level city) |
| Georgia | GE | 12 | 12 | 97 | 2 | administrative: autonomous_republic / region / city > municipality / district / city | municipality | ← | Complete — 9 regions + 2 ARs + Tbilisi + 65 municipalities + 16 districts + 4 cities (Geostat table; Abkhazia/SO units are Georgia formal claim, incl. 10 Tbilisi districts) |
| Germany | DE | 16 | 16 | 417 | 2 | administrative: state > district | district | ← | Complete — 16 states + 401 districts split rural/urban (294 Landkreise + 107 kreisfreie Städte; source Form column; Aachen/Hanover/Saarbrücken ride rural as district-level Kommunalverbände) |
| Ghana | GH | 16 | 16 | 277 | 2 | administrative: region > district | district | → | Complete — 16 regions + 261 assemblies (6 metropolitan + 113 municipal + 142 district) |
| Greece | GR | 14 | 14 | 346 | 2 | administrative: administrative_region > municipality | municipality | ← | Complete — 14 regions + 332 municipalities (List of municipalities of Greece 2011, incl. 2019 splits; Athos has none) |
| Greenland | GL | 5 | 5 | 5 | 1 | administrative: municipality | — | ← | L1 — no admin tier-2; 5 municipalities terminal|
| Grenada | GD | 7 | 7 | 7 | 1 | administrative: parish / dependency | — | none | L1 — 6 parishes + Carriacou dependency terminal|
| Guadeloupe | GP | 2 | 2 | 34 | 2 | administrative: district > commune | commune | ← | Complete — 2 districts + 32 communes (Communes of Guadeloupe + FR arrondissements) |
| Guam | GU | 19 | 19 | 19 | 1 | administrative: village | — | US → | L1 — 19 municipal villages, no tier below (regions statistical) |
| Guatemala | GT | 22 | 22 | 362 | 2 | administrative: department > municipality | municipality | ← | Complete — 22 departments + 340 municipalities (Source: Wikipedia Municipalities of Guatemala (MediaWiki API, Sep 2026).) |
| Guernsey | GG | 12 | 12 | 12 | 1 | administrative: parish / dependency | — | below | L1 — 10 parishes + Alderney/Sark as dependencies; douzaines are electoral only |
| Guinea | GN | 8 | 8 | 41 | 2 | administrative: administrative_region > prefecture | prefecture | ← | Complete — 7 regions + Conakry + 33 prefectures; Conakry childless |
| Guinea-Bissau | GW | 9 | 9 | 47 | 2 | administrative: region / autonomous_sector > sector | sector | ← | Complete — 8 regions + Bissau + 38 sectors (Leste/Norte/Sul statistical provinces not shipped) |
| Guyana | GY | 10 | 10 | 86 | 2 | administrative: region > town / neighbourhood_democratic_council | town | below | Complete — 10 regions + 10 towns + 66 NDCs (Neighbourhood Councils of Guyana; towns mapped to regions) |
| Haiti | HT | 10 | 10 | 52 | 2 | administrative: department > arrondissement | arrondissement | ← | Complete — 10 departments + 42 arrondissements (Source: Wikipedia Arrondissements of Haiti (MediaWiki API, Sep 2026). La Gonave links to the island article; named La Gonave per IHSI.) |
| Honduras | HN | 18 | 18 | 316 | 2 | administrative: department > municipality | municipality | ← | Complete — 18 departments + 298 municipalities (Source: Wikipedia Municipalities of Honduras (MediaWiki API, Sep 2026).) |
| Hong Kong | HK | 18 | 18 | 18 | 1 | administrative: district | — | none | L1 — no admin tier-2; constituencies are electoral only |
| Hungary | HU | 43 | 43 | 240 | 2 | administrative: county / city_with_county_rights / capital_city > district | district | ← | Complete — 43 counties/cities + 174 county districts + 23 Budapest districts (Districts of Hungary/Budapest) |
| Iceland | IS | 8 | 8 | 69 | 2 | administrative: region > municipality | municipality | ← | Complete — 8 regions + 61 municipalities (restructure; 3 merged away, 3 renamed official) |
| India | IN | 36 | 36 | 822 | 2 | administrative: state > district | district | below | Complete — 28 states + 8 UTs + 786 districts (LGD 31 May 2026 + Mahe/Yanam legacy codes); post-2011 splits/renames with aliases; Ladakh 5 + Kalyan Singh Nagar excluded (no LGD codes) |
| Indonesia | ID | 38 | 38 | 7837 | 4 | administrative: province > regency > district > village | district, regency | → | Complete to kecamatan; desa/kelurahan (83,762) opt-in via `geography.indonesia.villages` |
| Iran | IR | 31 | 31 | 460 | 2 | administrative: province > county | county | below | Complete — 31 ostans + 429 counties (UN OCHA COD v01, vintage May 2019; splits since not reflected; refresh from SCI when accessible) |
| Iraq | IQ | 19 | 19 | 138 | 2 | administrative: governorate > district | district | below | Complete — 19 governorates + 119 districts (per-governorate bullets (Makhmur under Nineveh only)) |
| Ireland | IE | 4 | 4 | 30 | 2 | administrative: province > county | county | below | Complete — 4 provinces + 26 counties |
| Isle of Man | IM | 6 | 6 | 27 | 2 | administrative: sheading > parish / town / district / village | local_authority | below | Complete — 6 sheadings + 13 parishes + 4 towns + 2 districts + 2 villages (Local government today table w/ Sheading column) |
| Italy | IT | 20 | 20 | 129 | 2 | administrative: region > province / metropolitan_city / free_municipal_consortium / decentralization_entity / autonomous_province | province | ← | Complete — 20 regions + 82 provinces + 15 metros + 6 consortiums + 4 entities + 2 autonomous (Provinces of Italy; Aosta disestablished excluded) |
| Ivory Coast | CI | 14 | 14 | 45 | 2 | administrative: district > region | region | none | Complete — 12 districts + Abidjan/Yamoussoukro + 31 regions (autonomous districts terminal; departments are L3) |
| Jamaica | JM | 14 | 14 | 14 | 1 | administrative: parish | — | none | L1 — no admin tier-2; 14 parishes terminal|
| Japan | JP | 47 | 47 | 1794 | 2 | administrative: prefecture > municipality | municipality | with country | Complete — 47 prefectures + 1,747 municipalities (792 cities + 743 towns + 183 villages + 23 Tokyo special wards + 6 Northern-Territories paper villages); designated-city wards out of scope |
| Jersey | JE | 12 | 12 | 68 | 2 | administrative: parish > vingtaine / canton / cueillette | vingtaine | below | Complete — 12 parishes + 48 vingtaines + 2 cantons + 6 cueillettes (Table of vingtaines w/ Parish column) |
| Jordan | JO | 12 | 12 | 63 | 2 | administrative: governorate > liwa | liwa | → | Complete — 12 governorates + 51 liwa per DOS Yearbook 2024; qada out of scope |
| Kazakhstan | KZ | 20 | 20 | 190 | 2 | administrative: region / city > district | district | ← | Complete — 20 regions + 170 districts (single table w/ rowspan Region (stat.gov.kz 2023; 3 cities childless, districts in flux)) |
| Kenya | KE | 47 | 47 | 337 | 2 | administrative: county > constituency | constituency | below | Complete — 47 counties + 290 constituencies (IEBC 1–290 verified; sub-counties coincide outside urban splits) |
| Kiribati | KI | 3 | 3 | 27 | 2 | administrative: island > council | council | → | Complete — 3 island groups + 24 councils (20 Gilbert incl. Banaba, 3 Line, Canton) |
| Kosovo | XK | 7 | 7 | 45 | 2 | administrative: district > municipality | municipality | ← | Complete — 7 districts + 38 municipalities (7-district table w/ municipality cells) |
| Kuwait | KW | 6 | 6 | 141 | 2 | administrative: governorate > area | area | ← | Complete — 6 governorates + 135 areas; blocks and per-area postcodes out of scope |
| Kyrgyzstan | KG | 9 | 9 | 53 | 2 | administrative: region / city > district | district | ← | Complete — 7 regions + Bishkek/Osh + 44 districts (per-region tables) |
| Laos | LA | 18 | 18 | 166 | 2 | administrative: province / prefecture > district | district | ← | Complete — 18 provinces + 148 districts (single coded table w/ Province column) |
| Latvia | LV | 42 | 42 | 627 | 2 | administrative: municipality > parish / town / city | parish | → | Complete — 35 municipalities + 7 state cities + 585 units (Varakļāni merged to Madona 1 Jul 2025; 3 twins share names) |
| Lebanon | LB | 9 | 9 | 34 | 2 | administrative: governorate > caza | caza | → | Complete — 9 governorates (KJ provisional) + 25 cazas (Districts of Lebanon) |
| Lesotho | LS | 10 | 10 | 90 | 2 | administrative: district > constituency | constituency | → | Complete — 10 districts + 80 constituencys (per-district tables (Legal Notice 37/2022)) |
| Liberia | LR | 15 | 15 | 142 | 2 | administrative: county > district | district | ← | Complete — 15 countys + 127 districts (rowspan county table (intro 136 stale; county articles confirm)) |
| Libya | LY | 22 | 22 | 122 | 2 | administrative: popularate > baladiya | baladiya | none | Complete — 22 sha'biyat + 100 baladiyas (IOM DTM R50/R62 identical sets, Oct 2023–Apr 2026; p-coded; DTM mantika mapped to ISO popularates) |
| Liechtenstein | LI | 11 | 11 | 11 | 1 | administrative: commune | — | ← | L1 — no admin tier-2; L1 already municipal level |
| Lithuania | LT | 10 | 10 | 70 | 2 | administrative: county > district_municipality / municipality / city_municipality | municipality | ← | Complete — 10 counties + 43 district + 10 plain + 7 city municipalities (restructure; Marijampolė retyped) |
| Luxembourg | LU | 12 | 12 | 112 | 2 | administrative: canton > commune | commune | ← | Complete — 12 cantons + 100 communes (List of communes of Luxembourg) |
| Madagascar | MG | 6 | 6 | 30 | 2 | administrative: province > region | region | ← | Complete — 6 provinces + 24 regions (single table w/ Province column (incl. Ambatosoa 2023)) |
| Malawi | MW | 3 | 3 | 31 | 2 | administrative: region > district | district | ← | Complete — 3 regions + 28 districts |
| Malaysia | MY | 16 | 16 | 1806 | dual 2+4 | administrative: region > division > district > subdivision; postal: region > locality | administrative_district, administrative_division, administrative_subdivision, postal_locality | ← | Complete; postal CSVs bundled; KL + Selangor + Pahang + Johor + Perlis + Melaka + Penang + Terengganu + Perak + Kedah + Kelantan per JUPEM UPI + N.Sembilan per PLANMalaysia/gazettes + Sabah + Sarawak per gazettes/SPR/DOSM |
| Maldives | MV | 23 | 23 | 215 | 2 | administrative: city / atoll > island | island | → | Complete — 23 atolls + 192 islands (per-atoll inhabited lists (Male/Kulhudhuffushi/Thinadhoo childless)) |
| Mali | ML | 20 | 20 | 179 | 2 | administrative: region > cercle | cercle | none | Complete — 19 regions + Bamako + 159 cercles (coded 0101–1909, gap-free; Bamako terminal); 9/10 per national law, diverge from ISO |
| Malta | MT | 68 | 68 | 68 | 1 | administrative: local_council | — | below | L1 — no admin tier-2; L1 already municipal level |
| Marshall Islands | MH | 26 | 2 | 26 | 2 | administrative: chain > municipality | municipality | US → | Complete — restructure 2 chains + 24 municipalities (14 Ralik, 10 Ratak; census chain table); municipalities unmapped states |
| Martinique | MQ | 4 | 4 | 38 | 2 | administrative: district > commune | commune | ← | Complete — 4 districts + 34 communes (Communes of Martinique + FR arrondissements) |
| Mauritania | MR | 15 | 15 | 78 | 2 | administrative: region > department | department | none | Complete — 15 regions + 63 departments (single table w/ Wilaya column (2024 census)) |
| Mauritius | MU | 12 | 12 | 154 | 2 | administrative: district > locality | locality | → | Complete — 9 districts + 3 dependencies + 142 localities (1 city + 4 towns + 137 villages incl. 3 Agaléga; 16 spans parent first-listed) |
| Mayotte | YT | 17 | 17 | 17 | 1 | administrative: commune | — | ← | L1 — 17 communes; L1 already municipal level |
| Mexico | MX | 32 | 32 | 2511 | 2 | administrative: state > municipality | municipality | ← | Complete — 32 states + 2,479 municipios (16 CDMX boroughs); INEGI CVEGEO |
| Micronesia | FM | 4 | 4 | 79 | 2 | administrative: state > municipality / city | municipality | US → | Complete — 4 states + 73 municipalities + 2 cities (40/4/11/20; Piherarh dup shipped once) |
| Moldova | MD | 37 | 37 | 1018 | 2 | administrative: district > commune / city | commune | ← | Complete — 37 L1 + 915 communes + 66 cities (Transnistria de jure; 6 pairs typed; 67 parent-scoped) |
| Monaco | MC | 17 | 17 | 17 | 1 | administrative: quarter | — | ← | L1 — 17 ISO quarters (2013 ordinance wards noted, not modelled) |
| Mongolia | MN | 22 | 22 | 361 | 2 | administrative: province / capital_city > sum / duureg | district | → | Complete — 21 aimags + Ulaanbaatar + 330 sums + 9 UB duuregs (per-province sum lists w/ counts) |
| Montenegro | ME | 25 | 25 | 25 | 1 | administrative: municipality | — | ← | L1 — no admin tier-2; L1 already municipal level |
| Montserrat | MS | 4 | 4 | 4 | 1 | administrative: parish | — | → | L1 — no admin tier-2; 4 parishes terminal (incl. uninhabited Saint Patrick) |
| Morocco | MA | 87 | 12 | 87 | 2 | administrative: region > province | province | ← | Complete to province; communes (~1,500) out of scope |
| Mozambique | MZ | 11 | 11 | 147 | 2 | administrative: province > district | district | ← | Complete — 10 provinces + Maputo City + 136 districts (129 provincial + 7 Maputo municipal; Maxixe excluded as city) |
| Myanmar | MM | 15 | 15 | 95 | 2 | administrative: region > district | district | → | Complete — 15 + 80 districts (MIMU via OCHA COD-AB, Feb 2024; Bago E/W + Shan E/N/S rolled up to ISO L1; announced 121 never operationalized) |
| Namibia | NA | 14 | 14 | 135 | 2 | administrative: region > constituency | constituency | below | Complete — 14 regions + 121 constituencies (Tondoro + Oshikunde added; Wiki table omits both) |
| Nauru | NR | 14 | 14 | 14 | 1 | administrative: district | — | below | L1 — 169 villages historical (1908 source, merged settlement; no admin function) |
| Nepal | NP | 7 | 7 | 84 | 2 | administrative: province > district | district | → | Complete — 7 provinces + 77 districts (7 per-province tables) |
| Netherlands | NL | 12 | 12 | 354 | 2 | administrative: province > municipality | municipality | ← | Complete — 12 provinces + 342 municipalities (Municipalities of the Netherlands; CBS codes; 3 specials under BQ) |
| New Caledonia | NC | 3 | 3 | 36 | 2 | administrative: province > commune | commune | ← | Complete — 3 provinces + 33 communes (Administrative divisions of New Caledonia; Poya spans N/S, parented South) |
| New Zealand | NZ | 17 | 17 | 1285 | 3 | administrative: region > district / city / council; postal: region > locality (refined by district) | district | ← | Complete — 17 regions + 67 territorial authorities (7 cross-boundary parented by largest share) + 1201 postal localities (1737 codes) |
| Nicaragua | NI | 17 | 17 | 170 | 2 | administrative: department / autonomous_region > municipality | municipality | above | Complete — 17 departments + 153 municipalities (Source: es.wikipedia Anexo:Municipios de Nicaragua (MediaWiki API, Sep 2026); en.wiki table has only 151.) |
| Niger | NE | 8 | 8 | 79 | 2 | administrative: region / urban_community > department / commune | department | ← | Complete — 7 regions + Niamey + 66 departments + 5 Niamey communes (per-region bullets + Niamey article) |
| Nigeria | NG | 37 | 37 | 811 | 2 | administrative: state > lga | lga | → | Complete — 37 states + 768 LGAs + 6 FCT area councils; post-2023 names; LCDAs excluded |
| Niue | NU | 14 | 14 | 14 | 1 | administrative: village | — | → | L1 — 14 municipal villages, no tier below |
| North Korea | KP | 13 | 13 | 192 | 2 | administrative: province > district | district | none | Complete — 13 + 179 districts (OCHA COD-AB, Jun 2019; Kaesong/Rason undivided; Pyongyang 3 rows incl. city core) |
| North Macedonia | MK | 80 | 80 | 80 | 1 | administrative: municipality | — | ← | L1 — no admin tier-2; L1 already municipal level |
| Norway | NO | 17 | 17 | 374 | 2 | administrative: county / arctic_region > municipality | municipality | ← | Complete — 17 counties + 357 municipalities (List of municipalities of Norway, May-2024 vintage; codes) |
| Oman | OM | 11 | 11 | 74 | 2 | administrative: governorate > wilayat | wilayat | above | Complete — 11 governorates + 63 wilayats |
| Pakistan | PK | 7 | 7 | 185 | 2 | administrative: province > district | district | → | Complete to district (178, mid-2026); tehsils out; Karezat/Jampur excluded |
| Palau | PW | 16 | 16 | 16 | 1 | administrative: state | — | US → | L1 — hamlets traditional/boundaryless, 9 states unlisted; no reliable tier-2 |
| Palestine | PS | 16 | 16 | 16 | 1 | administrative: governorate | — | → | L1 — localities (~500) skipped; no consolidated list, OCHA COD stops at governorates |
| Panama | PA | 14 | 14 | 95 | 2 | administrative: province / indigenous_region > district | district | none | Complete — 14 provinces + 81 districts (Source: Wikipedia Districts of Panama (MediaWiki API, Sep 2026), INEC 2023 census table. Guna Yala and Naso Tjer Di have no districts.) |
| Papua New Guinea | PG | 22 | 22 | 118 | 2 | administrative: province > district | district | → | Complete — 20 provinces + Bougainville + Port Moresby + 96 districts (post-2022 electorates; NCD seats under Port Moresby) |
| Paraguay | PY | 18 | 18 | 281 | 2 | administrative: department / capital_district > district | district | ← | Complete — 18 departments + 263 districts (Source: es.wikipedia Anexo:Municipios de Paraguay (MediaWiki API, Sep 2026); en.wiki table covers only 161 of 263. Typed district per official division term.) |
| Peru | PE | 26 | 26 | 222 | 2 | administrative: region / municipality > province | province | above | Complete — 26 regions + 196 provinces (Source: Wikipedia Provinces of Peru (MediaWiki API, Sep 2026); UBIGEO codes in code column. Lima-1501 under metro municipality, Callao-0701 under Callao region.) |
| Philippines | PH | 99 | 83 | 43750 | 3 | administrative: province > city / municipality / sub_municipality > barangay | municipality, barangay | ← | Complete — 82 provinces + NCR + 1656 muncities (149 + 1493 + 14) + 42011 barangays (PSGC 2025-2Q; HUCs to geographic province; SGA to Cotabato) |
| Poland | PL | 16 | 16 | 396 | 2 | administrative: voivodeship > land_county / city_county | land_county | ← | Complete — 16 voivodeships + 314 land + 66 city counties (pl.wiki Lista powiatow w Polsce) |
| Portugal | PT | 20 | 20 | 328 | 2 | administrative: autonomous_region / district > municipality | municipality | ← | Complete — 20 districts/regions + 308 municipalities (List of municipalities of Portugal) |
| Puerto Rico | PR | 78 | 78 | 979 | 2 | administrative: municipality > barrio / barrio_pueblo | barrio | US → | Complete — 78 municipios + 901 barrios (827 + 74 pueblos; Census 2024 Gazetteer; subbarrios out of scope) |
| Qatar | QA | 8 | 8 | 98 | 2 | administrative: municipality > zone | zone | none | Complete — 8 municipalities + 90 zones; PSA 2020 names; numbers 8–11, 59, 87–89 unassigned |
| Reunion | RE | 4 | 4 | 28 | 2 | administrative: district > commune | commune | ← | Complete — 4 districts + 24 communes (Communes of Réunion) |
| Romania | RO | 42 | 42 | 3228 | 2 | administrative: department > commune / town / municipality / sector | commune | ← | Complete — 42 L1 + 2861 communes + 217 towns + 102 municipalities + 6 sectors (Breb dropped; Băneasa town; 355 parent-scoped) |
| Russia | RU | 83 | 83 | 83 | 1 | administrative: subject | — | below | L1 — 83 ISO subjects; tier-2 parked pending GAR extract (see doc 17 research log) |
| Rwanda | RW | 5 | 5 | 35 | 2 | administrative: province / city > district | district | none | Complete — 5 provinces/city + 30 districts (Districts of Rwanda) |
| Saint Barthelemy | BL | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity; no tier-2|
| Saint Helena | SH | 10 | 10 | 10 | 1 | administrative: district / island | — | → | L1 — 8 districts + Ascension/Tristan da Cunha (added, provisional states); all terminal |
| Saint Kitts and Nevis | KN | 2 | 2 | 108 | 3 | administrative: island > parish > village | village | below | Complete — 2 islands + 14 parishes + 92 villages |
| Saint Lucia | LC | 10 | 10 | 10 | 1 | administrative: district | — | → | L1 — no admin tier-2; districts terminal|
| Saint Martin | MF | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity; no tier-2|
| Saint Pierre and Miquelon | PM | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity; no tier-2|
| Saint Vincent and the Grenadines | VC | 6 | 6 | 6 | 1 | administrative: parish | — | below | L1 — no admin tier-2; 6 parishes terminal|
| Samoa | WS | 11 | 11 | 353 | 2 | administrative: district > village | village | → | Complete — 11 districts + 342 census villages (Matautu/Mulivai twins disambiguated; 12 parent-scoped) |
| San Marino | SM | 9 | 9 | 9 | 1 | administrative: municipality | — | ← | L1 — no admin tier-2; L1 already municipal level |
| Sao Tome and Principe | ST | 7 | 7 | 7 | 1 | administrative: district / autonomous_region | — | none | L1 — 6 districts + Príncipe AR; districts terminal (localidades not admin) |
| Saudi Arabia | SA | 13 | 13 | 152 | 2 | administrative: region > governorate | governorate | above | Complete — 13 regions + 139 governorates (per-region tables incl. ill rows; = infobox 139) |
| Senegal | SN | 14 | 14 | 60 | 2 | administrative: region > department | department | ← | Complete — 14 regions + 46 departments (Departments of Senegal) |
| Serbia | RS | 32 | 32 | 189 | 2 | administrative: city / district / province > municipality / city / city_municipality | municipality | ← | Complete — 32 districts + 117 municipalities + 23 cities + 17 Belgrade city-municipalities (Municipalities and cities of Serbia; Kosovo under XK) |
| Seychelles | SC | 27 | 27 | 27 | 1 | administrative: district | — | none | L1 — 27 districts, only tier (terminal, no L2) |
| Sierra Leone | SL | 5 | 5 | 21 | 2 | administrative: province / area > district | district | none | Complete — 5 provinces + 16 districts (District/Province table) |
| Singapore | SG | 5 | 5 | 174 | dual 2+2 | postal: postal_district > postal_sector; administrative: region > planning_area | planning_area, postal_district, postal_sector, region | after country | Complete; postcodes via OneMap |
| Slovakia | SK | 8 | 8 | 87 | 2 | administrative: region > district | district | ← | Complete — 8 regions + 79 districts (Districts of Slovakia) |
| Slovenia | SI | 212 | 212 | 212 | 1 | administrative: municipality / urban_municipality | — | ← | L1 — 200 municipalities + 12 urban (grouped role); no admin tier-2 |
| Solomon Islands | SB | 10 | 10 | 193 | 2 | administrative: province > ward | ward | none | Complete — 9 provinces + Honiara + 183 wards (OCHA COD gazetteer w/ SINSO pcodes; Statoids cross-check) |
| Somalia | SO | 18 | 18 | 107 | 2 | administrative: region > district | district | none | Complete — 18 regions + 89 districts (Region/Districts table (formal claim incl. Somaliland)) |
| South Africa | ZA | 9 | 9 | 61 | 2 | administrative: province > district_municipality / city_municipality | municipality | below | Complete — 9 provinces + 44 districts + 8 metros (List of municipalities in South Africa) |
| South Korea | KR | 17 | 17 | 245 | 2 | administrative: special_city / metropolitan_city / province / special_self_governing_province / special_self_governing_city > city / county / district | sigungu | → | Complete — 17 first-level + 77 cities + 82 counties + 69 autonomous districts (si/gun/gu lists; non-autonomous gu excluded; Sejong childless) |
| South Sudan | SS | 10 | 10 | 98 | 2 | administrative: state > county | county | none | Complete — 10 states + 88 counties (per-state bullets + WBG table; Ruweng->Unity, Pibor->Jonglei) |
| Spain | ES | 19 | 19 | 69 | 2 | administrative: autonomous_community / autonomous_city > province | province | ← | Complete — 17 communities + Ceuta/Melilla + 50 provinces |
| Sri Lanka | LK | 9 | 9 | 34 | 2 | administrative: province > district | district | below | Complete — 9 provinces + 25 districts |
| Sudan | SD | 18 | 18 | 206 | 2 | administrative: state > district | district | above | Complete — 18 states + 188 districts (UN OCHA; Aj Jazirah/Gedaref mapped; Abyei PCA excluded) |
| Suriname | SR | 10 | 10 | 73 | 2 | administrative: district > resort | resort | none | Complete — 10 districts + 63 resorts (Resorts of Suriname) |
| Sweden | SE | 21 | 21 | 311 | 2 | administrative: county > municipality | municipality | ← | Complete — 21 counties + 290 municipalities (List of municipalities of Sweden; codes) |
| Switzerland | CH | 26 | 26 | 172 | 2 | administrative: canton > district | district | ← | Complete — 26 cantons + 146 districts (Districts of Switzerland; 7 cantons have no district tier) |
| Syria | SY | 14 | 14 | 80 | 2 | administrative: province > district | district | none | Complete — 14 provinces + 66 districts (per-governorate bullets (intro 65 stale; +Shaddadah; Damascus self)) |
| Taiwan | TW | 22 | 22 | 390 | 2 | administrative: division > district / township / city | district | → | Complete — 22 divisions + 368 units (8-digit codes + native names; 12 parent-scoped) |
| Tajikistan | TJ | 5 | 5 | 74 | 2 | administrative: capital_territory / autonomous_region / region / districts_under_republic_administration > district / city | district | ← | Complete — 5 first-level + 51 districts + 18 cities (per-region tables w/ 2024 ests; intro 58/17 stale) |
| Tanzania | TZ | 31 | 31 | 224 | 2 | administrative: region > district | district | ← | Complete — 31 regions + 193 districts (post-2021 splits; Nanyamba Town rename) |
| Thailand | TH | 78 | 78 | 1006 | 2 | administrative: province > amphoe | amphoe | below | Complete — 76 provinces + Bangkok/Pattaya + 928 (878 amphoe + 50 khet, DOPA geocoded; Mueang restored; Bueng Kan recoded; Pattaya terminal) |
| Timor-Leste | TL | 14 | 14 | 81 | 2 | administrative: municipality / special_administrative_region > administrative_post | administrative_post | → | Complete — 14 municipalities + 67 administrative posts (Source: Wikipedia Administrative posts of Timor-Leste (MediaWiki API, Sep 2026). 13 of 14 municipalities have posts; Atauro has none.) |
| Togo | TG | 5 | 5 | 44 | 2 | administrative: region > prefecture | prefecture | none | Complete — 5 regions + 39 prefectures (Prefectures of Togo) |
| Tonga | TO | 5 | 5 | 28 | 2 | administrative: division > district | district | none | Complete — 5 divisions + 23 districts (ISO codes; Ha'ano TO-025 fixes table dup) |
| Trinidad and Tobago | TT | 15 | 15 | 15 | 1 | administrative: region / borough / city / ward | — | → | L1 — 7 regions + 5 boroughs + 2 cities + Tobago ward; no admin tier-2 |
| Tunisia | TN | 24 | 24 | 303 | 2 | administrative: governorate > delegation | delegation | ← | Complete — 24 governorates + 279 delegations (INS 2024) |
| Turkmenistan | TM | 6 | 6 | 64 | 2 | administrative: region / city > district | district | below | Complete — 6 regions + 58 districts (per-province tables incl. Sep-2025 reestablishments) |
| Turks and Caicos | TC | 6 | 6 | 6 | 1 | administrative: district | — | below | L1 — no admin tier-2; 6 districts terminal|
| Tuvalu | TV | 8 | 8 | 8 | 1 | administrative: island_council / town_council | — | none | L1 — councils are the local government (Falekaupule Act; Funafuti town council grouped); villages no admin function |
| Türkiye | TR | 81 | 81 | 1054 | 2 | administrative: province > district | district | ← | Complete — 81 provinces + 973 districts; 51 Merkez; Ereğli twins; no district codes |
| Uganda | UG | 4 | 4 | 150 | 2 | administrative: region > district | district | ← | Complete — 4 regions + 135 districts + 11 cities (UBOS 2024 census; stable since Jul 2020; 5 unfunded cities excluded) |
| Ukraine | UA | 27 | 27 | 163 | 2 | administrative: oblast / city / republic > raion | raion | below | Complete — 27 regions + 136 raions incl. Crimea 10 (Raions of Ukraine; Kyiv/Sevastopol cities have none) |
| United Arab Emirates | AE | 7 | 7 | 7 | 1 | administrative: emirate | — | none | L1; no official tier-2 |
| United Kingdom | GB | 4 | 4 | 117 | 2 | administrative: nation > county / council_area / county_borough / district | county | below | Complete — 4 nations + 48 ENG ceremonial counties + 32 SCT council areas + 11 WLS counties + 11 WLS county boroughs + 11 NI districts |
| United States | US | 56 | 56 | 3199 | 2 | administrative: state > county | county | US → | Complete — 56 + 3,143 counties (Census flavors); PR/DC exclusions; AA/AE/AP/UM global-only |
| US Minor Outlying Islands | UM | 9 | 9 | 9 | 1 | administrative: island | — | none | L1 — uninhabited; 9 islands terminal|
| US Virgin Islands | VI | 3 | 3 | 23 | 2 | administrative: district > subdistrict | subdistrict | US → | Complete — 3 districts + 20 subdistricts (Source: Wikipedia Districts and sub-districts of the USVI (MediaWiki API, Sep 2026). 20 census subdistricts; town sub-rows excluded; East End parent-scoped.) |
| Uruguay | UY | 19 | 19 | 144 | 2 | administrative: department > municipality | municipality | ← | Complete — 19 departments + 125 municipalities (Source: Wikipedia Municipalities of Uruguay (MediaWiki API, Sep 2026); official 125 total.) |
| Uzbekistan | UZ | 14 | 14 | 220 | 2 | administrative: region / republic / city > tuman / city | tuman | ← | Complete — 12 regions + republic + city; 175 tumanlar + 31 regional-subordination cities; ASCII apostrophes; Namangan city districts excluded (L3) |
| Vanuatu | VU | 6 | 6 | 69 | 2 | administrative: province > area_council | area_council | none | Complete — 6 provinces + 60 area councils + 3 municipalities (HASC-coded; Lenakel added; municipalities parented geographically) |
| Venezuela | VE | 25 | 25 | 360 | 2 | administrative: state / capital_district / federal_dependency > municipality | municipality | → | Complete — 25 states + 335 municipalities (Source: Wikipedia Municipalities of Venezuela (MediaWiki API, Sep 2026). Vargas section mapped to La Guaira; Dependencias Federales has none.) |
| Vietnam | VN | 34 | 34 | 3355 | 2 | administrative: province / municipality > commune / ward / special_zone | commune | → | Complete — 34 provinces/municipalities + 3321 commune-level units (2599 + 709 + 13; GSO list service, post-2026 typing) |
| Wallis and Futuna | WF | 3 | 3 | 6 | 2 | administrative: administrative_precinct > district | district | ← | Complete — 3 kingdoms + 3 Uvea districts (Alo/Sigave childless) |
| Yemen | YE | 22 | 22 | 355 | 2 | administrative: governorate / municipality > district | district | none | Complete — 22 governorates + 333 districts (per-governorate bullets) |
| Zambia | ZM | 10 | 10 | 126 | 2 | administrative: province > district | district | → | Complete — 10 provinces + 116 districts (per-province lists) |
| Zimbabwe | ZW | 10 | 10 | 74 | 2 | administrative: province > district | district | none | Complete — 10 provinces + 64 districts (per-province bullets (Harare suburbs excluded; Bulawayo/Harare self)) |

## Not Covered

21 countries have no subdivisions in the source data, so no provider
or formatter. Uninhabited territories need nothing further; the rest
are candidates when a subdivision source appears. Mail routes noted
where verified (UPU Sep-2025 database).

| Country | Code | Note |
|---|---|---|
| Antarctica | AQ | uninhabited |
| Bouvet Island | BV | uninhabited |
| Cocos (Keeling) Islands | CC | mail via AU 6799 |
| Cook Islands | CK | no subdivision source |
| Curaçao | CW | no subdivision source |
| Christmas Island | CX | mail via AU 6798 |
| Western Sahara | EH | disputed; no subdivision source |
| Falkland Islands | FK | mail via UK FIQQ 1ZZ |
| Gibraltar | GI | no subdivision source |
| South Georgia | GS | no permanent population |
| Heard Island and McDonald Islands | HM | uninhabited |
| British Indian Ocean Territory | IO | mail via UK BBND 1ZZ |
| Macao | MO | city-state; served without provider |
| Northern Mariana Islands | MP | no subdivision source |
| Norfolk Island | NF | mail via AU 2899 |
| Pitcairn Islands | PN | no subdivision source |
| Svalbard and Jan Mayen | SJ | no subdivision source; mail via Norwegian system |
| Sint Maarten | SX | no subdivision source |
| Tokelau | TK | no subdivision source |
| Vatican City | VA | city-state; mail via IT 00120 |
| British Virgin Islands | VG | no subdivision source |

## Maintaining This File

Each column traces to one place:

- **States** — count of `['name' =>` entries in the provider's
  `stateDefinitions()`.
- **Mapped** — entries in the provider's `stateAreaMappings()`.
- **Rows** — data rows in `resources/geography/<slug>-address-areas.csv`.
- **Depth / Hierarchy / Roles** — the provider's `addressHierarchies()`.
- **Status** — hand-curated. Keep it to one line: what is bundled,
  the next researched tier (`candidate`), or the reason deeper data
  is excluded (`out of scope`, `blocked`, or the volatility note).
- **Fmt** — the formatter's `// UPU:` comment, classified per the
  Legend; keep the shape-line totals in sync.
- **Not Covered** — re-check states.json when the source refreshes;
  a country graduates by gaining subdivisions.

When a batch adds a level, update this row, the country's section in
[Country Data](./05-country-data.md), and the CSV line in
`resources/geography/README.md` in the same pass.
