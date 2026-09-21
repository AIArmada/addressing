---
title: Country Data
---

# Country Data

For per-provider depth and dataset status at a glance, see the
[Provider Coverage Registry](./14-provider-coverage.md).

## Bundled Dataset

The package always bundles ISO 3166-1 country/territory data.

File location: `resources/data/countries.json`

The bundled `MalaysiaGeographyProvider` supplies Malaysia's State/Federal Territory catalog, two explicit address hierarchies, the AddressArea hierarchy, and State↔AddressArea mappings. The postal/address hierarchy is `region → locality / precinct / kampung`; the administrative/land hierarchy is `region → district / division / jajahan → mukim / subdistrict / bandar / pekan`. It is selected with `SeedCountryGeographiesAction::execute('MY')` after countries are seeded.

The dataset contains **250 records** — these are ISO 3166-1 address entities, not 250 sovereign countries. Records include:

- ISO2, ISO3, numeric codes
- Names (common, native)
- Phone codes
- Capital
- Country centroid coordinates
- Region and subregion
- Currency code
- Timezones
- Top-level domain
- Translated country names

Currency, language, and timezone reference data is owned and seeded by `commerce-support`:

- `currencies` from the shared currency catalogue
- `languages` from the shared ISO 639-1 catalogue
- `timezones` from the shared IANA timezone catalogue

Countries are linked to shared currencies and timezones through UUID pivot tables. The country table does not store currency or timezone JSON/scalar columns. Country-language relationships are intentionally not modelled until a trusted mapping dataset is available.

## What is NOT Bundled by Default

Without selecting a country provider, the following must be supplied by users through `State`/`City` models, `AddressAreaSource`, array imports, or CSV imports:

- States, federal territories, provinces, prefectures, emirates
- Districts, cities, towns, villages, mukim, neighbourhoods
- Postcodes and worldwide area hierarchies

## Bundled States and Cities

`states.json` and `cities.json` are bundled from the same nnjeim/world source. Seed the global files first; country providers then complement those rows using stable country-scoped identities. The Malaysia provider updates matching states in place and adds Malaysia-specific rows not present in the global file, such as Putrajaya. It does not seed shared commerce-support reference data. The bundled area source contains no canonical city mapping data.

## Malaysia area roles

Kuala Lumpur, Putrajaya and Labuan do not receive duplicate postal-town
records. Their Federal Territory is the administrative area, while localities,
precincts and settlements are address subdivisions.

- postal_locality: Kuala Lumpur localities, Labuan settlements and Putrajaya precincts
- administrative_district: districts
- administrative_subdivision: mukim and subdistricts
- region: states and Federal Territories

## Malaysia cross-boundary mukims

Several mukim names appear on both sides of a district or state boundary.
Every case was verified against gazettes, the JUPEM UPI boundary book, and
titled-land evidence (Sept 2026). The verdict in all four cases: distinct
same-named mukims, each with exactly one parent — never one mukim spanning
two parents. DOSM's "Sebahagian Mukim X (Peralihan ...)" phrasing describes
the 1974 Gombak-formation transfers, not current dual parentage.

| Mukim | Verdict |
|---|---|
| Setapak | Two mukims: Gombak-side (Selangor gazette) and KL-side (Federal Gazette 2024, UPI 140007). A 1974-split remnant, administered separately ever since. |
| Ampang | Two mukims: Hulu Langat-side (MPAJ: "smallest county in the district of Hulu Langat") and KL-side (UPI 140001). The straddling *town*/MPAJ area is not a third mukim. |
| Batu | Two mukims: Gombak-side ("Mukim Batu bagi Daerah Gombak") and KL-side ("MUKIM BATU, DAERAH KUALA LUMPUR", UPI 140002). A third Mukim Batu sits in Kuala Langat. |
| Cheras | Two mukims: Hulu Langat-side and KL-side ("Mukim Cheras, Daerah Kuala Lumpur", UPI 140003). KL's P123 Cheras parliamentary constituency is a separate concept. |

## Kuala Lumpur mukims

WPKL has exactly seven gazetted mukims and no district (`TIADA DAERAH`), so
each hangs directly under the Federal Territory at level 2. Mukim rows use a
`mukim-` source-id prefix because the parliamentary localities already occupy
the bare names.

| Mukim | UPI / PLANMalaysia | DOSM census |
|---|---|---|
| Ampang | 140001 | 140101 |
| Batu | 140002 | 140102 |
| Cheras | 140003 | 140103 |
| Hulu Klang | 140004 | 140104 |
| Kuala Lumpur | 140005 | 140105 |
| Petaling | 140006 | 140106 |
| Setapak | 140007 | 140107 |

Notes:

- Bandar Kuala Lumpur is a gazetted bandar (UPI 140044), not a mukim, so it
  has no mukim row despite "Mukim Bandar Kuala Lumpur" title phrasing.
- The rows use the gazette/KWP "Hulu Klang" spelling; the JUPEM UPI
  "Hulu Kelang" form is stored as an alternative name.
- The duplicate Gombak "Ulu Kelang" mukim row was removed: Gombak has one
  Hulu Klang mukim, and "Ulu Kelang" is a gazetted town name there.

Evidence: JUPEM/MyGDI UPI book for WPKL (2nd ed. 2023), PLANMalaysia mukim
code list (state 14), DOSM census geography, Selangor State Gazette notices,
Federal Gazette P.U.(B) notices, ST licensee lists, and Bursa land
disclosures.

## Selangor mukim audit

Every Selangor subdivision row was diffed against the JUPEM UPI boundary book
for Selangor (Sept 2026). All gazetted Selangor mukims were already bundled;
the fixes below correct mistyped towns, wrong districts, renames, and
non-gazetted rows. Bandar and pekan are distinct row types sharing the
`administrative_subdivision` role; renamed rows keep their common names as
alternative or preferred names so searches keep resolving.

Retyped to bandar (gazetted): Banting, Bandar Baru Bangi, Kuang, Kundang,
Selayang, Petaling Jaya, Saujana, Subang Jaya, Cyberjaya, Shah Alam, Kuala
Kubu Bharu. Retyped to pekan (gazetted): Meru, Sekinchan, Sungai Besar,
Puchong, Sungai Pelek.

| Row | Fix | Evidence |
|---|---|---|
| Port Klang | Renamed Port Swettenham, bandar | UPI Bandar Port Swettenham 01/41; "Port Klang" kept as preferred common name |
| Gombak | Renamed Gombak Setia, bandar | UPI Bandar Gombak Setia 09/43; bare "Gombak" not gazetted |
| Salak Tinggi | Renamed Baru Salak Tinggi, bandar | UPI Bandar Baru Salak Tinggi 10/42; bare name not gazetted |
| Tanjung Sepat | Renamed Tanjong Sepat, bandar | UPI Bandar Tanjong Sepat 02/43 |
| Jenjarum (Klang) | Moved to Kuala Langat as Jenjarom, bandar | UPI Bandar Jenjarom 02/41; town is in Kuala Langat |
| Batu Arang (Sepang) | Moved to Gombak, bandar | UPI Bandar Batu Arang 09/40; town is in Gombak |
| Bukit Rotan (Hulu Selangor) | Moved to Kuala Selangor, pekan | UPI Pekan Bukit Rotan 04/72; town is in Kuala Selangor |
| Jenjarum Barat / Utama | Removed | No such place or entity in UPI, gazettes, or SPR records |
| Johan Setia, Paya Jaras | Removed | Kampung/locality inside a mukim, not a UPI entity |
| Setia Alam, Denai Alam, USJ / UEP Subang Jaya, Taman Melawati | Removed | Developer townships inside a mukim, not UPI entities |
| Batu Caves | Removed | Town inside Mukim Batu, not gazetted at any level |
| Sabak Bernam, Hulu Selangor | Removed | District-name rows; the districts and their mukims already exist |
| Teluk Panglima Garang (Klang) | Removed | Wrong-district duplicate of Kuala Langat's Mukim Telok Panglima Garang |

Spelling follows the gazetted UPI form for renamed rows (Jenjarom, Tanjong,
Swettenham); rows that keep common spellings (Hulu Langat, Telok Panglima
Garang, Kuala Kubu Bharu) carry the UPI variant as an alternative name.
Postal links were repointed to the surviving rows; no postcode lost its
primary link.

Two-hierarchy relocation: 3 removed towns returned as postal `locality`
rows — Batu Caves, Teluk Panglima Garang (corrected to Kuala Langat, town
distinct from Mukim Telok Panglima Garang), and Sabak Bernam (town distinct
from Mukim Sabak; 45100 Sungai Ayer Tawar stays district-linked). Stay
deleted: the Denai Alam, USJ, Setia Alam, and Taman Melawati townships (all
share Shah Alam, Subang Jaya, or Kuala Lumpur codes) and the Johan Setia and
Paya Jaras kampungs (no own postcode).

## Genting Highlands

Genting Highlands is not a mukim of Bentong. Bentong has exactly three
mukims (Bentong, Sabai, Pelangai); the gazetted Genting unit is Bandar
Genting under Daerah Kecil Genting, excised from Mukim Bentong in November
2019 (Pahang Gazette Notifications 2497/2501, UPI 06/12/40). The dataset
models the minor district and the bandar; "Genting Highlands" is stored as
the preferred common name. Karak in the same pass was corrected from mukim
to bandar (UPI Bandar Karak).

The resort genuinely spans the Pahang–Selangor border: the Selangor
footprint (Highlands/RW Hotel, Skyway assets, Gohtong Jaya) sits in Hulu
Selangor district, Mukim Batang Kali, under MPHS planning authority — while
mailing with the Pahang postcode 69000. There is no gazetted Selangor-side
Genting entity, so no second row exists; never key state off the postcode.

Evidence: JUPEM UPI book for Pahang, Pahang Gazette 2019 declarations,
Genting Berhad annual-report land schedules (Selangor vs Bentong), the
Genting Highlands–Hulu Selangor RKK plan, and titled-land records on both
sides.

## Pahang mukim audit

Every Pahang subdivision row was diffed against the JUPEM UPI boundary book
for Pahang (Sept 2026). Pahang has 11 districts plus 4 minor districts
(Genting, Gebeng, Jelai, Muadzam Shah — the last three created 2019–2021).
Retyped to bandar: Jerantut, Kuantan, Temerloh, Maran, Raub. Retyped to
pekan: Brinchang, Lanchang, Bukit Fraser, Kuala Rompin (UPI-listed, still
ungazetted).

| Row | Fix | Evidence |
|---|---|---|
| Gebeng (Kuantan) | Moved under new Daerah Kecil Gebeng, bandar | UPI Bandar Gebeng 13/40 |
| Batu Yon, Hulu Jelai (Lipis) | Moved under new Daerah Kecil Jelai, mukim | UPI Jelai 14/01–02; Hulu Jelai respelled Ulu Jelai |
| Telang | Kept under Lipis and added under Jelai | Two distinct mukims: Lipis 10 (Phg.733/2021) and Jelai 03 (Phg.520/2021) |
| Keratong (Rompin) | Moved under new Daerah Kecil Muadzam Shah, mukim | UPI Muadzam 15/01 |
| Bebar | Kept under Pekan and added under Muadzam Shah | Two distinct mukims: Pekan 01 (Phg.845/1992) and Muadzam 02 (Phg.732/2021) |
| Muadzam Shah (Rompin) | Removed; town is two bandars now modelled | UPI Muadzam Shah I/II (15/40–41) added under the minor district |
| Kuala Krau (Jerantut) | Moved to Temerloh as Kuala Kerau, pekan | UPI Pekan Kuala Kerau 08/72; common spelling kept preferred |
| Teras (Raub) | Renamed Tras, mukim | UPI Mukim Tras 07/07; "Teras" is an upstream typo with zero gazette hits |
| Bandar Kuantan, Bandar Bera | Removed | Duplicates of the Kuantan bandar and Bera mukim rows |
| Dong, Sega (Lipis) | Removed | Wrong-district duplicates of Raub's mukims |
| Balok, Bukit Goh, Bukit Kuin, Sungai Lembing | Removed | Non-gazetted Kuantan towns/schemes inside a mukim |
| Damak, Sungai Koyan, Chini, Kemayan | Removed | Non-gazetted kampung/FELDA localities |
| Bandar Pusat Jengka, Bandar Tun Abdul Razak, Lurah Bilut | Removed | Non-gazetted towns (Jengka rows duplicated one renamed town) |

