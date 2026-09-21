---
title: Provider Coverage Registry
---

# Provider Coverage Registry

At-a-glance depth and dataset status for every bundled geography
provider. Update the row whenever a provider gains a level, changes
counts, or changes scope. See [Country Data](./05-country-data.md)
for per-country narrative and [Provider Authoring](./13-provider-authoring.md)
for how to add a level.

Current shape: 229 providers — 2 dual-hierarchy, 1 depth-3, 26 depth-2,
200 depth-1.
All 229 providers ship a formatter: 181 print a postcode (←99 →39 US →8 above6 below28 after country1), 48 codeless.

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
  above/below the locality, `after country` code after the
  country line (Singapore), `none` no postcode system (a supplied
  code prints on its own line).
- **Status** — `Complete` (at designed depth), `L1` (level-1 only),
  plus scope notes. `candidate` marks a researched next tier;
  `out of scope` marks a deliberate exclusion.

## Registry

| Country | Code | States | Mapped | Rows | Depth | Hierarchy | Roles | Fmt | Status |
|---|---|---:|:---:|:---:|:---:|---|---|---|---|
| Afghanistan | AF | 34 | 34 | 34 | 1 | administrative: province | — | ← | L1; districts (~400) blocked, stale data |
| Aland | AX | 16 | 16 | 16 | 1 | administrative: municipality | — | ← | L1 — 16 municipalities |
| Albania | AL | 12 | 12 | 12 | 1 | administrative: county | — | above | L1 |
| Algeria | DZ | 69 | 69 | 617 | 2 | administrative: wilaya > daira | daira | ← | Complete — 69 wilayas + 548 dairas per décrets 91-306/26-253; communes excluded |
| American Samoa | AS | 5 | 5 | 5 | 1 | administrative: district | — | US → | L1 — 3 districts + 2 atolls |
| Andorra | AD | 7 | 7 | 7 | 1 | administrative: parish | — | ← | L1 |
| Angola | AO | 18 | 18 | 18 | 1 | administrative: province | — | none | L1; 2024 split unimplemented |
| Anguilla | AI | 14 | 14 | 14 | 1 | administrative: district | — | below | L1 |
| Antigua and Barbuda | AG | 8 | 8 | 8 | 1 | administrative: parish | — | none | L1 — 6 parishes + Barbuda/Redonda |
| Argentina | AR | 24 | 24 | 24 | 1 | administrative: province | — | ← | L1 — 23 provinces + CABA |
| Armenia | AM | 11 | 11 | 11 | 1 | administrative: region | — | ← | L1 — 10 regions + Yerevan |
| Aruba | AW | 9 | 9 | 9 | 1 | administrative: region | — | none | L1 — 8 regions + Oranjestad |
| Australia | AU | 8 | 8 | 8 | 1 | administrative: state | — | → | L1 — 6 states + 2 territories; externals global-only |
| Austria | AT | 9 | 9 | 9 | 1 | administrative: state | — | ← | L1 — 9 states |
| Azerbaijan | AZ | 78 | 78 | 78 | 1 | administrative: district | — | ← | L1 — 66 districts + 11 municipalities + Nakhchivan AR |
| Bahamas | BS | 32 | 32 | 32 | 1 | administrative: district | — | none | L1 — 31 districts + New Providence |
| Bahrain | BH | 4 | 4 | 4 | 1 | administrative: governorate | — | → | L1; no admin tier-2, blocks are postal |
| Bangladesh | BD | 72 | 8 | 72 | 2 | administrative: division > district | district | → | Complete to district; upazilas out of scope |
| Barbados | BB | 11 | 11 | 11 | 1 | administrative: parish | — | → | L1 |
| Belarus | BY | 7 | 7 | 7 | 1 | administrative: oblast | — | ← | L1 — 6 oblasts + Minsk; oblast/city Minsk share a name |
| Belgium | BE | 3 | 3 | 13 | 2 | administrative: region > province | province | ← | Complete — 3 regions + 10 provinces; Brussels childless |
| Belize | BZ | 6 | 6 | 6 | 1 | administrative: district | — | none | L1 |
| Benin | BJ | 12 | 12 | 12 | 1 | administrative: department | — | none | L1 |
| Bermuda | BM | 9 | 9 | 9 | 1 | administrative: municipality | — | → | L1 |
| Bhutan | BT | 20 | 20 | 20 | 1 | administrative: district | — | → | L1 — 20 dzongkhags; gewogs out of scope |
| Bolivia | BO | 9 | 9 | 9 | 1 | administrative: department | — | none | L1 |
| Bosnia and Herzegovina | BA | 3 | 3 | 3 | 1 | administrative: entity | — | ← | L1 — 2 entities + Brčko District |
| Botswana | BW | 16 | 16 | 16 | 1 | administrative: district | — | none | L1 — 10 districts + 2 cities + 4 towns |
| Brazil | BR | 27 | 27 | 27 | 1 | administrative: state | — | below | L1 — 26 states + DF |
| Brunei | BN | 4 | 4 | 43 | 2 | administrative: district > mukim | mukim | → | Complete; kampungs via components |
| Bulgaria | BG | 28 | 28 | 28 | 1 | administrative: district | — | ← | L1 |
| Burkina Faso | BF | 64 | 64 | 64 | 1 | administrative: region | — | ← | L1 — 17 regions + 47 provinces flat (July 2025 reform); region codes 14–17 + KAR/DYA provisional |
| Burundi | BI | 5 | 5 | 5 | 1 | administrative: province | — | none | L1 — 5 provinces (July 2025 reform); codes 01-05 provisional pending ISO |
| Cambodia | KH | 25 | 25 | 25 | 1 | administrative: province | — | → | L1 — 24 provinces + Phnom Penh; districts/communes out of scope |
| Cameroon | CM | 10 | 10 | 10 | 1 | administrative: region | — | none | L1 |
| Canada | CA | 13 | 13 | 13 | 1 | administrative: province | — | → | L1 — 10 provinces + 3 territories |
| Cape Verde | CV | 24 | 24 | 24 | 1 | administrative: municipality | — | ← | L1 — 22 municipalities + 2 island groups |
| Caribbean Netherlands | BQ | 3 | 3 | 3 | 1 | administrative: special_municipality | — | none | L1 — 3 special municipalities |
| Cayman Islands | KY | 3 | 3 | 3 | 1 | administrative: island | — | → | L1 — 3 islands |
| Central African Republic | CF | 17 | 17 | 17 | 1 | administrative: prefecture | — | none | L1 — 15 prefectures + Bangui + Nana-Grébizi |
| Chad | TD | 23 | 23 | 23 | 1 | administrative: province | — | none | L1 |
| Chile | CL | 16 | 16 | 16 | 1 | administrative: region | — | ← | L1 |
| China | CN | 33 | 33 | 33 | 1 | administrative: province | — | ← | L1; Taiwan separate (TW) |
| Colombia | CO | 33 | 33 | 33 | 1 | administrative: department | — | → | L1 — 32 departments + Bogotá |
| Comoros | KM | 3 | 3 | 3 | 1 | administrative: island | — | none | L1 — 3 islands |
| Congo | CG | 12 | 12 | 12 | 1 | administrative: department | — | none | L1 |
| Costa Rica | CR | 7 | 7 | 7 | 1 | administrative: province | — | below | L1 |
| Croatia | HR | 21 | 21 | 21 | 1 | administrative: county | — | ← | L1 — 20 counties + City of Zagreb |
| Cuba | CU | 16 | 16 | 16 | 1 | administrative: province | — | ← | L1 — 15 provinces + Isla de la Juventud |
| Cyprus | CY | 6 | 6 | 6 | 1 | administrative: district | — | ← | L1 |
| Czech Republic | CZ | 14 | 14 | 90 | 2 | administrative: region > district | district | ← | Complete — 13 regions + Praha + 76 districts; Praha childless |
| Denmark | DK | 5 | 5 | 5 | 1 | administrative: region | — | ← | L1 |
| Djibouti | DJ | 6 | 6 | 6 | 1 | administrative: region | — | ← | L1 — 5 regions + Djibouti City |
| Dominica | DM | 10 | 10 | 10 | 1 | administrative: parish | — | none | L1 |
| Dominican Republic | DO | 10 | 10 | 42 | 2 | administrative: region > province | province | ← | Complete — 10 regions + 31 provinces + DN; DN under Ozama |
| DR Congo | CD | 26 | 26 | 26 | 1 | administrative: province | — | ← | L1 |
| Ecuador | EC | 24 | 24 | 24 | 1 | administrative: province | — | ← | L1 |
| Egypt | EG | 27 | 27 | 27 | 1 | administrative: governorate | — | below | L1; markaz (~350) candidate, Arabic-first |
| El Salvador | SV | 14 | 14 | 14 | 1 | administrative: department | — | ← | L1 |
| Equatorial Guinea | GQ | 2 | 2 | 10 | 2 | administrative: region > province | province | none | Complete — 2 regions + 8 provinces |
| Eritrea | ER | 6 | 6 | 6 | 1 | administrative: region | — | none | L1 |
| Estonia | EE | 15 | 15 | 93 | 2 | administrative: county > municipality | municipality | ← | Complete — 15 counties + 78 municipalities (Toila merged into Jõhvi 2025) |
| Eswatini | SZ | 4 | 4 | 4 | 1 | administrative: region | — | below | L1 |
| Ethiopia | ET | 14 | 14 | 14 | 1 | administrative: region | — | ← | L1; SN dissolved on seed |
| Faroe Islands | FO | 6 | 6 | 6 | 1 | administrative: region | — | ← | L1 — 6 regions |
| Fiji | FJ | 5 | 5 | 19 | 2 | administrative: division > province | province | none | Complete — 4 divisions + Rotuma + 14 provinces; Rotuma standalone |
| Finland | FI | 18 | 18 | 18 | 1 | administrative: region | — | ← | L1 |
| France | FR | 18 | 18 | 18 | 1 | administrative: region | — | ← | L1 |
| French Guiana | GF | 1 | 1 | 1 | 1 | administrative: overseas_region | — | ← | L1 — single region |
| French Polynesia | PF | 5 | 5 | 5 | 1 | administrative: division | — | ← | L1 — 5 divisions |
| French Southern Territories | TF | 5 | 5 | 5 | 1 | administrative: district | — | none | L1 — 5 districts; uninhabited |
| Gabon | GA | 9 | 9 | 9 | 1 | administrative: province | — | ← | L1 |
| Gambia | GM | 6 | 6 | 6 | 1 | administrative: division | — | none | L1 — 5 divisions + Banjul |
| Georgia | GE | 12 | 12 | 12 | 1 | administrative: region | — | ← | L1 — 9 regions + 2 ARs + Tbilisi |
| Germany | DE | 16 | 16 | 16 | 1 | administrative: state | — | ← | L1 |
| Ghana | GH | 16 | 16 | 16 | 1 | administrative: region | — | → | L1 |
| Greece | GR | 14 | 14 | 14 | 1 | administrative: administrative_region | — | ← | L1 — 13 regions + Mount Athos |
| Greenland | GL | 5 | 5 | 5 | 1 | administrative: municipality | — | ← | L1 — 5 municipalities |
| Grenada | GD | 7 | 7 | 7 | 1 | administrative: parish | — | none | L1 — 6 parishes + Carriacou |
| Guadeloupe | GP | 2 | 2 | 2 | 1 | administrative: district | — | ← | L1 — 2 districts |
| Guam | GU | 19 | 19 | 19 | 1 | administrative: village | — | US → | L1 — 19 villages |
| Guatemala | GT | 22 | 22 | 22 | 1 | administrative: department | — | ← | L1 |
| Guernsey | GG | 12 | 12 | 12 | 1 | administrative: parish | — | below | L1 — 12 parishes |
| Guinea | GN | 8 | 8 | 41 | 2 | administrative: administrative_region > prefecture | prefecture | ← | Complete — 7 regions + Conakry + 33 prefectures; Conakry childless |
| Guinea-Bissau | GW | 12 | 12 | 12 | 1 | administrative: province | — | ← | L1 — 3 provinces + 8 regions + Bissau sector |
| Guyana | GY | 10 | 10 | 10 | 1 | administrative: region | — | below | L1 |
| Haiti | HT | 10 | 10 | 10 | 1 | administrative: department | — | ← | L1 |
| Honduras | HN | 18 | 18 | 18 | 1 | administrative: department | — | ← | L1 |
| Hong Kong | HK | 18 | 18 | 18 | 1 | administrative: district | — | none | L1 — 18 districts |
| Hungary | HU | 43 | 43 | 43 | 1 | administrative: county | — | ← | L1 — 20 counties + 22 county-rights cities + Budapest |
| Iceland | IS | 72 | 72 | 72 | 1 | administrative: region | — | ← | L1 — 8 regions + 64 municipalities flat |
| India | IN | 36 | 36 | 822 | 2 | administrative: state > district | district | below | Complete — 28 states + 8 UTs + 786 districts (LGD 31 May 2026 + Mahe/Yanam legacy codes); post-2011 splits/renames with aliases; Ladakh 5 + Kalyan Singh Nagar excluded (no LGD codes) |
| Indonesia | ID | 38 | 38 | 7837 | 4 | administrative: province > regency > district > village | district, regency | → | Complete to kecamatan; desa/kelurahan (83,762) opt-in via `geography.indonesia.villages` |
| Iran | IR | 31 | 31 | 31 | 1 | administrative: province | — | below | L1 — 31 ostans; counties out of scope |
| Iraq | IQ | 19 | 19 | 19 | 1 | administrative: governorate | — | below | L1 incl. Halabja; KR region removed; qada (~120) candidate |
| Ireland | IE | 4 | 4 | 30 | 2 | administrative: province > county | county | below | Complete — 4 provinces + 26 counties |
| Isle of Man | IM | 6 | 6 | 6 | 1 | administrative: sheadings | — | below | L1 — 6 sheadings |
| Israel | IL | 6 | 6 | 6 | 1 | administrative: district | — | ← | L1 — 6 districts; sub-districts out of scope |
| Italy | IT | 20 | 20 | 20 | 1 | administrative: region | — | ← | L1 |
| Ivory Coast | CI | 14 | 14 | 14 | 1 | administrative: district | — | none | L1 — 12 districts + Abidjan/Yamoussoukro |
| Jamaica | JM | 14 | 14 | 14 | 1 | administrative: parish | — | none | L1 |
| Japan | JP | 47 | 47 | 1794 | 2 | administrative: prefecture > municipality | municipality | below | Complete — 47 prefectures + 1,747 municipalities (792 cities + 743 towns + 183 villages + 23 Tokyo special wards + 6 Northern-Territories paper villages); designated-city wards out of scope |
| Jersey | JE | 12 | 12 | 12 | 1 | administrative: parish | — | below | L1 — 12 parishes |
| Jordan | JO | 12 | 12 | 63 | 2 | administrative: governorate > liwa | liwa | → | Complete — 12 governorates + 51 liwa per DOS Yearbook 2024; qada out of scope |
| Kazakhstan | KZ | 20 | 20 | 20 | 1 | administrative: region | — | ← | L1 — 17 regions + 3 cities |
| Kenya | KE | 47 | 47 | 47 | 1 | administrative: county | — | below | L1 |
| Kiribati | KI | 3 | 3 | 3 | 1 | administrative: island | — | → | L1 — 3 island groups |
| Kosovo | XK | 7 | 7 | 7 | 1 | administrative: district | — | ← | L1 |
| Kuwait | KW | 6 | 6 | 140 | 2 | administrative: governorate > area | area | ← | Complete — 6 governorates + 134 areas; blocks and per-area postcodes out of scope |
| Kyrgyzstan | KG | 9 | 9 | 9 | 1 | administrative: region | — | ← | L1 — 7 regions + Bishkek/Osh |
| Laos | LA | 18 | 18 | 18 | 1 | administrative: province | — | ← | L1 — 17 provinces + Vientiane Prefecture; muang out of scope |
| Latvia | LV | 43 | 43 | 43 | 1 | administrative: municipality | — | → | L1 — 36 municipalities + 7 state cities; 3 twins share names |
| Lebanon | LB | 8 | 8 | 8 | 1 | administrative: governorate | — | → | L1 — 8 governorates; cazas out of scope |
| Lesotho | LS | 10 | 10 | 10 | 1 | administrative: district | — | → | L1 |
| Liberia | LR | 15 | 15 | 15 | 1 | administrative: county | — | ← | L1 |
| Libya | LY | 22 | 22 | 22 | 1 | administrative: popularate | — | none | L1 — 22 sha'biyat |
| Liechtenstein | LI | 11 | 11 | 11 | 1 | administrative: commune | — | ← | L1 |
| Lithuania | LT | 70 | 70 | 70 | 1 | administrative: county | — | ← | L1 — 10 counties + 60 municipalities flat; 4 city/district twins suffixed by code |
| Luxembourg | LU | 12 | 12 | 12 | 1 | administrative: canton | — | ← | L1 |
| Madagascar | MG | 6 | 6 | 6 | 1 | administrative: province | — | ← | L1; codeless 23 regions omitted |
| Malawi | MW | 3 | 3 | 31 | 2 | administrative: region > district | district | ← | Complete — 3 regions + 28 districts |
| Malaysia | MY | 16 | 16 | 1782 | dual 2+4 | postal: region > locality; administrative: region > division > district > subdivision | administrative_district, administrative_division, administrative_subdivision, postal_locality | ← | Complete; postal CSVs bundled; KL + Selangor + Pahang + Johor + Perlis + Melaka + Penang + Terengganu + Perak + Kedah + Kelantan per JUPEM UPI + N.Sembilan per PLANMalaysia/gazettes + Sabah + Sarawak per gazettes/SPR/DOSM |
| Maldives | MV | 21 | 21 | 21 | 1 | administrative: atoll | — | → | L1 — 20 atolls + Addu City |
| Mali | ML | 11 | 11 | 11 | 1 | administrative: region | — | none | L1 — 10 regions + Bamako |
| Malta | MT | 68 | 68 | 68 | 1 | administrative: local_council | — | below | L1 — 68 local councils |
| Marshall Islands | MH | 26 | 26 | 26 | 1 | administrative: municipality | — | US → | L1 — 24 municipalities + 2 chains |
| Martinique | MQ | 4 | 4 | 4 | 1 | administrative: district | — | ← | L1 — 4 districts |
| Mauritania | MR | 15 | 15 | 15 | 1 | administrative: region | — | none | L1 |
| Mauritius | MU | 12 | 12 | 12 | 1 | administrative: district | — | → | L1 — 9 districts + 3 dependencies |
| Mayotte | YT | 17 | 17 | 17 | 1 | administrative: commune | — | ← | L1 — 17 communes |
| Mexico | MX | 32 | 32 | 32 | 1 | administrative: state | — | ← | L1 |
| Micronesia | FM | 4 | 4 | 4 | 1 | administrative: state | — | US → | L1 — 4 states |
| Moldova | MD | 37 | 37 | 37 | 1 | administrative: district | — | ← | L1 — 32 districts + 3 cities + Gagauzia/Transnistria |
| Monaco | MC | 17 | 17 | 17 | 1 | administrative: quarter | — | ← | L1 — 17 quarters |
| Mongolia | MN | 22 | 22 | 22 | 1 | administrative: province | — | → | L1 — 21 aimags + Ulaanbaatar |
| Montenegro | ME | 25 | 25 | 25 | 1 | administrative: municipality | — | ← | L1 |
| Montserrat | MS | 3 | 3 | 3 | 1 | administrative: parish | — | → | L1 — 3 parishes |
| Morocco | MA | 87 | 12 | 87 | 2 | administrative: region > province | province | ← | Complete to province; communes (~1,500) out of scope |
| Mozambique | MZ | 11 | 11 | 11 | 1 | administrative: province | — | ← | L1 — 10 provinces + Maputo City |
| Myanmar | MM | 15 | 15 | 15 | 1 | administrative: region | — | → | L1 — 7 regions + 7 states + Naypyidaw |
| Namibia | NA | 14 | 14 | 14 | 1 | administrative: region | — | below | L1 |
| Nauru | NR | 14 | 14 | 14 | 1 | administrative: district | — | below | L1 |
| Nepal | NP | 7 | 7 | 7 | 1 | administrative: province | — | → | L1 — 7 provinces; districts out of scope |
| Netherlands | NL | 12 | 12 | 12 | 1 | administrative: province | — | ← | L1 |
| New Caledonia | NC | 3 | 3 | 3 | 1 | administrative: province | — | ← | L1 — 3 provinces |
| New Zealand | NZ | 17 | 17 | 17 | 1 | administrative: region | — | ← | L1 — 16 regions + Chatham Islands |
| Nicaragua | NI | 17 | 17 | 17 | 1 | administrative: department | — | above | L1 — 15 departments + 2 autonomous regions |
| Niger | NE | 8 | 8 | 8 | 1 | administrative: region | — | ← | L1 — 7 regions + Niamey |
| Nigeria | NG | 37 | 37 | 811 | 2 | administrative: state > lga | lga | → | Complete — 37 states + 768 LGAs + 6 FCT area councils; post-2023 names; LCDAs excluded |
| Niue | NU | 14 | 14 | 14 | 1 | administrative: village | — | → | L1 — 14 villages |
| North Korea | KP | 13 | 13 | 13 | 1 | administrative: province | — | none | L1 — 9 provinces + 4 cities; no postcode |
| North Macedonia | MK | 80 | 80 | 80 | 1 | administrative: municipality | — | ← | L1 — 80 municipalities |
| Norway | NO | 17 | 17 | 17 | 1 | administrative: county | — | ← | L1 — 15 counties + Svalbard/Jan Mayen |
| Oman | OM | 11 | 11 | 74 | 2 | administrative: governorate > wilayat | wilayat | above | Complete — 11 governorates + 63 wilayats |
| Pakistan | PK | 7 | 7 | 181 | 2 | administrative: province > district | district | → | Complete to district (174, late-2025); tehsils out; 2026 Balochistan batch excluded |
| Palau | PW | 16 | 16 | 16 | 1 | administrative: state | — | US → | L1 — 16 states |
| Palestine | PS | 16 | 16 | 16 | 1 | administrative: governorate | — | → | L1 |
| Panama | PA | 14 | 14 | 14 | 1 | administrative: province | — | none | L1 — 10 provinces + 4 comarcas |
| Papua New Guinea | PG | 22 | 22 | 22 | 1 | administrative: province | — | → | L1 — 20 provinces + Bougainville + Port Moresby |
| Paraguay | PY | 18 | 18 | 18 | 1 | administrative: department | — | ← | L1 — 17 departments + Asunción |
| Peru | PE | 26 | 26 | 26 | 1 | administrative: region | — | above | L1 — 25 regions + Lima |
| Philippines | PH | 99 | 82 | 82 | 1 | administrative: province | — | ← | L1 — provinces; regions global-only; municipalities/barangay out of scope |
| Poland | PL | 16 | 16 | 16 | 1 | administrative: voivodeship | — | ← | L1 |
| Portugal | PT | 20 | 20 | 20 | 1 | administrative: district | — | ← | L1 — 18 districts + Azores/Madeira |
| Puerto Rico | PR | 78 | 78 | 78 | 1 | administrative: municipality | — | US → | L1 — 78 municipios (10 typed region in source) |
| Qatar | QA | 8 | 8 | 98 | 2 | administrative: municipality > zone | zone | none | Complete — 8 municipalities + 90 zones; PSA 2020 names; numbers 8–11, 59, 87–89 unassigned |
| Reunion | RE | 4 | 4 | 4 | 1 | administrative: district | — | ← | L1 — 4 districts |
| Romania | RO | 42 | 42 | 42 | 1 | administrative: department | — | ← | L1 — 41 departments + Bucharest |
| Russia | RU | 83 | 83 | 83 | 1 | administrative: subject | — | below | L1 |
| Rwanda | RW | 5 | 5 | 5 | 1 | administrative: province | — | none | L1 — 4 provinces + Kigali |
| Saint Barthelemy | BL | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity |
| Saint Helena | SH | 8 | 8 | 8 | 1 | administrative: district | — | → | L1 — 8 districts |
| Saint Kitts and Nevis | KN | 2 | 2 | 16 | 2 | administrative: island > parish | parish | below | Complete — 2 islands + 14 parishes |
| Saint Lucia | LC | 10 | 10 | 10 | 1 | administrative: district | — | → | L1 |
| Saint Martin | MF | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity |
| Saint Pierre and Miquelon | PM | 1 | 1 | 1 | 1 | administrative: overseas_collectivity | — | ← | L1 — single collectivity |
| Saint Vincent and the Grenadines | VC | 6 | 6 | 6 | 1 | administrative: parish | — | below | L1 |
| Samoa | WS | 11 | 11 | 11 | 1 | administrative: district | — | → | L1 |
| San Marino | SM | 9 | 9 | 9 | 1 | administrative: municipality | — | ← | L1 — 9 municipalities |
| Sao Tome and Principe | ST | 7 | 7 | 7 | 1 | administrative: district | — | none | L1 — 6 districts + Príncipe AR |
| Saudi Arabia | SA | 13 | 13 | 13 | 1 | administrative: region | — | above | L1; governorates (~130) candidate, Arabic-first |
| Senegal | SN | 14 | 14 | 14 | 1 | administrative: region | — | ← | L1 |
| Serbia | RS | 32 | 32 | 32 | 1 | administrative: district | — | ← | L1 — 29 districts + 2 provinces + Belgrade |
| Seychelles | SC | 27 | 27 | 27 | 1 | administrative: district | — | none | L1 — 27 districts |
| Sierra Leone | SL | 5 | 5 | 5 | 1 | administrative: province | — | none | L1 — 4 provinces + Western Area |
| Singapore | SG | 5 | 5 | 174 | dual 2+2 | postal: postal_district > postal_sector; administrative: region > planning_area | planning_area, postal_district, postal_sector, region | after country | Complete; postcodes via OneMap |
| Slovakia | SK | 8 | 8 | 8 | 1 | administrative: region | — | ← | L1 |
| Slovenia | SI | 212 | 212 | 212 | 1 | administrative: municipality | — | ← | L1 — 200 municipalities + 12 urban municipalities |
| Solomon Islands | SB | 10 | 10 | 10 | 1 | administrative: province | — | none | L1 — 9 provinces + Honiara |
| Somalia | SO | 18 | 18 | 18 | 1 | administrative: region | — | none | L1 |
| South Africa | ZA | 9 | 9 | 9 | 1 | administrative: province | — | below | L1 |
| South Korea | KR | 17 | 17 | 17 | 1 | administrative: province | — | → | L1 |
| South Sudan | SS | 10 | 10 | 10 | 1 | administrative: state | — | none | L1 — 10 states |
| Spain | ES | 69 | 19 | 69 | 2 | administrative: community > province | province | ← | Complete — communities + 50 provinces |
| Sri Lanka | LK | 9 | 9 | 34 | 2 | administrative: province > district | district | below | Complete — 9 provinces + 25 districts |
| Sudan | SD | 18 | 18 | 18 | 1 | administrative: state | — | above | L1 |
| Suriname | SR | 10 | 10 | 10 | 1 | administrative: district | — | none | L1 |
| Sweden | SE | 21 | 21 | 21 | 1 | administrative: county | — | ← | L1 |
| Switzerland | CH | 26 | 26 | 26 | 1 | administrative: canton | — | ← | L1 |
| Syria | SY | 14 | 14 | 14 | 1 | administrative: province | — | none | L1 |
| Taiwan | TW | 22 | 22 | 22 | 1 | administrative: division | — | → | L1 |
| Tajikistan | TJ | 5 | 5 | 5 | 1 | administrative: region | — | ← | L1 — 2 regions + GBAR + Dushanbe + republican districts |
| Tanzania | TZ | 31 | 31 | 31 | 1 | administrative: region | — | ← | L1 |
| Thailand | TH | 78 | 78 | 78 | 1 | administrative: province | — | below | L1 — 76 provinces + Bangkok/Pattaya; amphoe candidate |
| Timor-Leste | TL | 14 | 14 | 14 | 1 | administrative: municipality | — | → | L1 — 13 ISO + Atauro (AT provisional); admin posts out of scope |
| Togo | TG | 5 | 5 | 5 | 1 | administrative: region | — | none | L1 |
| Tonga | TO | 5 | 5 | 5 | 1 | administrative: division | — | none | L1 — 5 divisions |
| Trinidad and Tobago | TT | 15 | 15 | 15 | 1 | administrative: region | — | → | L1 — 10 regions + 3 boroughs + POS + Tobago |
| Tunisia | TN | 24 | 24 | 24 | 1 | administrative: governorate | — | ← | L1 |
| Turkmenistan | TM | 6 | 6 | 6 | 1 | administrative: region | — | below | L1 — 5 regions + Ashgabat |
| Turks and Caicos | TC | 6 | 6 | 6 | 1 | administrative: district | — | below | L1 — 6 districts |
| Tuvalu | TV | 8 | 8 | 8 | 1 | administrative: island_council | — | none | L1 — 7 island + 1 town council |
| Türkiye | TR | 81 | 81 | 1054 | 2 | administrative: province > district | district | ← | Complete — 81 provinces + 973 districts; 51 Merkez; Ereğli twins; no district codes |
| Uganda | UG | 4 | 4 | 4 | 1 | administrative: region | — | ← | L1 — regions only; districts excluded (volatile) |
| Ukraine | UA | 27 | 27 | 27 | 1 | administrative: oblast | — | below | L1 — 24 oblasts + Kyiv/Sevastopol/Crimea |
| United Arab Emirates | AE | 7 | 7 | 7 | 1 | administrative: emirate | — | none | L1; no official tier-2 |
| United Kingdom | GB | 4 | 4 | 4 | 1 | administrative: nation | — | below | L1 — 4 nations; 221 subdivisions global-only |
| United States | US | 56 | 56 | 56 | 1 | administrative: state | — | US → | L1 — 50 + DC + 5 territories; AA/AE/AP/UM global-only |
| US Minor Outlying Islands | UM | 9 | 9 | 9 | 1 | administrative: island | — | none | L1 — 9 islands; uninhabited |
| US Virgin Islands | VI | 3 | 3 | 3 | 1 | administrative: district | — | US → | L1 — 3 districts |
| Uruguay | UY | 19 | 19 | 19 | 1 | administrative: department | — | ← | L1 |
| Uzbekistan | UZ | 14 | 14 | 220 | 2 | administrative: region > tuman | tuman | ← | Complete — 12 regions + republic + city; 175 tumanlar + 31 regional-subordination cities; ASCII apostrophes; Namangan city districts excluded (L3) |
| Vanuatu | VU | 6 | 6 | 6 | 1 | administrative: province | — | none | L1 |
| Venezuela | VE | 25 | 25 | 25 | 1 | administrative: state | — | → | L1 — 23 states + Caracas + dependencies |
| Vietnam | VN | 34 | 34 | 34 | 1 | administrative: province | — | → | L1 post-merger 34; communes candidate |
| Wallis and Futuna | WF | 3 | 3 | 3 | 1 | administrative: administrative_precinct | — | ← | L1 — 3 precincts |
| Yemen | YE | 22 | 22 | 22 | 1 | administrative: governorate | — | none | L1 — 21 governorates + Amanat Al Asimah |
| Zambia | ZM | 10 | 10 | 10 | 1 | administrative: province | — | → | L1 |
| Zimbabwe | ZW | 10 | 10 | 10 | 1 | administrative: province | — | none | L1 |

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