Postal links were repointed to the surviving mukim, pekan, or district rows;
no postcode lost its primary link.

Two-hierarchy relocation: 12 removed towns returned as postal `locality`
rows — Balok (26080/26190 only; 26100/26150 are Kuantan city codes), Bukit
Goh, Sungai Lembing, Bandar Tun Abdul Razak (corrected to the Muadzam Shah
minor district), Lurah Bilut (corrected to Bentong), Chini, Bandar Bera,
Kemayan, Bandar Pusat Jengka (corrected to Maran; 27080 stays Jerantut),
Damak, Kuala Krau (corrected to Temerloh), and Sungai Koyan. Stay deleted:
Bukit Kuin (sub-locality), Muadzam Shah (duplicate of the I/II bandars), and
Hulu Jelai (admin entity, no postcode).

## Johor mukim audit

Every Johor subdivision row was diffed against the JUPEM UPI boundary book
for Johor (Sept 2026). Retyped to bandar: Ayer Hitam, Bandar Penggaram,
Rengit, Senggarang, Yong Peng, Johor Bahru, Bandar Kluang, Bandar Kota
Tinggi, Bandar Mersing, Bandar Maharani, Batu Anam, Segamat, Bandar Kulai,
Bandar Tangkak. Retyped to pekan: Bukit Pasir, Pekan Nenas.

| Row | Fix | Evidence |
|---|---|---|
| Muar (Muar) | Renamed Bandar, kept mukim | UPI Mukim Bandar 06/02; town row is Bandar Maharani |
| Bandar Pontian | Renamed Pontian Kechil, bandar | UPI Bandar Pontian Kechil 07/41; Pontian Kechil is the town |
| Sungai Mati (Muar) | Moved to Tangkak, bandar | UPI Bandar Sungai Mati 22/43 |
| Bandar Johor Bahru, Bandar Segamat, Renggam, Gerisek, Seri Medan | Removed | Duplicates of the Johor Bahru bandar, Segamat bandar, Rengam, Grisek, and Sri Medan rows |
| Chaah (Kluang) | Removed | Wrong district; Chaah belongs to Segamat |
| Batu Pahat, Parit Raja, Parit Sulong, Semerah | Removed | Non-gazetted towns (town core is Bandar Penggaram) |
| Bandar Tiram, Gelang Patah, Iskandar Puteri, Masai, Pasir Gudang, Ulu Choh, Ulu Tiram | Removed | Non-gazetted JB towns/corridors inside a mukim |
| Divisyen Bandaraya | Removed | Not a place at all; an MBJB assessment-division label |
| Simpang Renggam, Bandar Penawar | Removed | Non-gazetted towns inside a mukim |
| Ayer Tawar 2, Pulau Satu | Removed | Wrong-district camp/island locality, non-gazetted |
| Endau (Mersing) | Removed | Non-gazetted town; the only gazetted Endau is Pahang's Mukim Endau |
| Bukit Gambir, Pagoh, Kukup | Removed | Non-gazetted towns/villages (Bukit Gambir is in Tangkak) |
| Bandar Tenggara, Gugusan Taib Andak | Removed | Non-gazetted FELDA schemes (Bandar Tenggara is in Kota Tinggi) |

UPI spelling variants are stored as alternative names (Nyior, Ulu Sungei
Sedili Besar, Sungei Pinggan, Renggam, Gerisek, Bandar Pontian). Postal
links were repointed to the surviving mukim, bandar, pekan, or district
rows; duplicate same-area links created by the merges were collapsed.

Two-hierarchy relocation pilot: 21 of the removed towns were reinstated as
postal `locality` rows under the two-hierarchy rule (postcode + official
recognition required; relocate, don't delete). Reinstated: Gelang Patah,
Iskandar Puteri, Masai, Pasir Gudang, Ulu Tiram, Ulu Choh (corrected to
Kulai), Gugusan Taib Andak, Bandar Tenggara and Ayer Tawar 2 (both corrected
to Kota Tinggi), Bandar Penawar, Kukup, Endau, Parit Raja, Parit Sulong,
Semerah, Renggam and Simpang Rengam (towns distinct from Mukim Rengam),
Pekan Chaah (corrected to Segamat), Pagoh, Bukit Gambir (corrected to
Tangkak), and Gerisek (town within Mukim Grisek). Bandar Tiram (quarter of
Ulu Tiram), Divisyen Bandaraya (non-place label), Bandar Pontian (alias of
Pontian Kechil), and Pulau Satu (Forest City island, no own postcode) stay
deleted. Each town's postcodes link it as primary with the covering admin
areas kept as secondary links.

## Perlis

Perlis has no districts (`TIADA DAERAH`) and exactly 22 mukims, all already
bundled with exact names. Verified clean against the JUPEM UPI book; no
changes. Bandar Arau, Bandar Kangar, Pekan Kuala Perlis, and Pekan Kaki
Bukit exist but have no rows (missing entities are not added).

## Melaka mukim audit

Every Melaka subdivision row was diffed against the JUPEM UPI boundary book
for Melaka (Sept 2026). Retyped to bandar: Melaka, Bandar Jasin, Bandar Alor
Gajah. Retyped to pekan: Asahan, Bemban. Same-name mukim/town duals keep
the mukim row and the town entity stays unlisted (missing entities are not
added): Merlimau, Kuala Sungai Baru, Sungai Rambai, Ayer Molek, Batu
Berendam, and the other Melaka Tengah duals. Removed: the
Bandaraya Melaka city-status label, both Ayer Keroh town rows (non-gazetted;
postcode 75450 spans Bukit Katil and Bukit Baru, so it links the district),
the Alor Gajah town duplicate, and the wrong-district Alor Gajah Asahan row
(Asahan town is Jasin's Pekan Asahan — the error came from its Alor Gajah
parliamentary seat). Kept on gazette evidence despite UPI naming quirks:
Ayer Pa'abas (UPI 03/01) and Sungai Baru Tengah (2004/2006 gazettes; UPI
shortens it to plain Sungei Baru).

Two-hierarchy relocation: Ayer Keroh town (75450) returned as a postal
`locality` row under Melaka Tengah; the Jasin Ayer Keroh row stays deleted
as a misfile.

## Penang mukim audit

Every Penang subdivision row was diffed against the JUPEM UPI boundary book
for Pulau Pinang (Sept 2026), cross-checked against PLANMalaysia kod-mukim,
DOSM census divisions, state gazettes, and land-title records. Retyped to
bandar (14): Bukit Mertajam, Perai, Butterworth, Kepala Batas, Nibong Tebal,
Air Itam, Bandar George Town, Batu Ferringhi, Gelugor, Jelutong, Tanjong
Bungah, Bukit Bendera (renamed from Penang Hill; gazetted Bandar Bukit
Bendera), Balik Pulau, Bayan Lepas. The island shares one mukim sequence:
Barat Daya is Mukim 1-12 plus Mukim A-J, Timur Laut is Mukim 13-18; Seberang
Perai Utara genuinely skips Mukim 15, so that row was removed. Removed 14
rows: non-gazetted town/area names (Permatang Pauh, Seberang Jaya, Kubang
Semang, Penaga, Tasek Gelugor, Simpang Ampat, Sungai Jawi, Batu Maung, Teluk
Kumbar), the state-name Pulau Pinang row (its George Town city postcodes move
to Bandar George Town), the USM institution row (11800 moves to Gelugor), the
SPU Mukim 15 gap row, and the Bandar Bukit Mertajam / Bandar Butterworth
duplicates. Postcodes remap to the true numbered mukim (Kubang Semang is SPT
Mukim 5, a district correction); Sungai Jawi straddles SPS Mukim 6+7 so 14200
links the district.

Two-hierarchy relocation: 8 removed towns returned as postal `locality`
rows — Permatang Pauh, Kubang Semang (corrected to SPT), Penaga, Tasek
Gelugor, Batu Maung, Teluk Kumbar (11920 only; 11950 is Bayan Lepas),
Simpang Ampat, and Sungai Jawi. Stay deleted: Penang Hill (11300 is George
Town), USM (11800 is Gelugor, plus a facility), Seberang Jaya (13700 is
Perai), and SPU Mukim 15 (no gazette evidence; genuinely skipped).

## Terengganu mukim audit

Every Terengganu subdivision row was diffed against the JUPEM UPI boundary
book for Terengganu (Sept 2026), cross-checked against PLANMalaysia kod-mukim,
DOSM census divisions, state gazettes, and land-title records. Retyped to
bandar: Kuala Terengganu, Dungun, Cukai (renamed from Chukai). Retyped to
pekan: Marang, Jertih (renamed from Jerteh). Renamed to gazetted forms with
common names kept as alternatives: Hulu Cukai, Kemasik, Kertih, Mercang,
Pengkalan Nangka. Kept against a narrow UPI read on wider evidence: Kuala
Abang (UPI truncates it to Abang; PLANMalaysia, DOSM, and gazettes agree on
Kuala Abang) and Caluk (canonical over the Chalok duplicate, which was
removed). Removed 12 rows: non-gazetted towns Paka, Bukit Besi, Bandar
Al-Muktafi Billah Shah, Ceneh, Ajil, Permaisuri, Bandar Permaisuri, Penarik;
wrong-district Ketengah Jaya (Dungun's Mukim Rasau), Sungai Tong (Setiu's
Mukim Hulu Nerus), and Bukit Payong (Marang's Mukim Bukit Payung, spelling
corrected in the move); and the Chalok duplicate of Caluk. Postcodes remap to
the gazetted mukim each town falls in.

Two-hierarchy relocation: 11 removed towns returned as postal `locality`
rows — Bukit Payong (corrected to Marang), Ceneh (24060 only; 24050 is Air
Putih), Kerteh, Ketengah Jaya (corrected to Dungun), Al-Muktafi Billah
Shah, Bukit Besi, Paka, Ajil, Sungai Tong (corrected to Setiu), Permaisuri
(the Bandar Permaisuri pair merged into one row), and Chalok (postal
spelling, distinct from Mukim Caluk). Stay deleted: Chukai (duplicate of
Bandar Cukai) and Penarik (no own postcode).

## Perak mukim audit

Every Perak subdivision row was diffed against the JUPEM UPI boundary book
for Perak (Sept 2026; 13 districts), cross-checked against PLANMalaysia
kod-mukim, DOSM census divisions, state gazettes, and land-title records.
Structural fixes: the combined Larut-Matang-dan-Selama district row was split
into Larut Matang (15 rows) and Selama (3 rows); Sungai Sumun moved from
Hilir Perak to Bagan Datuk; Trolak moved from Batang Padang to Muallim as
Pekan Terolak. Ipoh town split into Bandar Ipoh (N) and Bandar Ipoh (S) with
all 112 town postcodes linking the Kinta district (Muadzam precedent; the
N/S line runs east-west across the town centre per plan PW 5296). Retyped 17
rows to bandar and 12 to pekan (Langkap and Malim Nawar kept as pekan on
gazette evidence). Renamed to gazetted forms: Kelian Intan, Simpang Empat,
Terung (Terong/Trong are the same place), Hulu Ijok, Hulu Selama, Jaya Baru,
Pasir Panjang Hulu, Sayung, Sungai Raya, Hulu Bernam Barat. Removed 23 rows:
7 non-gazetted towns (Behrang Stesen, Seri Manjung, Kampung Kepayang, Jeram,
Sauk, Enggor, Ulu Bernam), the TLDM Lumut base row (32100 to Lumut), 9
wrong-district rows (Changkat Jering, Slim, Slim River, Belanja, Kampar, Teja,
Tronoh, Rantau Panjang, Batu Kurau), 5 spelling duplicates (Bruas, Bagan
Datoh, Ulu Kinta, Bandar Seri Iskandar, Trong of Terung), and Ipoh town
(split into N/S bandars). Uncertain
postcodes link the district: 36500 (Ladang Ulu Bernam estate) to Hilir Perak,
31750 to Kinta, 34140 to Selama, 34850 to Larut Matang.

Two-hierarchy relocation: 11 removed towns returned as postal `locality`
rows — Kampung Kepayang, Trong, Seri Manjung, Behrang Stesen (corrected to
Muallim), Changkat Jering (corrected to Larut-Matang), Enggor, Jeram
(corrected to Kinta), Sauk, Tronoh (corrected to Kampar; 31750 moves off
Kinta district), Rantau Panjang (corrected to Selama), and Ulu Kinta
(postal spelling, distinct from Mukim Hulu Kinta). Stay deleted: Sungai
Raia (shares 31300), Terong (variant), the TLDM Lumut base (facility),
Simpang Ampat Semanggol and Trolak (duplicates of Pekan Simpang Empat and
Pekan Terolak), Saiong (mukim variant), Intan (Kelian Intan short form),
and Ulu Bernam (split/shared codes).

## Kedah mukim audit

Every Kedah subdivision row was diffed against the JUPEM UPI boundary book
for Kedah (Sept 2026), cross-checked against PLANMalaysia kod-mukim, DOSM
census divisions, state gazettes, and land-title records. Retyped 17 rows to
bandar and 5 to pekan. Renamed to gazetted forms: Gunung, Changlun, Hosba,
Kurung Hitam, Guar Cempedak. Moved 3 rows to the correct district: Kepala
Batas and Kodiang from Kota Setar to Kubang Pasu (both bandar), Jeniang from
Sik to Kuala Muda as Pekan Jeniang (gazetted 2009 from Mukim Gurun; 08700
follows the move while 08320 links Sik's Mukim Jeneri). Removed 5 rows: the
Kota Setar and Langkawi district-name rows (Langkawi town postcodes move to
Kuah), the UUM institution row, Yan Kechil locality (06910 secondary moves
to Sungai Daun), and Bukit Paya (a PLANMalaysia typo for Mukim Bukit Raya).

## Kelantan mukim audit

Every Kelantan subdivision row was diffed against the JUPEM UPI boundary book
for Kelantan (Sept 2026), cross-checked against PLANMalaysia kod-mukim, DOSM
census divisions, state gazettes, and land-title records. Retyped to bandar:
Kota Bharu, Baru Kubang Kerian (renamed from Kubang Kerian), Pasir Mas, Tanah
Merah. Retyped Mulong to pekan. Moved Melor from Bachok to Kota Bharu.
Removed 14 rows: the Bandar Kota Bharu duplicate (93 town postcodes move to
Kota Bharu), non-gazetted Daerah/penghulu names (Chiku, Dabong x2, Galas, Olak
Jeram, Ayer Lanas, Bandar Baru Tunjong, Ketereh, Panji), the Kem Desa Pahlawan
camp row (16500 to the district; 16450 spans mukims so it links the district
too), the false Bandar Jeli row (Jeli has no gazetted bandar; 17600/17700 go
to Mukim Jeli), the Bachok Cherang Ruku duplicate, and the Betis duplicate of
Kuala Betis. 18200 Dabong links Mukim Kuala Stong on land-title evidence.

Two-hierarchy relocation: 3 removed towns returned as postal `locality`
rows — Ketereh (16450), Dabong (18200, Kuala Krai; the Gua Musang row stays
deleted as a misfile), and Ayer Lanas (17700). Stay deleted: Bandar Baru
Tunjong (township sharing Kota Bharu codes), the Kem Desa Pahlawan camp
(facility), Panji, Olak Jeram, Chiku, and Galas (no own postcode), and
Bandar Jeli (the town's name is exactly Mukim Jeli's, so it is covered).

## Negeri Sembilan mukim audit

No JUPEM UPI book is published for Negeri Sembilan, so every subdivision row
was diffed against the PLANMalaysia kod-mukim inventory (2021) with each
verdict corroborated by state gazettes, DOSM-coded lists, or land titles.
Retyped 5 rows to bandar and 10 to pekan. Moved Tanjong Ipoh from Jelebu to
Kuala Pilah as a pekan. Renamed to gazetted forms: Baru Enstek, Serting Hulu,
Sepri. Removed Seremban 2 (housing scheme in Mukim Rasah) and Pusat Bandar
Palong (FELDA cluster centre; postcodes move to Mukim Rompin). Kept on
gazette evidence against PLANMalaysia typos: Titian Bintangor (not Bintagor),
Keru (not Kebu), Tebong of Tampin (not Tenong; distinct from Melaka's Tebong).

Two-hierarchy relocation: Pusat Bandar Palong (73430-73470, own post
office) returned as a postal `locality` row; Seremban 2 stays deleted (70300
is Seremban).

## Sabah audit

Sabah has no gazetted mukim tier, so every subdivision row was verified
against Sabah state gazettes, the SPR polling-district gazette, DOSM census
divisions, district-office mukim lists, and council records. Added the Paitan
and Sook districts (gazetted 2024). Moved Jambongan from Beluran to Paitan,
Nangoh from Beluran to Telupid, and Dalit from Keningau to Sook. Removed 8
rows: stale pre-split district duplicates (Paitan, Tongod, Nabawan, Sook,
Tambunan), the Libaran parliament/island ambiguity, the Wallace Bay water
feature, and the Cenderawasih FELDA postal town (91150 moves to Lahad Datu
district). Renamed to official forms: Gum-Gum, Bum-Bum, Sepulot.

Two-hierarchy relocation: Cenderawasih (91150, own post office) returned as
a level-4 postal `locality` row; the remaining removals stay deleted as
duplicates or non-places.

## Sarawak audit

Sarawak has no mukim tier; the daerah kecil is the formal subdistrict tier,
so every row was verified against the Administrative Areas Order 2022
gazette, the 2018 admin table plus 2021/22 upgrade notices, the SPR
polling-district gazette, and DOSM townships. Moved 9 rows: Tapah to Siburan,
Moyan and Tambirat to Asajaya, Triso to Pusa, Roban to Kabong, Nanga Medamit
to Limbang, Belawai to Tanjung Manis, Paloh to Daro, and Niah to Subis.
Removed 13 rows: stale post-split duplicates (Balingian, Bekenu, Long Lama,
Lingga, Kabong, Sebauh, Tatau, Entabai), the Kuala Balingian duplicate, the
Pusat Mel Miri mail centre (98070 moves to Miri), the Baram region name
(Marudi postcodes move to Marudi), the Poyut/Nibong conflated name, and the
Sebelak river name. Renamed Budu to the official Nanga Budu form.

## Singapore

The bundled `SingaporeGeographyProvider` supplies the five ISO 3166-2 CDC
districts as `State` rows, the URA planning tree (5 regions, 55 planning
areas), and the postal tree (28 districts, 81 sectors; sector `74` is
unallocated). The postal hierarchy is `postal district → postal sector`; the
administrative hierarchy is `planning region → planning area`. It is selected
with `SeedCountryGeographiesAction::execute('SG')` after countries are seeded.

CDC districts, URA regions, and postal districts are three independent
first-level partitions: a CDC district is never the parent of a planning area
or a postal sector. `State` rows link to matching district areas without a
hierarchy type so the bridge resolves them for any hierarchy.

Individual six-digit postcodes are intentionally not bundled. Every building
in Singapore has its own postcode, so the dataset is SingPost-scale. Resolve
operational postcodes on demand with `ResolveSingaporePostalCodesAction`,
which queries SLA's OneMap API and caches the results in `postal_codes` with
sector links. Use the bundled postal sectors (the first two postcode digits)
for area-level grouping. OneMap data requires attribution to OneMap/SLA in
applications that display it.

Singapore addresses are formatted as street lines followed by
`Singapore NNNNNN`. State and city lines are omitted when they are empty or
just repeat `Singapore`; a distinctive town value is kept on its own line.

## Indonesia

The bundled `IndonesiaGeographyProvider` supplies the 38 ISO 3166-2
provinces as `State` rows and one administrative hierarchy: 38 provinces →
514 regencies/cities → 7,285 districts. Area codes are the official
Kemendagri codes (2/4/6 digits) and `source_id` values embed them
(`id:province:11`, `id:regency:1101`, `id:district:110101`). It is selected
with `SeedCountryGeographiesAction::execute('ID')` after countries are seeded.

The area tree is derived from
[lokabisa-oss/region-id v1.0.1](https://github.com/lokabisa-oss/region-id/releases/tag/v1.0.1)
(MIT), keeping official names, codes, and parent links. Two province names
differ from that source so areas match the seeded states: `DKI Jakarta`
(source: `Daerah Khusus Ibukota Jakarta`, the pre-2024 legal name) and `DI
Yogyakarta` (source: `Daerah Istimewa Yogyakarta`, kept as an alias). The
current legal name `Daerah Khusus Jakarta` is also aliased.

Villages and urban villages (83,762 desa/kelurahan, level 4) ship as an
opt-in dataset in `indonesia-villages.csv`, from the same upstream release.
Set `addressing.geography.indonesia.villages` to `true`
(`ADDRESSING_INDONESIA_VILLAGES`) to seed them; the default seed stops at
districts.

ISO 3166-2 defines seven Indonesian geographical units (island groups such
as `ID-JW` Jawa) alongside the 38 provinces. Those units are not provinces
and were removed from the bundled state data; `seed()` also deletes any
stragglers from databases seeded before that fix, so `Papua` always resolves
to the province. Nusantara/IKN is a separate capital authority, not a 39th
province.

Villages (83,762) and individual five-digit postcodes are intentionally not
bundled. Import operational villages through `AddressAreaSource` and
postcodes through `ImportPostalCodesAction`.

Indonesian addresses are formatted as street lines, `kelurahan`/`desa` and
`kecamatan` components, `{kota} {postcode}`, province, and country, per the
UPU addressing layout.

## Brunei

The bundled `BruneiGeographyProvider` supplies the four ISO 3166-2
districts as `State` rows and one administrative hierarchy: 4 districts →
39 mukims (18 in Brunei-Muara, 8 each in Belait and Tutong, 5 in
Temburong). It is selected with
`SeedCountryGeographiesAction::execute('BN')` after countries are seeded.

Mukim names follow Brunei government spellings (`Pengkalan Batu`, with
`Pangkalan Batu` aliased). Bruneian postcodes are six characters — two
uppercase letters (district, then mukim) followed by four digits — but no
complete public dataset exists, so postcodes are intentionally not bundled.
Import operational villages and postcodes through `AddressAreaSource` and
`ImportPostalCodesAction`.

Brunei addresses are formatted per the UPU layout: street lines, kampung
component, `{town or district} {postcode}` with the town preferred, and
country.

## Bahrain

The bundled `BahrainGeographyProvider` supplies the four ISO 3166-2
governorates as `State` rows (codes `13`, `14`, `15`, `17` — there is no
`16` since the Central Governorate was abolished in 2014) and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BH')` after countries are seeded.

Bahraini addresses are formatted per the UPU layout: street lines,
`{municipality} {postcode}` with a 3–4 digit postcode, and country.

## Qatar

The bundled `QatarGeographyProvider` supplies the eight municipalities
as `State` rows with 90 census zones as level-2 areas (57 Doha, 10 Al
Rayyan, 7 Al Wakrah, 7 Al Sheehaniya, 3 Al Khor, 3 Al Shamal, 2 Al
Daayen, 1 Umm Salal) in a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('QA')` after
countries are seeded. Zone numbers run 1–98 with 8, 9, 10, 11, 59, 87,
88, 89 unassigned; zones 50/57/58 sit in Doha, 69 in Al Daayen, and
96/97 in Al Rayyan, so filter by parent rather than assuming
contiguous ranges. Official PSA district names are kept as `official`
aliases; repeated district names across zones (Al Thumama ×3, Nuaija
×3, Al Bidda, Mushaireb, Old Al Ghanim, Fereej Bin Mahmoud, Onaiza,
Doha International Airport ×2 each) share names by design; filter by
code.

Municipality names use official English spellings (`Al Sheehaniya`,
`Al Shamal`, `Doha`); the ISO names `Ad Dawhah` and `Madinat ash
Shamal` are kept as aliases. Qatar has no postcode system — delivery
is by P.O. Box or zone/street — so the formatter stacks street lines,
city, and country with no postcode line.

## Kuwait

The bundled `KuwaitGeographyProvider` supplies the six ISO 3166-2
governorates as `State` rows with 134 postal areas as level-2 areas
(31 Capital, 29 Ahmadi, 24 Jahra, 20 Farwaniya, 17 Hawalli, 13 Mubarak
Al-Kabeer) in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KW')` after countries are seeded.
Uninhabited islands (Miskan, Umm an Namil, Bubiyan, Warbah) are excluded;
blocks and per-area postcodes stay out of scope, and governorate/area
name twins (Farwaniya, Ahmadi, Jahra, Hawalli, Mubarak Al-Kabeer) share
names by design; filter by type.

Kuwaiti addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country.

## Jordan

The bundled `JordanGeographyProvider` supplies the twelve ISO 3166-2
governorates as `State` rows and a two-level administrative hierarchy
(`governorate` > `liwa`). It is selected with
`SeedCountryGeographiesAction::execute('JO')` after countries are seeded.

Level 2 carries the 51 districts (liwa) from DOS Statistical Yearbook
2024 Table 2.4: Amman 9, Irbid 9, Karak 7, Balqa 5, Mafraq 4, Ma'an 4,
Zarqa 3, Tafilah 3, Ajloun 2, Aqaba 2, Madaba 2, Jerash 1. Canonical
names follow DOS romanization except where the shipped L1 spellings
already differ (Ajloun, Tafilah, Jerash, Deir Alla, Naour, Mahis and
Fuhais); DOS forms are kept as `official` aliases and
English-Wikipedia/citypopulation/PCGN spellings (Al-Quwairah, Shoubak,
Husseiniya, Kufrinjah, Wadi Al Seer, Aii, Faqou', Ruwayshid,
Quwaysimah, Jizeh, Mowaqqar, Hashimiyya) as `alternative` aliases.
DOS publishes no liwa codes, so L2 rows carry no `code`. Qada
(sub-districts) are out of scope and do not ship.

Jordanian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, and country.

## Oman

The bundled `OmanGeographyProvider` supplies the eleven ISO 3166-2
governorates as `State` rows and a two-level administrative hierarchy
(governorate → 63 wilayats). It is selected with
`SeedCountryGeographiesAction::execute('OM')` after countries are seeded.

Omani addresses are formatted per the UPU layout: street lines, a
3-digit postcode on its own line above the locality, and country.

## United Arab Emirates

The bundled `UnitedArabEmiratesGeographyProvider` supplies the seven
emirates as `State` rows and a single-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('AE')`
after countries are seeded.

The UAE has no postcode system — delivery is to P.O. Boxes only — so
the formatter stacks street lines, city, and country with no postcode
line.

## Saudi Arabia

The bundled `SaudiArabiaGeographyProvider` supplies the thirteen ISO
3166-2 regions as `State` rows (codes `01`–`12` and `14`; there is no
region `13`) and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SA')` after
countries are seeded.

Saudi addresses are formatted per the UPU home-delivery layout:
street lines, a 5-digit postcode on its own line above the locality,
and country. Short addresses (`RAGI2929` style) and the separate P.O.
Box layout are not generated.

## Egypt

The bundled `EgyptGeographyProvider` supplies the 27 ISO 3166-2
governorates as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('EG')` after countries are seeded.

Egyptian addresses are formatted per the UPU layout: street lines,
locality, governorate, a 7-digit postcode on its own line, and country.

## South Africa

The bundled `SouthAfricaGeographyProvider` supplies the nine ISO
3166-2 provinces as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ZA')` after countries are seeded.

South African addresses are formatted per the UPU layout: street
lines, locality, a 4-digit postcode below it, and country. The
province line is omitted when a postcode is present, per the UPU rule.

## Türkiye

The bundled `TurkiyeGeographyProvider` supplies the 81 ISO 3166-2
provinces as `State` rows and a two-level administrative hierarchy
(`province` > `district`). It is selected with
`SeedCountryGeographiesAction::execute('TR')` after countries are seeded.

Level 2 carries all 973 ilçeler. The 51 non-metropolitan provinces
each have a bare `Merkez` central district; the 30 metropolitan
provinces have multiple urban districts instead (no `Merkez` row).
The newest district is Derecik (Hakkâri), created in 2018 by Law 7148;
no district has been created since. Districts carry no `code`: no
official numeric ilçe code system was verified, so the column stays
empty. `Kazan` is kept as a `historic` alias (renamed Kahramankazan by
Law 6752 in 2016) and `Karadeniz Ereğli` as a `common` alias for
Zonguldak's `Ereğli`, which twins officially with Konya's `Ereğli`.
25 names twin across provinces (`Merkez` ×51, `Ereğli` ×2,
`Yenişehir` ×3, 22 pairs) — filter by `type` and parent, never by
name alone. YSK's qualified `{Province} Merkez` form is a YSK-table
convention, not the district name. Villages and neighbourhoods
(mahalle) are intentionally not bundled.

Turkish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}/{province}` with a 5-digit postcode, and
country. Sub-locality postcode suffixes (`06050-01` style) are not
generated.

## Pakistan

The bundled `PakistanGeographyProvider` supplies the seven ISO 3166-2
subdivisions as `State` rows — four provinces plus the Islamabad
Capital Territory, Gilgit-Baltistan, and Azad Jammu and Kashmir
(`Azad Kashmir` is aliased) — and a two-level administrative hierarchy
(province/territory → 174 districts). It is selected with
`SeedCountryGeographiesAction::execute('PK')` after countries are seeded.

Districts follow the late-2025 reorganization state: Punjab counts 42
(`Jampur` and `Taunsa` included), Khyber Pakhtunkhwa counts 40
(Chitral split into Lower/Upper plus `Central Dir`, `Paharpur`, and
`Upper Swat` from the October 2025 batch), and Balochistan includes
`Hub`, `Karezat`, and `Surab`. The January/May 2026 Balochistan batch
(`Tump`, Upper Dera Bugti, `Taftan`, `Wadh`, `Barshor`, Quetta
East/West) is intentionally excluded: unlike the clean October 2025
Khyber Pakhtunkhwa adds, its January Quetta City/Saddar and May Quetta
East/West notifications contradict each other. Tehsils are
intentionally not bundled.

Pakistani addresses are formatted per the UPU layout: street lines,
`{locality}-{postcode}` with a 5-digit dash-separated postcode, and
country.

## India

The bundled `IndiaGeographyProvider` supplies the 36 ISO 3166-2 states
and union territories as `State` rows (28 states, 8 union territories
including Ladakh and the merged Dadra and Nagar Haveli and Daman and
Diu) and a two-level administrative hierarchy (`state` > `district`).
It is selected with `SeedCountryGeographiesAction::execute('IN')` after
countries are seeded. State codes follow the 23 November 2023 ISO
amendment (`CG`, `OD`, `TS`, `UK`).

Level 2 carries 786 districts keyed by official LGD code
(`in:district:<lgd-code>`, LGD snapshot 31 May 2026): all 784 LGD rows
plus Mahe (599) and Yanam (601), which LGD dropped in 2024–25 but which
remain official Puducherry districts with Census 2011 codes 636/634.
Names follow current official spellings, including the January 2026
Delhi reorganisation (Shahdara dissolved; Old Delhi, Central North,
Outer North added), Kushavati, Hansi, Markapuram, Polavaram, Vav-Tharad,
Meluri, Sribhumi, Ahilyanagar, Chhatrapati Sambhajinagar, Dharashiv,
Bengaluru South and Narmadapuram, with former and LGD-variant names kept
as aliases. Three cross-state twins exist (Bilaspur, Hamirpur,
Pratapgarh) — filter by `type` and parent, never by name alone.
Excluded for lack of LGD codes: the five Ladakh districts notified 27
April 2026, the announced-but-unnotified Kalyan Singh Nagar (UP), and
West Bengal's unimplemented announced seven. Tehsils/blocks (L3) are out
of scope.

Indian addresses are formatted per the UPU layout: street lines,
locality, state, a 6-digit postcode on its own line, and country.
Secondary postcodes (`834001-34` style) are intentionally not bundled.

## United Kingdom

The bundled `UnitedKingdomGeographyProvider` supplies the four nations
(England, Scotland, Wales, Northern Ireland) as areas in a
single-level administrative hierarchy. The 221 ISO 3166-2 subdivisions
remain global `State` rows only; they are not imported as areas. It is
selected with `SeedCountryGeographiesAction::execute('GB')` after
countries are seeded.

British addresses are formatted per the UPU layout: street lines, post
town, the uppercased postcode on its own line, and country. The county
line is omitted when a postcode is present, per the UPU rule.

## Bangladesh

The bundled `BangladeshGeographyProvider` supplies the eight ISO
3166-2 divisions as `State` rows plus all 64 districts, with one
administrative hierarchy: 8 divisions → 64 districts (Dhaka 13,
Chattogram 11, Khulna 10, Rajshahi 8, Rangpur 8, Barishal 6, Sylhet 4,
Mymensingh 4). It is selected with
`SeedCountryGeographiesAction::execute('BD')` after countries are seeded.

Names follow the post-2018 official English spellings (`Barishal`,
`Chattogram`, `Bogura`, `Jashore`, `Cumilla`, `Jhalakathi`,
`Netrokona`); seeding also corrects the matching global state rows.
Only divisions link to states; districts are assignable through the
`district` role with their division selected first.

Bangladeshi addresses are formatted per the UPU layout: street lines,
an optional `thana` component, `{locality} - {postcode}` with a
4-digit postcode, and country.

## Morocco

The bundled `MoroccoGeographyProvider` supplies the twelve ISO 3166-2
regions as `State` rows plus all 75 second-level divisions, with one
administrative hierarchy: 12 regions → 62 provinces + 13 prefectures.
It is selected with `SeedCountryGeographiesAction::execute('MA')`
after countries are seeded.

Region names follow ISO (`Marrakech-Safi`, `Fès-Meknès`,
`Tanger-Tétouan-Al Hoceïma`, `L'Oriental`); the `(EH)` Western Sahara
annotations from the global source are stripped, and seeding corrects
the matching global state rows. Only regions link to states;
provinces and prefectures are assignable through the `province` role
with their region selected first.

Moroccan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country.

## China

The bundled `ChinaGeographyProvider` supplies 33 provincial-level
divisions as `State` rows (22 provinces, 5 autonomous regions, 4
municipalities, plus Hong Kong and Macao) and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CN')` after countries are seeded.

Taiwan carries its own country code (`TW`) with its own postal
system, so it is intentionally not a CN area. Prefecture-level cities,
counties, and districts are intentionally not bundled.

Chinese addresses are formatted per the UPU layout: street lines, an
optional city/district line, `{postcode} {province}` with a 6-digit
postcode, and country.

## Russia

The bundled `RussiaGeographyProvider` supplies the 83 ISO 3166-2
federal subjects as `State` rows (46 oblasts, 21 republics, 9 krais,
4 okrugs, Moscow and Saint Petersburg, and the Jewish Autonomous
Oblast) and a single-level administrative hierarchy. It is selected
with `SeedCountryGeographiesAction::execute('RU')` after countries are
seeded.

Crimea, Sevastopol, and the territories claimed in 2022 are not
ISO-recognized subdivisions and are intentionally absent. Nenets is
typed as an okrug (the global snapshot mistypes it), and krai/okrug
names carry their suffixes per the UPU province list.

Russian addresses are formatted as street lines, locality, subject,
a 6-digit postcode, and country — country last, per the UPU IB
recommendation. Domestic Russian convention prints the postcode after
the country instead; the formatter deliberately deviates.

## Germany

The bundled `GermanyGeographyProvider` supplies the 16 Länder as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('DE')` after
countries are seeded.

State names use German official forms (`Bayern`, `Sachsen`); the
seven common English exonyms (`Bavaria`, `Lower Saxony`, `North
Rhine-Westphalia`, `Rhineland-Palatinate`, `Saxony`, `Saxony-Anhalt`,
`Thuringia`) are kept as aliases.

German addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. No `D-`
prefix is ever added.

## France

The bundled `FranceGeographyProvider` supplies the 18 regions (13
metropolitan, 5 overseas) as `State` rows in a single-level
administrative hierarchy. The 101 departments and overseas
collectivities remain global `State` rows only; they are not imported
as areas. It is selected with
`SeedCountryGeographiesAction::execute('FR')` after countries are seeded.

French addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. CEDEX
suffixes are not generated.

## Italy

The bundled `ItalyGeographyProvider` supplies the 20 regions as
`State` rows and a single-level administrative hierarchy. Provinces
are intentionally not bundled: Friuli-Venezia Giulia replaced its
provinces with regional entities, Sardinia restructured twice in a
decade, and Sicily uses consortia, so no stable province layer exists
to ship. It is selected with
`SeedCountryGeographiesAction::execute('IT')` after countries are seeded.

Region names use Italian official forms (`Toscana`, `Sicilia`); the
eight common English exonyms (`Piedmont`, `Aosta Valley`, `Lombardy`,
`Trentino-South Tyrol`, `Tuscany`, `Apulia`, `Sicily`, `Sardinia`)
are kept as aliases.

Italian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality} {province}` with a 5-digit postcode, and
country. The two-letter province abbreviation comes from the optional
`province_code` address component and is omitted when absent.

## Japan

The bundled `JapanGeographyProvider` supplies the 47 prefectures as
`State` rows and a two-level administrative hierarchy (`prefecture` >
`municipality`). It is selected with
`SeedCountryGeographiesAction::execute('JP')` after countries are seeded.

Level 2 carries all 1,747 municipalities from the MIC R6.1.1 code table:
792 cities, 743 towns, 183 villages, the 23 Tokyo special wards (folded
in as municipalities), and the 6 Northern-Territories paper villages
(Shikotan, Tomari, Ruyobetsu, Rubetsu, Shana, Shibetoro — Japanese
claimed/notional rows under Hokkaido). Names use bare unmacroned
romanization (`Sapporo`, `Chiyoda`, `Naha`) with kanji in `native_name`;
municipality kind (city/town/village/ward) is carried by the kanji
suffix only. Thirteen same-prefecture name twins exist (Tomari ×2 in
Hokkaido, Fuchu city/town in Hiroshima, Toshima ward/village in Tokyo,
and ten more) plus ~100 cross-prefecture twins (Date, Fuchu) — filter
by `code` and parent, never by name alone. Ordinance-designated-city
wards (e.g. Osaka's 24 ku) are sub-municipal and intentionally not
bundled.

Japanese addresses are formatted per the UPU western layout: street
lines, `{city}, {prefecture}`, the `NNN-NNNN` postcode on its own
line, and country.

## United States

The bundled `UnitedStatesGeographyProvider` supplies 56 states,
districts, and territories as `State` rows (50 states, the District
of Columbia, American Samoa, Guam, the Northern Mariana Islands,
Puerto Rico, and the U.S. Virgin Islands) and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('US')` after countries are seeded.

The military postal regions (`AA`, `AE`, `AP`) and the Minor Outlying
Islands (`UM`) are postal constructs, not addressable geography, and
are intentionally not areas.

American addresses are formatted per USPS Publication 28: street
lines, `{locality} {ST} {ZIP}` with the state abbreviation resolved
from a full-name map (already-abbreviated values pass through
uppercased), and country.

## Spain

The bundled `SpainGeographyProvider` supplies the 17 autonomous
communities plus Ceuta and Melilla as `State` rows, with all 50
provinces, and one administrative hierarchy: 19 communities/cities →
50 provinces. It is selected with
`SeedCountryGeographiesAction::execute('ES')` after countries are seeded.

Community names use short English forms (`Extremadura`, `Asturias`,
`Murcia`, `Madrid`) while provinces keep official local spellings
(`A Coruña`, `Bizkaia`, `Gipuzkoa`, `Araba`, `Ourense`, `Illes
Balears`). Only communities link to states; provinces are assignable
through the `province` role with their community selected first.

Spanish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the province on its
own line, and country.

## Poland

The bundled `PolandGeographyProvider` supplies the 16 voivodeships as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('PL')` after
countries are seeded.

Voivodeship names use standard English exonyms (`Mazovia`, `Lesser
Poland`) since the official Polish forms are adjectives, not
standalone proper nouns. Powiats and gminas are intentionally not
bundled.

Polish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `NN-NNN` postcode, and country.

## Netherlands

The bundled `NetherlandsGeographyProvider` supplies the 12 provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NL')` after
countries are seeded.

Province names use Dutch official forms (`Noord-Brabant`,
`Noord-Holland`, `Zuid-Holland`). Municipalities are intentionally
not bundled.

Dutch addresses are formatted per the UPU layout: street lines,
`{postcode}  {locality}` with an uppercased `NNNN LL` postcode and
two spaces before the locality, and country.

## Nigeria

The bundled `NigeriaGeographyProvider` supplies the 36 states plus the
Abuja Federal Capital Territory as `State` rows and a two-level
administrative hierarchy (`state` > `lga`). It is selected with
`SeedCountryGeographiesAction::execute('NG')` after countries are seeded.

Level 2 carries the constitutional 774: 768 LGAs plus the 6 FCT Area
Councils (typed `area_council`, sharing the `lga` role). Names follow
the post-2023 gazetted forms from the Fifth Alteration (Afikpo, Edda,
Ghari, Yewa North/South, Atisbo, Obio-Akpor) with pre-2023 names kept
as `historic` aliases. `Aiyekire` keeps its constitutional spelling
with `Gbonyin` (state usage, preferred) and `Ayekire` as aliases; the
Barkin Ladi → Gwol rename failed in the Senate and is not applied.
Six cross-state twins exist (Obi, Bassa, Ifelodun, Irepodun, Surulere,
Nasarawa) — filter by `type` and parent, never by name alone.
State-created LCDAs (e.g. Lagos's 37) and the October 2025 NASS
state/LGA-creation talks are excluded: only gazetted LGAs ship.

Nigerian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 6-digit postcode, the state on its own
line, and country.

## Ethiopia

The bundled `EthiopiaGeographyProvider` supplies 14 regions and city
administrations as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ET')` after countries are seeded.

The Southern Nations, Nationalities, and Peoples' Region was dissolved
in August 2023 (split into Sidama, Southwest, South, and Central
Ethiopia). Seeding deletes any `SN` straggler rows, and the bundled
state data no longer ships the code. South Ethiopia (`SE`) and Central
Ethiopia (`CE`) have no ISO codes yet; those codes are provisional
and will be updated when ISO assigns them.

Ethiopian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

## Democratic Republic of the Congo

The bundled `DemocraticRepublicOfCongoGeographyProvider` supplies the
26 provinces as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CD')` after countries are seeded.

Congolese addresses are formatted per the UPU layout: street lines,
an optional commune line, `{postcode} {province}` with a 7-digit
postcode, and country.

## Tanzania

The bundled `TanzaniaGeographyProvider` supplies the 31 regions
(including Songwe, split from Mbeya in 2016) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TZ')` after countries are seeded.

Tanzanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line, and country. Districts and wards are intentionally not
bundled.

## Kenya

The bundled `KenyaGeographyProvider` supplies the 47 counties as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('KE')` after
countries are seeded.

Kenyan addresses are formatted per the UPU postal layout: street or
P.O. Box lines, the 5-digit postcode on its own line, then the town,
and country. The county line is omitted when a postcode is present.

## Sudan

The bundled `SudanGeographyProvider` supplies the 18 states as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SD')` after countries are seeded.

Sudanese addresses are formatted per the UPU layout: street lines, a
5-digit postcode on its own line above the locality, and country.

## Uganda

The bundled `UgandaGeographyProvider` supplies the four regions as
`State` rows in a single-level administrative hierarchy. The 130+
districts change almost yearly (new ones are split off regularly), so
they are intentionally not bundled — any snapshot would be stale on
arrival. It is selected with
`SeedCountryGeographiesAction::execute('UG')` after countries are seeded.

Ugandan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.

## Algeria

The bundled `AlgeriaGeographyProvider` supplies the 69 wilayas
(codes `49`–`58` from the 2019 expansion with corrected numbering,
plus `59`–`69` created by Law 26-06 in April 2026) as `State` rows
and a two-level administrative hierarchy (`wilaya` > `daira`). It is
selected with `SeedCountryGeographiesAction::execute('DZ')` after
countries are seeded.

Level 2 carries the 548 dairas: 482 in wilayas `01`–`48`, 26 in the
2019 batch, 40 in the 2026 batch. Daira boundaries follow décret
exécutif 91-306 as amended by décret 26-253 (JO 2026 n°52), which
moves all other dairas whole and creates exactly one new daira
(El Aricha, from split-off Sebdou communes); El Borma sits in
Ouargla per the 2021 formalization, superseding its 2019 Touggourt
assignment. Names use French official forms (`Alger`, not `Algiers`;
daira `Bou Saada` without the wilaya's â, daira `El Meniaa` against
wilaya `El Menia`). The cross-wilaya `Mansoura` twin (Bordj Bou
Arréridj + Ghardaïa) — filter by `type` and parent, never by name
alone. Communes (1,541) are intentionally not bundled.

Algerian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.

## Brazil

The bundled `BrazilGeographyProvider` supplies the 26 states plus the
Distrito Federal as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BR')` after countries are seeded.

Municipalities (5,500+) are intentionally not bundled.

Brazilian addresses are formatted per the UPU layout: street lines,
`{locality} - {ST}` with the two-letter state abbreviation resolved
from a full-name map, the `NNNNN-NNN` postcode on its own line, and
country.

## Mexico

The bundled `MexicoGeographyProvider` supplies the 32 federal
entities as `State` rows (all typed `state`, including Ciudad de
México, which has been state-equivalent since 2016) and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MX')` after countries are seeded.

Municipalities are intentionally not bundled.

Mexican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}, {abbrev}` with the state abbreviation from
the UPU list (`CDMX`, `EDOMEX`, `Q. ROO`, `TAMPS`), and country.

## Canada

The bundled `CanadaGeographyProvider` supplies the 10 provinces plus
the 3 territories as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CA')` after countries are seeded.

Canadian addresses are formatted per the UPU layout: street lines,
`{locality} {PR} {postcode}` with the two-letter province abbreviation
and uppercased `ANA NAN` postcode, and country.

## Australia

The bundled `AustraliaGeographyProvider` supplies the 6 states plus
the 2 mainland territories as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AU')` after countries are seeded.

External territories (Norfolk Island, Christmas Island, Cocos
Islands) carry their own postcodes and are intentionally not areas.

Australian addresses are formatted per the UPU layout: street lines,
`{locality}  {ST}  {postcode}` with two spaces between each part, and
country.

## Argentina

The bundled `ArgentinaGeographyProvider` supplies the 23 provinces
plus the Autonomous City of Buenos Aires as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AR')` after countries are seeded.

Departments and municipalities are intentionally not bundled.

Argentine addresses are formatted per the UPU layout: street lines,
`{CPA} {locality}` with the `XNNNNLLL` postcode left of the locality,
and country.

## Colombia

The bundled `ColombiaGeographyProvider` supplies the 32 departments
plus Bogotá D.C. as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CO')` after countries are seeded.

Municipalities are intentionally not bundled.

Colombian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 6-digit postcode, the department on
its own line, and country.

## Peru

The bundled `PeruGeographyProvider` supplies the 25 regions plus the
Lima metropolitan municipality as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PE')` after countries are seeded.

Provinces and districts are intentionally not bundled. The `Huánuco`
spelling is corrected at seed.

Peruvian addresses are formatted per the UPU layout: street lines, a
5-digit postcode on its own line, the department on its own line, and
country.

## Vietnam

The bundled `VietnamGeographyProvider` supplies the post-merger 34
provincial-level divisions (28 provinces, 6 municipalities:
Hà Nội, Hải Phòng, Huế, Đà Nẵng, Cần Thơ, Hồ Chí Minh City) as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('VN')` after
countries are seeded.

The June 2025 merger (63 → 34, districts eliminated) is reflected as
shipped; seeding renames `Thừa Thiên-Huế` to `Huế` and retypes Hải
Phòng, Hồ Chí Minh City, and Huế as municipalities. Communes and
wards are intentionally not bundled.

Vietnamese addresses are formatted per the UPU layout: street and
ward lines, `{province} {postcode}` with a 5-digit postcode, and
country.

## Thailand

The bundled `ThailandGeographyProvider` supplies the 76 provinces
plus Bangkok and Pattaya as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TH')` after countries are seeded.

Districts (amphoe) and sub-districts are intentionally not bundled.

Thai addresses are formatted per the UPU layout: street lines,
`{district}, {province}`, the 5-digit postcode on its own line, and
country.

## Philippines

The bundled `PhilippinesGeographyProvider` supplies the 82 provinces
as areas in a single-level administrative hierarchy, including the
2022 Maguindanao split (`Maguindanao del Norte` / `Maguindanao del
Sur`) and `Davao de Oro`. It is selected with
`SeedCountryGeographiesAction::execute('PH')` after countries are seeded.

The 17 regions stay global `State` rows only (with `Bangsamoro` and
`Cordillera Administrative Region` name corrections at seed): regions
are churny (ARMM→BARMM in 2019, Negros Island Region re-created in
2024 without an ISO code), while provinces are the address-relevant
unit. Cities and barangays are intentionally not bundled.
`Samar` keeps `Western Samar` as an alias.

Filipino addresses are formatted per the UPU layout: street lines,
the municipality on its own line with `{postcode} {province}` below
it for provincial addresses (`{postcode} {municipality}, METRO MANILA`
for Metro Manila, `{postcode} {locality}` when no province is set),
and country.

## South Korea

The bundled `SouthKoreaGeographyProvider` supplies the 17
provincial-level divisions as `State` rows (8 provinces including the
special self-governing Gangwon State, Jeju, and Jeonbuk State — the
2023/2024 official renames, with `Gangwon` and `North Jeolla` aliased
— 6 metropolitan cities, Seoul, and Sejong) and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KR')` after countries are seeded.

Cities, districts, and dongs are intentionally not bundled.

South Korean addresses are formatted per the UPU layout: street
lines, `{province or city} {postcode}` with a 5-digit postcode, and
country.

## Taiwan

The bundled `TaiwanGeographyProvider` supplies the 22 divisions (6
special municipalities, 3 cities, 13 counties) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TW')` after countries are seeded.

Townships, districts, and villages are intentionally not bundled.

Taiwanese addresses are formatted per Chunghwa Post (no UPU sheet is
published for Taiwan): street lines, `{locality} {postcode}` with the
6-digit 3+3 postcode, and country.

## Ukraine

The bundled `UkraineGeographyProvider` supplies the 24 oblasts plus
Kyiv, Sevastopol, and the Autonomous Republic of Crimea as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UA')` after countries are seeded.

Raions and hromadas are intentionally not bundled. Oblast names use
the ISO adjectival forms (`Kyivska`, `Lvivska`).

Ukrainian addresses are formatted per the UPU layout: street lines,
locality, oblast, a 5-digit postcode on its own line, and country.

## Iraq

The bundled `IraqGeographyProvider` supplies the 19 governorates as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IQ')` after
countries are seeded.

`Iqlim Kurdistan` is ISO-listed as a region (`IQ-KR`) overlapping
Erbil, Dohuk, Sulaymaniyah, and Halabja — not a governorate. Since
governorates are the address-relevant unit, seeding deletes any `KR`
straggler rows and the bundled state data no longer ships the code.
Halabja (governorate in Kurdistan since 2014, federally since April
2025) has no ISO code yet; `HL` follows UK government usage pending
ISO assignment. Districts are intentionally not bundled.

Iraqi addresses are formatted per the UPU layout: street lines,
`{city}, {governorate}`, the 5-digit postcode on its own line, and
country.

## Ghana

The bundled `GhanaGeographyProvider` supplies the 16 regions
(including the six created in 2019) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GH')` after countries are seeded.

Districts are intentionally not bundled.

Ghanaian addresses are formatted per the UPU layout: street or P.O.
Box lines, `{locality} {postcode}` (accepting both short and digital
`GA-183-8164` forms as given), the region on its own line, and
country.

## Angola

The bundled `AngolaGeographyProvider` supplies the 18 ISO provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AO')` after
countries are seeded.

The September 2024 law creating three more provinces (Icolo e Bengo,
Moxico Leste, and the Cuando/Cubango split, 21 total) is enacted but,
per official sources, not yet implemented — so the shipped 18 track
implemented reality, and the new units will be added once live.
Municipalities are intentionally not bundled.

Angola has no postcode system, so the formatter stacks street lines,
city, and country with no postcode line.

## Cameroon

The bundled `CameroonGeographyProvider` supplies the 10 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CM')` after
countries are seeded.

Departments and communes are intentionally not bundled.

Cameroon has no postcode system, so the formatter stacks street
lines, city, and country with no postcode line.

## Madagascar

The bundled `MadagascarGeographyProvider` supplies the 6 provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MG')` after
countries are seeded.

The 23 post-2009 regions have no ISO codes (ISO 3166-2:MG still lists
the 6 former faritany) and are intentionally not bundled; the 6
remain postally relevant since the postcode's first digit routes by
old province.

Malagasy addresses are formatted per the UPU layout: street lines,
`{postcode} {town}` with a 3-digit postcode, and country.

## Afghanistan

The bundled `AfghanistanGeographyProvider` supplies the 34 provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AF')` after
countries are seeded.

Districts are intentionally not bundled. The `Ghor` and `Kunduz`
spellings are corrected at seed.

Afghan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the province on its
own line, and country.

## Mozambique

The bundled `MozambiqueGeographyProvider` supplies the 10 provinces
plus Maputo City as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MZ')` after countries are seeded.

Districts are intentionally not bundled. `Maputo Province` and
`Maputo City` are disambiguated at seed.

Mozambican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the province on its
own line, and country.

## Uzbekistan

The bundled `UzbekistanGeographyProvider` supplies the 12 regions
plus Karakalpakstan and Tashkent City as `State` rows and a
two-level administrative hierarchy (`region` > `tuman`). It is
selected with `SeedCountryGeographiesAction::execute('UZ')` after
countries are seeded.

Level 2 carries 175 tumanlar plus the 31 cities of regional
subordination (typed `city`, sharing the `tuman` role — the
Indonesia regency+city precedent). L1 names keep their established
forms while L2 names use official Uzbek Latin endonyms (`Buxoro`,
`Farg'ona`, `Kattaqo'rg'on`, `Toshkent`); O'/G' use the ASCII
apostrophe. Seventeen tuman/city twins share a parent (Andijon,
Buxoro, Farg'ona, Kogon, Namangan, Nukus, Qarshi, Shahrisabz,
Samarqand, Kattaqo'rg'on, Guliston, Termiz, Bekobod, Ohangaron,
Yangiyo'l, Urganch, Xiva) — filter by `type` and parent, never by
name alone. `city` spans both levels (L1 Tashkent City vs L2
regional cities), disambiguated by level. Verified `alternative`
aliases: `Shayxontohur`, `Sergeli`, `Xazorasp`. Namangan city's
Davlatobod and Yangi Namangan districts are excluded: their parent
is Namangan city (L2), making them L3, which this provider does
not ship.

Region names use official Uzbek Latin forms (`Qashqadaryo`,
`Samarqand`, `Sirdaryo`, `Surxondaryo`, `Navoiy`, `Xorazm`).
`Tashkent Region` and `Tashkent City` are disambiguated at seed.

Uzbek addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with a 6-digit postcode, the region on its
own line (omitted when it duplicates the city), and country.

## Myanmar

The bundled `MyanmarGeographyProvider` supplies the 7 regions, 7
states, and Naypyidaw as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MM')` after countries are seeded.

Townships and districts are intentionally not bundled.

Myanmar addresses are formatted per the UPU layout: street lines,
`{locality}, {postcode}` with a 7-digit postcode, the region or state
on its own line, and country.

## Cambodia

The bundled `CambodiaGeographyProvider` supplies the 24 provinces plus
Phnom Penh municipality as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KH')` after countries are seeded.

Code `18` seeds the official `Preah Sihanouk` name with `Sihanoukville`
kept as an alternative area name. Districts (srok/khan) and communes
are intentionally not bundled.

Cambodian addresses are formatted per the UPU layout: street lines,
the city above `{province} {postcode}` with a 6-digit postcode, and
country.

## Laos

The bundled `LaosGeographyProvider` supplies the 17 provinces plus the
Vientiane Prefecture as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LA')` after countries are seeded.

The Vientiane province (`VI`) and Vientiane Prefecture (`VT`) are
separate areas sharing a name; districts (muang) are intentionally not
bundled.

Laotian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the province on its
own line when both are set, and country.

## Timor-Leste

The bundled `TimorLesteGeographyProvider` supplies the 13 ISO 3166-2
municipalities (Oecusse is a special administrative region) plus
Atauro — split from Dili in 2022 with provisional code `AT`, since
ISO has not assigned one yet — as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TL')` after countries are seeded.

Timor-Leste joined ASEAN as the 11th member in October 2025.
Administrative posts are intentionally not bundled.

Timorese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a `TL` + 5-digit postcode, and country.
Distinct city and municipality join as `{city} - {municipality}
{postcode}`; equal values print once.

## Armenia

The bundled `ArmeniaGeographyProvider` supplies the 10 regions plus
Yerevan as `State` rows and a single-level administrative hierarchy.
It is selected with
`SeedCountryGeographiesAction::execute('AM')` after countries are seeded.

Armenian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the region on its
own line when both are set, and country.

## Azerbaijan

The bundled `AzerbaijanGeographyProvider` supplies the 66 districts,
11 municipalities, and the Nakhchivan Autonomous Republic as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AZ')` after countries are seeded.

The Lankaran, Shaki, Yevlakh, and Nakhchivan municipality/district
pairs share names by design; filter by type.

Azerbaijani addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `AZ` + 4-digit postcode, the district
or region on its own line when both are set, and country.

## Bhutan

The bundled `BhutanGeographyProvider` supplies the 20 dzongkhags as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BT')` after
countries are seeded. Gewogs are intentionally not bundled.

Bhutanese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the dzongkhag on its
own line when it differs, and country.

## Cyprus

The bundled `CyprusGeographyProvider` supplies the 6 districts with
bilingual names as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CY')` after countries are seeded.

Cypriot addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
Inbound international mail prefixes `CY-`; the formatter prints the
postcode exactly as supplied.

## Georgia

The bundled `GeorgiaGeographyProvider` supplies the 9 regions plus
the Abkhazia and Adjara autonomous republics and Tbilisi as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GE')` after countries are seeded.

Georgian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the region on its
own line when both are set, and country.

## Hong Kong

The bundled `HongKongGeographyProvider` supplies the 18 districts as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('HK')` after
countries are seeded.

Hong Kong has no postcode system. Addresses are formatted per the UPU
layout: street lines, the district, and country; any supplied code
prints on its own line for form-compatibility.

## Iran

The bundled `IranGeographyProvider` supplies the 31 ostans
(provinces) as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('IR')` after countries are seeded.
Counties (shahrestan) are intentionally not bundled.

Iranian addresses are formatted per the UPU layout: street lines,
the locality, the province, the 10-digit postcode on its own line,
and country.

## Israel

The bundled `IsraelGeographyProvider` supplies the 6 districts as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IL')` after
countries are seeded. Sub-districts are intentionally not bundled.

Israeli addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 7-digit postcode (legacy 5-digit codes
pass through), and country.

## Kazakhstan

The bundled `KazakhstanGeographyProvider` supplies the 17 regions
plus Almaty, Astana, and Shymkent as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KZ')` after countries are seeded.

The Almaty region and Almaty city share a name by design; filter by
type.

Kazakh addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with either the legacy 6-digit or the new
`A99A9A9` postcode, the region on its own line when both are set, and
country.

## Kyrgyzstan

The bundled `KyrgyzstanGeographyProvider` supplies the 7 regions plus
Bishkek and Osh as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KG')` after countries are seeded.

The Osh region and Osh city share a name by design; filter by type.

Kyrgyz addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the region on its
own line when both are set, and country.

## Lebanon

The bundled `LebanonGeographyProvider` supplies the 8 governorates
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LB')` after
countries are seeded. Cazas are intentionally not bundled.

Lebanese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with the optional 4+4-digit LibanPost code,
the governorate on its own line when both are set, and country.

## Maldives

The bundled `MaldivesGeographyProvider` supplies the 20 atolls plus
Addu City as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MV')` after countries are seeded.

Maldivian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the atoll on its own
line when both are set, and country.

## Mongolia

The bundled `MongoliaGeographyProvider` supplies the 21 aimags
(provinces) plus Ulaanbaatar as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MN')` after countries are seeded.

Mongolian addresses are formatted per the UPU layout: street lines,
the district above `{province} {postcode}` with a 5-digit postcode
(`-NNNN` extensions pass through), and country.

## Nepal

The bundled `NepalGeographyProvider` supplies the 7 federal provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NP')` after
countries are seeded. Districts are intentionally not bundled.

Nepali addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the province on its
own line when both are set, and country.

## North Korea

The bundled `NorthKoreaGeographyProvider` supplies the 9 provinces
plus Kaesong, Nampho, Pyongyang, and Rason as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KP')` after countries are seeded.

North Korea has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, and country; any supplied
code prints on its own line.

## Palestine

The bundled `PalestineGeographyProvider` supplies the 16 West Bank
and Gaza governorates as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PS')` after countries are seeded.

Palestinian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a `P` + 7-digit postcode (short `P` + 3
passes through), and country.

## Sri Lanka

The bundled `SriLankaGeographyProvider` supplies the 9 provinces
as `State` rows with the 25 districts as level-2 areas (5 Northern,
3 each Western/Central/Southern/Eastern, 2 each North Western/North
Central/Uva/Sabaragamuwa) in a two-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('LK')`
after countries are seeded.

Sri Lankan addresses are formatted per the UPU layout: street lines,
the locality, the province when it differs, the 5-digit postcode on
its own line, and country.

## Syria

The bundled `SyriaGeographyProvider` supplies the 14 provinces as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SY')` after
countries are seeded.

Syria has no live postcode system (a 4-digit scheme was announced but
never confirmed). Addresses print street lines, the locality, and
country; any supplied code prints on its own line.

## Tajikistan

The bundled `TajikistanGeographyProvider` supplies Khatlon, Sughd,
Gorno-Badakhshan, Dushanbe, and the Districts under Republic
Administration as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TJ')` after countries are seeded.

Tajik addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the region on its
own line when both are set, and country.

## Turkmenistan

The bundled `TurkmenistanGeographyProvider` supplies the 5 regions
plus Ashgabat as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TM')` after countries are seeded.

Turkmen addresses are formatted per the UPU layout: street lines,
the locality, the region when it differs, the 6-digit postcode on
its own line, and country.

## Yemen

The bundled `YemenGeographyProvider` supplies the 21 governorates
plus Amanat Al Asimah (the Sanaa municipality) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('YE')` after countries are seeded.

Yemen has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the governorate when it differs,
and country; any supplied code prints on its own line.

## Benin

The bundled `BeninGeographyProvider` supplies the 12 departments
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BJ')` after
countries are seeded.

Benin has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, and country; any supplied code
prints on its own line.
## Botswana

The bundled `BotswanaGeographyProvider` supplies the 10 districts,
Gaborone, Francistown, and 4 towns as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BW')` after countries are seeded.

Botswana has no postcode system. Addresses are formatted per the UPU
layout: P.O. box or private bag lines, the town, and country; any
supplied code prints on its own line.
## Burkina Faso

The bundled `BurkinaFasoGeographyProvider` supplies the 17 regions
and 47 provinces flat at level 1 as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BF')` after countries are seeded.
The July 2025 reform renamed all 13 regions, renamed 5 provinces
(`Koosin`, `Gobnangou`, `Djelgodji`, `Sandbondtenga`, `Bassitenga`),
and added 4 regions plus `Karo-Peli` and `Dyamongou` provinces.
Renamed divisions keep their former codes; region codes `14`–`17`
and province codes `KAR`/`DYA` are provisional pending ISO 3166-2:BF.

Burkinabe addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line when both are set, and country.
## Burundi

The bundled `BurundiGeographyProvider` supplies the 5 provinces
(Buhumuza, Bujumbura, Burunga, Butanyerera, Gitega) as `State` rows
and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BI')` after countries are
seeded. The July 2025 reform replaced the former 18 provinces; ISO
3166-2:BI has not issued new codes, so the bundled codes 01-05 are
provisional local numbers pending ISO.

Burundi has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the commune, the province, and country; any
supplied code prints on its own line.
## Cape Verde

The bundled `CapeVerdeGeographyProvider` supplies the 22
municipalities plus the Barlavento and Sotavento island groups as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CV')` after
countries are seeded. Cabo Verde is the official name since 2013;
the bundled data keeps the `Cape Verde` spelling.

Cape Verdean addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit (or 7-digit
`NNNN-NNN`) postcode, and country.
## Central African Republic

The bundled `CentralAfricanRepublicGeographyProvider` supplies the
15 prefectures plus the Bangui commune and Nana-Grébizi as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CF')` after countries are seeded.

The country has no postcode system. Addresses are formatted per the
UPU layout: P.O. box lines, the locality, and country; any supplied
code prints on its own line.
## Chad

The bundled `ChadGeographyProvider` supplies the 23 provinces as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TD')` after
countries are seeded.

Chad has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the province when it differs,
and country; any supplied code prints on its own line.
## Comoros

The bundled `ComorosGeographyProvider` supplies the 3 islands as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('KM')` after
countries are seeded.

Comoros has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the island when it differs,
and country; any supplied code prints on its own line.
## Congo

The bundled `CongoGeographyProvider` supplies the 12 departments
of the Republic of Congo as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CG')` after countries are seeded.

Congo has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line.
## Ivory Coast

The bundled `IvoryCoastGeographyProvider` supplies the 12 districts
plus the Abidjan and Yamoussoukro autonomous districts as `State`
rows and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CI')` after countries are seeded.

Ivory Coast has no postcode system; the 2-digit office code on box
lines is routing, not a postcode. Addresses print street lines, the
locality, and country; any supplied code prints on its own line.
## Djibouti

The bundled `DjiboutiGeographyProvider` supplies the 5 regions
plus Djibouti City as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DJ')` after countries are seeded.

Djiboutian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
## Dominican Republic

The bundled `DominicanRepublicGeographyProvider` supplies the 10
planning regions as `State` rows with the 31 provinces plus the
Distrito Nacional as level-2 areas (4 each Cibao Nordeste/Cibao
Noroeste/Valdesia/Enriquillo, 3 each Cibao Norte/Cibao Sur/Higuamo/Yuma,
2 each El Valle/Ozama; Distrito Nacional under Ozama) in a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DO')` after countries are seeded.

Dominican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
## Equatorial Guinea

The bundled `EquatorialGuineaGeographyProvider` supplies the 2
regions as `State` rows with the 8 provinces as level-2 areas
(3 Insular, 5 Río Muni) in a two-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('GQ')`
after countries are seeded.

Equatorial Guinea has no postcode system. Addresses are formatted
per the UPU layout: street lines, the locality, the province when it
differs, and country; any supplied code prints on its own line.
## Eritrea

The bundled `EritreaGeographyProvider` supplies the 6 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ER')` after
countries are seeded.

Eritrea has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line.
## Gabon

The bundled `GabonGeographyProvider` supplies the 9 provinces as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GA')` after
countries are seeded.

Gabonese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 2-digit zone, and country. The full
UPU line adds the delivery-office code right (`NN LOCALITY NN`);
only the zone is represented since the office half has no field.
## Gambia

The bundled `GambiaGeographyProvider` supplies the 5 divisions
plus Banjul as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GM')` after countries are seeded.

Gambia has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the division when it differs,
and country; any supplied code prints on its own line.
## Guinea

The bundled `GuineaGeographyProvider` supplies the 7 regions
plus Conakry as `State` rows with the 33 prefectures as level-2
areas (6 Nzérékoré, 5 each Boké/Kankan/Kindia/Labé, 4 Faranah,
3 Mamou; Conakry childless) in a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GN')` after countries are seeded.
Region/prefecture name twins (Boké, Faranah, Kankan, Kindia, Labé,
Mamou, Nzérékoré) share names by design; filter by type.

Guinean addresses are formatted per the UPU layout: P.O. box lines,
`{postcode} {locality}` with a 3-digit radical, and country.
## Guinea-Bissau

The bundled `GuineaBissauGeographyProvider` supplies the 3
provinces, 8 regions, and the Bissau sector as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GW')` after countries are seeded.

Bissau-Guinean addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
## Lesotho

The bundled `LesothoGeographyProvider` supplies the 10 districts
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LS')` after
countries are seeded.

Basotho addresses are formatted per the UPU layout: P.O. box lines,
`{locality} {postcode}` with a 3-digit postcode, and country.
## Liberia

The bundled `LiberiaGeographyProvider` supplies the 15 counties
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LR')` after
countries are seeded.

Liberian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
system is officially defined but flagged not-in-use by UPU, so codes
stay optional; Monrovia zone suffixes pass through as supplied.
## Libya

The bundled `LibyaGeographyProvider` supplies the 22 popularates
(sha'biyat) as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LY')` after countries are seeded.

Libya has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line.
## Malawi

The bundled `MalawiGeographyProvider` supplies the 3 regions
as `State` rows with the 28 districts as level-2 areas (13 Southern,
9 Central, 6 Northern) in a two-level administrative hierarchy. It
is selected with `SeedCountryGeographiesAction::execute('MW')` after
countries are seeded.

Malawian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the region on its
own line when both are set, and country.
## Mali

The bundled `MaliGeographyProvider` supplies the 10 regions plus
the Bamako district as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ML')` after countries are seeded.

Mali has no postcode system. Addresses are formatted per the UPU
layout: street lines, the quarter, the locality, and country; any
supplied code prints on its own line.
## Mauritania

The bundled `MauritaniaGeographyProvider` supplies the 15 regions
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MR')` after
countries are seeded.

Mauritania has no postcode system. Addresses are formatted per the
UPU layout: P.O. box lines, the locality, and country; any supplied
code prints on its own line.
## Mauritius

The bundled `MauritiusGeographyProvider` supplies the 9 districts
plus Agaléga, Rodrigues, and Saint Brandon as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MU')` after countries are seeded.

Mauritian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode (`R` + 4 digits on
Rodrigues), and country.
## Namibia

The bundled `NamibiaGeographyProvider` supplies the 14 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NA')` after
countries are seeded.

Namibian addresses are formatted per the UPU layout: street or box
lines, the locality, the 5-digit postcode on its own line, and
country.
## Niger

The bundled `NigerGeographyProvider` supplies the 7 regions plus
the Niamey urban community as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NE')` after countries are seeded.

Nigerien addresses are formatted per the UPU layout: P.O. box lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Rwanda

The bundled `RwandaGeographyProvider` supplies the 4 provinces
plus Kigali as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RW')` after countries are seeded.

Rwanda has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the province when it differs,
and country; any supplied code prints on its own line.
## Sao Tome and Principe

The bundled `SaoTomeAndPrincipeGeographyProvider` supplies the 6
districts plus the Príncipe autonomous region as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ST')` after countries are seeded.

The country has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, and country; any supplied
code prints on its own line.
## Senegal

The bundled `SenegalGeographyProvider` supplies the 14 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SN')` after
countries are seeded.

Senegalese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode (often written `CP
NNNNN`), and country.
## Seychelles

The bundled `SeychellesGeographyProvider` supplies the 27 districts
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SC')` after
countries are seeded.

Seychelles has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, the island, and country; any
supplied code prints on its own line.
## Sierra Leone

The bundled `SierraLeoneGeographyProvider` supplies the 4 provinces
plus the Western Area as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SL')` after countries are seeded.

Sierra Leone has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, the province when it differs,
and country; any supplied code prints on its own line.
## Somalia

The bundled `SomaliaGeographyProvider` supplies the 18 regions
(gobolka) as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SO')` after countries are seeded.

Somalia has no operational postcode system; the UPU paper format
(`AA NNNNN` right of the locality) was never taken into use.
Addresses print P.O. box lines, the locality, and country; any
supplied code prints on its own line.
## South Sudan

The bundled `SouthSudanGeographyProvider` supplies the 10 states
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SS')` after
countries are seeded.

South Sudan has no postcode system. Addresses are formatted per the
UPU layout: street or box lines, the town, the state when it differs,
and country; any supplied code prints on its own line.
## Eswatini

The bundled `EswatiniGeographyProvider` supplies the 4 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SZ')` after
countries are seeded.

Eswatini addresses are formatted per the UPU layout: P.O. box lines,
the locality, the region-letter + 3-digit postcode on its own line,
and country.
## Togo

The bundled `TogoGeographyProvider` supplies the 5 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TG')` after
countries are seeded.

Togo has no postcode system. Addresses are formatted per the UPU
layout: P.O. box or street lines, the locality, the region when it
differs, and country; any supplied code prints on its own line.
## Tunisia

The bundled `TunisiaGeographyProvider` supplies the 24 governorates
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TN')` after
countries are seeded.

Tunisian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Zambia

The bundled `ZambiaGeographyProvider` supplies the 10 provinces as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ZM')` after
countries are seeded.

Zambian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, and country. Codes
are routinely omitted in practice, so the formatter never requires
one.
## Zimbabwe

The bundled `ZimbabweGeographyProvider` supplies the 10 provinces
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ZW')` after
countries are seeded.

Zimbabwe has no postcode system. Addresses are formatted per the UPU
layout: street lines, the suburb, the city, and country; any supplied
code prints on its own line.

## Albania

The bundled `AlbaniaGeographyProvider` supplies the 12 counties
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AL')` after
countries are seeded.

Albanian addresses are formatted per the UPU layout: street lines,
the 4-digit postcode on its own line above the locality, the county
when it differs, and country.
## Andorra

The bundled `AndorraGeographyProvider` supplies the 7 parishes
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AD')` after
countries are seeded.

Andorran addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `AD` + 3-digit postcode, and country.
## Austria

The bundled `AustriaGeographyProvider` supplies the 9 states as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AT')` after
countries are seeded.

Austrian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Belarus

The bundled `BelarusGeographyProvider` supplies the 6 oblasts
plus Minsk as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BY')` after countries are seeded.
The Minsk oblast and Minsk city share a name by design; filter by type.

Belarusian addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with a 6-digit postcode, the oblast on its
own line when both are set, and country.
## Belgium

The bundled `BelgiumGeographyProvider` supplies the 3 regions
as `State` rows with the 10 provinces as level-2 areas (5 Flanders,
5 Wallonia; Brussels-Capital childless) in a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BE')` after countries are seeded.

Belgian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. `B-`
and `BE-` prefixes are forbidden by bpost and are never added.
## Bosnia and Herzegovina

The bundled `BosniaAndHerzegovinaGeographyProvider` supplies the
Federation, Republika Srpska, and Brčko District as `State` rows
and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BA')` after countries are seeded.

Bosnian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
## Bulgaria

The bundled `BulgariaGeographyProvider` supplies the 28 districts
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BG')` after
countries are seeded.

Bulgarian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Croatia

The bundled `CroatiaGeographyProvider` supplies the 20 counties
plus the City of Zagreb (code `21`, county-level city) as `State`
rows and a single-level administrative hierarchy. It is selected
with `SeedCountryGeographiesAction::execute('HR')` after countries
are seeded.

Croatian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
Inbound international mail prefixes `HR-`; the formatter prints the
postcode exactly as supplied.
## Czech Republic

The bundled `CzechRepublicGeographyProvider` supplies the 13
regions plus Praha as `State` rows with the 76 districts as level-2
areas (12 Středočeský, 7 each Jihomoravský/Jihočeský/Ústecký/Plzeňský,
6 Moravskoslezský, 5 each Vysočina/Královéhradecký/Olomoucký, 4 each
Liberecký/Pardubický/Zlínský, 3 Karlovarský; Praha childless) in a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CZ')` after countries are seeded.

Czech addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `NNN NN`
(Prague delivery-district suffixes pass through), and country.
## Denmark

The bundled `DenmarkGeographyProvider` supplies the 5 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('DK')` after
countries are seeded.

Danish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
optional `DK-` prefix passes through when supplied.
## Estonia

The bundled `EstoniaGeographyProvider` supplies the 15 counties
as `State` rows with the 78 municipalities as level-2 areas (16 Harju,
8 each Tartu/Lääne-Viru, 7 each Ida-Viru/Pärnu, 5 Võru, 4 each
Rapla/Viljandi, 3 each Lääne/Järva/Jõgeva/Põlva/Saare/Valga, 1 Hiiu)
in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('EE')` after countries are seeded.
Toila merged into Jõhvi in 2025 and is no longer listed.
County/municipality name twins share names by design; filter by type.

Estonian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
## Fiji

The bundled `FijiGeographyProvider` supplies the 4 divisions plus
Rotuma as `State` rows with the 14 provinces as level-2 areas (5
Central, 3 each Eastern/Northern/Western; Rotuma standalone) in a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('FJ')` after countries are seeded.

Fiji has no postcode system. Addresses print street lines, the
locality, and country; any supplied code prints on its own line.
## Finland

The bundled `FinlandGeographyProvider` supplies the 18 regions
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('FI')` after
countries are seeded.

Finnish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. The
optional `FI-` prefix passes through when supplied.
## Greece

The bundled `GreeceGeographyProvider` supplies the 13
administrative regions plus Mount Athos (code `69`) as `State` rows
and a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GR')` after countries are seeded.

Greek addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `NNN NN`,
and country.
## Hungary

The bundled `HungaryGeographyProvider` supplies the 20 counties,
22 cities with county rights, and Budapest as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('HU')` after countries are seeded.

Hungarian addresses follow international one-line practice: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
(Domestic Hungarian order prints the postcode on its own line below
the street, but the locality-before-street domestic layout does not
fit the package's lines-first convention.)
## Iceland

The bundled `IcelandGeographyProvider` supplies the 8 regions and
64 municipalities flat at level 1 as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('IS')` after countries are seeded.

Icelandic addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 3-digit postcode, and country.
## Ireland

The bundled `IrelandGeographyProvider` supplies the 4 provinces
as `State` rows with the 26 counties as level-2 areas (12 Leinster,
6 Munster, 5 Connacht, 3 Ulster) in a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('IE')` after countries are seeded.

Irish addresses are formatted per the UPU layout: street lines, the
locality, the county, the Eircode on its own line, and country.
## Kosovo

The bundled `KosovoGeographyProvider` supplies the 7 districts
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('XK')` after
countries are seeded.

Kosovar addresses are formatted per the postal convention: street
lines, `{postcode} {locality}` with a 5-digit postcode, and country.
## Latvia

The bundled `LatviaGeographyProvider` supplies the 36
municipalities plus 7 state cities as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LV')` after countries are seeded.
The Jelgava, Rēzekne, and Ventspils municipality/city pairs share
names by design; filter by type.

Latvian addresses are formatted per the UPU layout: street lines,
`{locality}, {postcode}` with an `LV-NNNN` postcode, and country.
## Liechtenstein

The bundled `LiechtensteinGeographyProvider` supplies the 11
communes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LI')` after countries are seeded.
Postal services follow Swiss rules.

Liechtenstein addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
## Lithuania

The bundled `LithuaniaGeographyProvider` supplies the 10 counties
and 60 municipalities flat at level 1 as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LT')` after countries are seeded.
The Alytus, Kaunas, Šiauliai, and Vilnius city/district pairs share
both name and type in the source data, so their area slugs carry a
code suffix (e.g. `alytus-02`).

Lithuanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
International mail prefixes `LT-`; the formatter prints the postcode
exactly as supplied.
## Luxembourg

The bundled `LuxembourgGeographyProvider` supplies the 12 cantons
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LU')` after
countries are seeded. Canton codes follow current ISO 3166-2:LU
(`GR` for Grevenmacher, `LU` for Luxembourg).

Luxembourg addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `L-NNNN` postcode, and country.
## Malta

The bundled `MaltaGeographyProvider` supplies the 68 local
councils as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MT')` after countries are seeded.

Maltese addresses are formatted per the UPU layout: street lines,
the locality, the `AAA NNNN` postcode on its own line, and country.
## Moldova

The bundled `MoldovaGeographyProvider` supplies the 32 districts,
3 cities, Gagauzia, and Transnistria as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MD')` after countries are seeded.

Moldovan addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with an `MD-NNNN` postcode, and country.
The prefix passes through as supplied.
## Monaco

The bundled `MonacoGeographyProvider` supplies the 17 quarters
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MC')` after
countries are seeded.

Monegasque addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit `98xxx` postcode, and country.
## Montenegro

The bundled `MontenegroGeographyProvider` supplies the 25
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ME')` after countries are seeded.

Montenegrin addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 5-digit postcode, and country.
## North Macedonia

The bundled `NorthMacedoniaGeographyProvider` supplies the 80
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MK')` after countries are seeded.

Macedonian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Norway

The bundled `NorwayGeographyProvider` supplies the 15 counties
plus Svalbard and Jan Mayen as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NO')` after countries are seeded.
The old 4-digit `N-` prefix is obsolete and never added.

Norwegian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Portugal

The bundled `PortugalGeographyProvider` supplies the 18
districts plus the Azores and Madeira as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PT')` after countries are seeded.

Portuguese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 7-digit `NNNN-NNN` postcode, and country.
## Romania

The bundled `RomaniaGeographyProvider` supplies the 41 departments
plus Bucharest as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RO')` after countries are seeded.

Romanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, and country.
## Saint Kitts and Nevis

The bundled `SaintKittsAndNevisGeographyProvider` supplies the 2
islands as `State` rows with the 14 parishes as level-2 areas (9
Saint Kitts, 5 Nevis) in a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('KN')` after
countries are seeded.

Kittitian and Nevisian addresses are formatted per the UPU layout:
street lines, the locality, the island, the `KN`-prefixed postcode
on its own line, and country.
## San Marino

The bundled `SanMarinoGeographyProvider` supplies the 9
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SM')` after countries are seeded.

Sammarinese addresses are formatted per the UPU layout (Italian CAP
system): street lines, `{postcode} {locality}` with a `47890–47899`
postcode, and country.
## Serbia

The bundled `SerbiaGeographyProvider` supplies the 29 districts,
2 provinces, and Belgrade as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RS')` after countries are seeded.

Serbian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit delivery-office number, and
country. The street-level 6-digit PAK has no field and is not printed.
## Slovakia

The bundled `SlovakiaGeographyProvider` supplies the 8 regions as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SK')` after
countries are seeded.

Slovak addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country.
## Slovenia

The bundled `SloveniaGeographyProvider` supplies the 200
municipalities plus 12 urban municipalities as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SI')` after countries are seeded.

Slovenian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. An
`SI-` prefix passes through when supplied.
## Sweden

The bundled `SwedenGeographyProvider` supplies the 21 counties as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SE')` after
countries are seeded.

Swedish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country. An `SE-` prefix passes through when supplied.
## Switzerland

The bundled `SwitzerlandGeographyProvider` supplies the 26
cantons as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CH')` after countries are seeded.

Swiss addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode (office numbers and
canton abbreviations pass through), and country.
## Aland

The bundled `AlandGeographyProvider` supplies the 16
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AX')` after countries are seeded.

Åland addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit `22xxx` postcode, and country.
International mail prefixes `AX-`; the formatter prints the postcode
exactly as supplied.
## Faroe Islands

The bundled `FaroeIslandsGeographyProvider` supplies the 6
regions as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('FO')` after countries are seeded.

Faroese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `FO-NNN` postcode, and country. Old
Danish `38xx` codes are obsolete.
## Guernsey

The bundled `GuernseyGeographyProvider` supplies the 12 parishes
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GG')` after
countries are seeded.

Guernsey follows the UK postcode system (`GY` prefix, not `GG`).
Addresses print street lines, the post town, the postcode on its own
line, and country.
## Jersey

The bundled `JerseyGeographyProvider` supplies the 12 parishes as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('JE')` after
countries are seeded.

Jersey follows the UK postcode system (`JE` prefix). Addresses print
street lines, the post town, the postcode on its own line, and country.
## Isle of Man

The bundled `IsleOfManGeographyProvider` supplies the 6 sheadings
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IM')` after
countries are seeded.

The Isle of Man follows the UK postcode system (`IM` prefix).
Addresses print street lines, the post town, the postcode on its own
line, and country. The formatter prints `Isle of Man` rather than
the database's inverted `Man (Isle of)` spelling.

## Numeric state codes

Bahrain, Italy, South Korea, Saudi Arabia, Türkiye, Morocco, France,
Japan, Poland, Kenya, Tanzania, Algeria, Thailand, Vietnam, Ukraine,
Myanmar, Bhutan, Cyprus, Iran, Kazakhstan, Sri Lanka, Mongolia,
Maldives, North Korea, Burkina Faso, Congo, Gabon, Mali,
Mauritania, Niger, Rwanda, Sao Tome and Principe, Seychelles,
Tunisia, Zambia, Albania, Andorra, Austria, Bulgaria, Croatia,
Czech Republic, Denmark, Estonia, Finland, Greece, Iceland, Latvia,
Liechtenstein, Lithuania, Malta, Montenegro, North Macedonia,
Norway, Portugal, San Marino, Serbia, Slovenia, Aland, Guernsey,
Jersey, and Isle of Man use numeric ISO subdivision codes
at the state-mapping level. PHP casts numeric-string array keys to
int, so
`stateAreaMappings()` returns int keys for those countries and the
contract documents `array<int|string, ...>`. `linkStateAreas()`
stringifies keys before querying, so seeding behaves identically on
every database driver.

## Seed Command

```bash
php artisan address:seed-countries
php artisan commerce:seed-currencies
php artisan commerce:seed-languages
php artisan commerce:seed-timezones
php artisan address:seed-country-references
```

This is idempotent — running it multiple times is safe.
