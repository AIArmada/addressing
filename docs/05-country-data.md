---
title: Country Data
---

# Country Data

For per-provider depth and dataset status at a glance, see the
[Provider Coverage Registry](./14-provider-coverage.md).

## Bundled Dataset

The package always bundles ISO 3166-1 country/territory data.

File location: `resources/data/countries.json`

The bundled `MalaysiaGeographyProvider` supplies Malaysia's State/Federal Territory catalog, two explicit address hierarchies, the AddressArea hierarchy, and State↔AddressArea mappings. The primary administrative/land hierarchy is `region → district / division / jajahan → mukim / subdistrict / bandar / pekan`; the secondary postal/address hierarchy is `region → locality / precinct / kampung`. It is selected with `SeedCountryGeographiesAction::execute('MY')` after countries are seeded.

The dataset contains **249 records** — these are ISO 3166-1 address entities, not 249 sovereign countries. Records include:

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

`states.json` and `cities.json.gz` are bundled from the same nnjeim/world source. The city file ships gzipped; the seed readers prefer the `.gz` sidecar automatically. Seed the global files first; country providers then complement those rows using stable country-scoped identities. The Malaysia provider updates matching states in place and adds Malaysia-specific rows not present in the global file, such as Putrajaya. It does not seed shared commerce-support reference data. The bundled area source contains no canonical city mapping data.

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

Scope expansion: Tongkang Pechah and Parit Yaani were added as Batu Pahat
postal `locality` rows (external town lists name both, and addresses place
them under 83010). They share 83010 with the town core, so they link it as
secondary with Bandar Penggaram staying primary — the same pattern as Ulu
Choh sharing 81550 with Gelang Patah. (Renggam town is the opposite case:
86300 is its own code, so the town is primary there.)

Johor sweep (shared-postcode secondaries, primaries untouched): Skudai
(JB, 81300), Saleng (Kulai, 81400 shared with Senai), Kelapa Sawit (Kulai,
81000/81030 shared with Bandar Kulai), Chamek (Kluang, 86600 shared with
Paloh). Skipped for weak evidence: Sedili and Teluk Sengat (no distinct
town postcode), Seelong/Sengkang/Ayer Bemban (no postcode evidence),
Kangkar Pulai (ambiguous district), Taman Universiti and Mengkibol
(sub-localities of Skudai/Kluang).

Johor sweep, batch 2: Tanjung Agas (Tangkak, 84000 shared cross-district
with Muar town core). Muar, Segamat, Pontian, and Mersing needed no
additions — every listed town already has a row. Skipped: Bukit Naning and
Bukit Siput (suburbs), Kampung Tengah (85000 suburb), Jagoh, Sungai Karas,
Kayu Ara Pasong, Sanglang, Teluk Sengat, Sagil (village-level, no town
postcode evidence), Gemas Baharu (weak evidence), Pontian Besar (covered by
Mukim Pontian), Pekan Air Panas (likely Labis alias), Air Papan (village),
Segamat Baru (township), Permas (unreliable listing; Permas Jaya is JB).

Melaka sweep: Lubok China (Alor Gajah) added as a postal `locality` —
own post office and postcode 78100, so it takes primary with the district
placeholder demoted to secondary. All other Melaka towns already have rows.

Negeri Sembilan sweep: Telok Kemang (Port Dickson, federal constituency,
71050 shared with Si Rusa) added as a secondary-link `locality`. Gemas
town stays covered by Mukim Gemas. Seremban suburbs without rows (Sikamat,
Mambau, Paroi, Lobak, Rahang) deliberately skipped as sub-localities of
the town core.

Kedah sweep: Tikam Batu (Kuala Muda, federal-gazette post office, 08700
shared with Jeniang) added as a secondary-link `locality`. Guar Chempedak
already covered as Bandar Guar Cempedak (gazette spelling). Skipped:
Simpang Kuala (Alor Setar suburb), Tanjung Dawai (fishing village, no
town postcode evidence), Sungai Lalang / Sintok / Napoh (no verified
postcode evidence yet), Naka (village-level).

Perlis sweep: Kangar (01000), Padang Besar (02100), Kaki Bukit (02200),
and Simpang Empat (02700) added as state-parented `locality` rows, each
taking primary on its own code with the covering mukim demoted to
secondary. Arau and Kuala Perlis stay covered by their mukim rows.

Penang sweep: Teluk Bahang (Barat Daya, DUN, 11050 shared cross-district
with Bandar George Town), Batu Kawan (SPS, 14100 shared with Simpang
Ampat), and Bertam (SPU, DUN, 13200 shared with Kepala Batas) added as
secondary-link `locality` rows. Skipped: Bukit Minyak, Juru, Seberang
Jaya, Mak Mandin, Sungai Dua, Tanjung Bungah, Paya Terubong, and Sungai
Bakap (suburbs/sub-localities).

Kelantan sweep: Kok Lanas (Kota Bharu, 16450 shared with Ketereh) added
as a secondary-link `locality`. Skipped for lack of verified postcode
evidence: Pengkalan Kubor, Gual Ipoh, Bukit Bunga (in Jeli district, not
Tanah Merah).

Terengganu sweep: no additions — Chukai and Jerteh are already covered
as Bandar Cukai and Jertih (UPI spellings) with primaries on 24000 and
22000. All other listed towns have rows. Skipped: Seberang Takir (no
verified postcode evidence), Gong Badak (KT suburb), Penarik (fishing
village).

Pahang sweep: Bukit Tinggi (Bentong, 28750 shared with Bentong) and
Mengkarak (Bera, 28200 shared with Bandar Bera) added as secondary-link
`locality` rows. Genting Highlands already covered as Bandar Genting with
the 69000 primary. Skipped: Kampung Raja and Tanjung Gemok (no verified
postcode evidence); Janda Baik, Tekek, Tringkap, Kuala Semantan, Teriang,
Kerayong, Nenasi, Merchong (village-level).

Perak sweep: Simpang Lima (Kerian, 34200 shared with Parit Buntar) added
as a secondary-link `locality`. Trolak already covered as Terolak (UPI
spelling) with the 35700 primary; Tanjung Piandang already covered as
Mukim Tanjong Piandang. Skipped: Ayer Kuning, Bukit Merah, Lubuk Merbau,
Salak (no verified postcode evidence); Ampang and Tanjung Rambutan
(Ipoh suburbs).

Selangor sweep: Seri Kembangan (43300) and Serdang (43400) added as
Petaling `locality` rows taking primaries from district placeholders;
Balakong (Hulu Langat, 43300 shared cross-district) added secondary.
Tanjung Sepat already covered as Tanjong Sepat (UPI spelling).
Skipped: Sijangkang and Sungai Air Tawar (no clean postcode evidence —
conflicting codes for the latter); Batang Berjuntai (Bestari Jaya
alias); Setia Alam (township); Klang Valley suburbs and townships as a
class (USJ, Sunway, Puchong Jaya, Kinrara, and the like).

Sabah sweep: no additions — towns live as level-4 `subdistrict` rows and
every checked town (Kundasang, Tamparuli, Kiulu, Donggongon, Kinarut,
Benoni, Kimanis, Bongawan, Menumbok, Melalap, Kemabong, Sindumin,
Apin-Apin, Bingkor, Tungku, Sukau, Bukit Garam, Matunggong, Tandek) has
one. Skipped: Lok Kawi, Sikuati, Kanibongan (no verified postcode
evidence).

Sarawak sweep: no additions — same level-4 `subdistrict` pattern covers
every checked town (Sematan, Engkilili, Debak, Spaoh, Roban, Bintangor,
Niah, Batu Niah, Bekenu, Oya, Balingian, Sundar, Trusan, Sadong Jaya).
Skipped: Bako (fishing village).

WP sweep: no additions — KL's 11 parliamentary `locality` rows plus 7
mukims, Putrajaya's precincts, and Labuan's 28 kampung `locality` rows
already model each territory. KL neighborhoods (Bangsar, Mont Kiara,
and the like) deliberately skipped as sub-localities.

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
hierarchy type so the bridge resolves them for any hierarchy. The
independence is structural, not a gap: CDC boundaries follow electoral
divisions (GRCs/SMCs), which cut across planning areas and regions — four of
the five CDCs overlap the Central Region alone — so no CDC nesting is
modeled and no `refinedBy` is declared.

Revisit record: the postal tree was verified link by link (all 81 sector →
district links, sector `74` unallocated, no sector `83`) and the planning
tree name by name (all 55 areas with region parents: 22 Central, 6 East, 8
North, 7 North-East, 12 West). Singapore intentionally has no postal
`locality` level: postcodes are building-level, so towns have no single
postcode, and HDB towns already coincide with planning areas. The pins live
in `SingaporeGeographyProviderTest`.

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
districts. Seeded villages carry the `village` role, matching the level's
assignment role.

Cascade labels use the national terms (`Kecamatan`, `Desa`,
`Kelurahan`, `Kota`) with special-autonomy overrides: `Gampong` in
Aceh (Law 11/2006, regencies and cities), `Nagari` in West Sumatra
(rural only — municipalities stay kelurahan), `Kapanewon` /
`Kemantren` + `Kalurahan` in Yogyakarta (Perda DIY), and `Distrik` /
`Kampung` in the six Papua provinces (Otsus Law 21/2001). Deep areas
link their province ancestor directly, like the Malaysia provider.

ISO 3166-2 defines seven Indonesian geographical units (island groups such
as `ID-JW` Jawa) alongside the 38 provinces. Those units are not provinces
and were removed from the bundled state data; `seed()` also deletes any
stragglers from databases seeded before that fix, so `Papua` always resolves
to the province. Nusantara/IKN is a separate capital authority, not a 39th
province.

Individual five-digit postcodes are intentionally not bundled; import
operational postcodes through `ImportPostalCodesAction`. There is no postal
hierarchy and no `refinedBy`: the single administrative chain
province → regency/city → district → village already scopes every level,
and villages are administrative rows rather than postal localities.

Revisit record: all 38 province codes verified against ISO 3166-2:ID, every
province's regency/city split reconciled (416 + 98), and all 91,599 rows
checked for dangling parents and Kemendagri code shape with zero violations.
Known lag: BPS counts 7,288 districts (2025) and 84,048 villages (2024)
against the bundled 7,285 and 83,762 — upstream `lokabisa-oss/region-id`
has no release newer than v1.0.1, so refresh when it does rather than
hand-patching rows.

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
country. Types are labelled `Daerah` and `Mukim`.

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
governorates as `State` rows with 135 postal areas as level-2 areas
(32 Capital, 29 Ahmadi, 24 Jahra, 20 Farwaniya, 17 Hawalli, 13 Mubarak
Al-Kabeer) in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KW')` after countries are seeded.
Uninhabited islands (Miskan, Umm an Namil, Bubiyan, Warbah) are excluded;
blocks and per-area postcodes stay out of scope, and governorate/area
name twins (Farwaniya, Ahmadi, Jahra, Hawalli, Mubarak Al-Kabeer) share
names by design; filter by type.

Kuwaiti addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country. The `governorate` type is labelled `Muhafaza`.

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
Types are labelled `Muhafaza` and `Liwa`.

## Oman

The bundled `OmanGeographyProvider` supplies the eleven ISO 3166-2
governorates as `State` rows and a two-level administrative hierarchy
(governorate → 63 wilayats). It is selected with
`SeedCountryGeographiesAction::execute('OM')` after countries are seeded.

Omani addresses are formatted per the UPU layout: street lines, a
3-digit postcode on its own line above the locality, and country.
The `governorate` type is labelled `Muhafaza`.

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
region `13`) and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SA')` after
countries are seeded.
The 139 governorates ship as level-2 areas under their regions.

Saudi addresses are formatted per the UPU home-delivery layout:
street lines, a 5-digit postcode on its own line above the locality,
and country. Short addresses (`RAGI2929` style) and the separate P.O.
Box layout are not generated.

## Ecuador

The bundled `EcuadorGeographyProvider` supplies the 24 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('EC')` after
countries are seeded.
The 222 cantons ship as level-2 areas under their provinces.

Ecuadorian addresses are formatted per the UPU layout: street
lines, `{postcode} - {locality}` with a 6-digit postcode, and
country. Types are labelled `Provincia` and `Cantón`.

The 1225-code overlay (010101–900004) comes from the GeoNames dump
at canton level covering all 222 cantons. The four zone-90 codes
for the former undelimited zones link their absorbing cantons
(El Piedrero to El Triunfo, Manga del Cura to El Carmen, Las
Golondrinas to Cotacachi, per referendum/decree records). No new
area rows.

## Egypt

The bundled `EgyptGeographyProvider` supplies the 27 ISO 3166-2
governorates as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('EG')` after countries are seeded.
The 365 districts ship as level-2 areas from the OCHA Common
Operational Dataset on Administrative Boundaries (CAPMAS
census geography, valid 21 April 2017), which carries a
p-code (`EG0401`-style) and an explicit governorate parent
per district. Rows mix urban qisms and rural marakiz (plus a
few police-administered units such as `Port Suez Police
Department`); same-named qism/markaz pairs (e.g. the two
`Luxor` rows) ship as separate parent-scoped rows. Names use
COD transliteration (`Suhag`, `Sharkia`, `Qina`).

Egyptian addresses are formatted per the UPU layout: street lines,
locality, governorate, a 7-digit postcode on its own line, and country.
Governorate and the generic district (mixed qism/markaz rows) need no
type labels; qism/markaz cannot split further because the COD table
carries no kind column.

## South Africa

The bundled `SouthAfricaGeographyProvider` supplies the nine ISO
3166-2 provinces as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ZA')` after countries are seeded.
The 44 district and 8 metropolitan municipalities ship as level-2 areas under their provinces.

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

## Panama

The bundled `PanamaGeographyProvider` supplies the 10 provinces plus the
3 province-level comarcas (Guna Yala, Emberá, Ngäbe-Buglé) as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PA')` after countries are seeded.
The 81 districts ship as level-2 areas under provinces and comarcas.

Panama has no postcode system: addresses are formatted per the UPU layout
with street lines, locality, and country. Rural PO-box style addresses
(`Zona 4, Apartado 0819-...)` keep the zone box in the street line.

## Paraguay

The bundled `ParaguayGeographyProvider` supplies the 17 departments plus
Asunción as `State` rows and a two-level administrative hierarchy
(department → 263 districts). It is selected with
`SeedCountryGeographiesAction::execute('PY')` after countries are seeded.
Asunción is typed `capital_district` sharing the L1 level and `department`
assignment role, so it sits in the state tier with department grouping
rather than a standalone label.

Paraguayan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

## Uruguay

The bundled `UruguayGeographyProvider` supplies the 19 departments
as `State` rows and a two-level administrative hierarchy
(department → 125 municipalities). It is selected with
`SeedCountryGeographiesAction::execute('UY')` after countries are seeded.

Uruguayan addresses are formatted per the UPU layout: street lines,
`{postcode} – {locality}` with a 5-digit postcode and en dash, the
department on its own line, and country.

## Venezuela

The bundled `VenezuelaGeographyProvider` supplies the 23 states
plus the Capital District and the Federal Dependencies (ISO code
`W`) as `State` rows and a two-level administrative hierarchy
(state → 335 municipalities). It is selected with
`SeedCountryGeographiesAction::execute('VE')` after countries are seeded.
The capital district shares the `state` assignment role; the
childless federal dependency keeps its own role. Libertador ships
under the capital district.

Venezuelan addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 4-digit postcode (extended
`3028-A` style codes pass through), the state on its own line,
and country.

## Pakistan

The bundled `PakistanGeographyProvider` supplies the seven ISO 3166-2
subdivisions as `State` rows — four provinces plus the Islamabad
Capital Territory, Gilgit-Baltistan, and Azad Jammu and Kashmir
(`Azad Kashmir` is aliased) — and a two-level administrative hierarchy
(province/territory → 178 districts). It is selected with
`SeedCountryGeographiesAction::execute('PK')` after countries are seeded.

Districts follow the mid-2026 reorganization state: Punjab counts 41
(`Taunsa` included; `Jampur` was announced in December 2022 but never
notified and stays excluded), Khyber Pakhtunkhwa counts 40
(Chitral split into Lower/Upper plus `Central Dir`, `Paharpur`, and
`Upper Swat`), and Balochistan counts 42. The May 2026 Balochistan
batch (`Barshore`, `Tump`, Upper Dera Bugti, `Taftan`, `Wadh`, Quetta
East/West) supersedes the January Quetta City/Saddar notification and
is included; whole `Quetta` is retired by the East/West split and
`Karezat` stays removed (abolished 29 November 2022). Tehsils are
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
two-level administrative hierarchy. The 221 ISO 3166-2 subdivisions
remain global `State` rows only; they are not imported as areas. It is
selected with `SeedCountryGeographiesAction::execute('GB')` after
countries are seeded.
The 48 English ceremonial counties, 32 Scottish council areas, 22 Welsh principal areas and 11 Northern Ireland districts ship as level-2 areas under their nations.

The 2,941-code overlay (outward codes) comes from postcodes.io
(ONSPD-derived) at ceremonial county/council level: 597 multi-area
codes carry authoritative secondaries (London boroughs roll to Greater
London, Scilly to Cornwall); Crown Dependencies and non-geographic or
invalid codes are excluded; no new area rows.

British addresses are formatted per the UPU layout: street lines, post
town, the uppercased postcode on its own line, and country. The county
line is omitted when a postcode is present, per the UPU rule. Nation,
county, council area, county borough, and district need no type labels;
historic counties are intentionally not aliased (they map ambiguously
onto the current areas).

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

The 1349-code overlay (1000–9461) comes from the GeoNames dump at
district level: all 64 districts covered, old-spelling admin2 names
mapped to the post-2018 spellings, GPO anchors verified. Office-level
codes link their district; no new area rows.

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

The 2,089-code overlay (10000–94152) joins the GeoNames 1,325-code
base with a Mapanet 3,370-row top-up that adds Casablanca and Rabat
coverage: 62 of 75 provinces/prefectures covered (13 small/new
codeless), 7 conflicts adjudicated; no new area rows.

Moroccan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country. Types are labelled `Région`, `Préfecture`, and
`Province`.

## China

The bundled `ChinaGeographyProvider` supplies 33 provincial-level
divisions as `State` rows (22 provinces, 5 autonomous regions, 4
municipalities, plus Hong Kong and Macao) and 333 prefecture-level
divisions (293 prefecture-level cities, 30 autonomous prefectures,
7 prefectures, 3 leagues) as `AddressArea` rows under a
province → prefecture hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CN')` after countries are seeded.

Taiwan carries its own country code (`TW`) with its own postal
system, so it is intentionally not a CN area. Counties and districts
(level 3) are intentionally not bundled. The two Suzhou and two Fuzhou
prefecture-level cities carry province-disambiguated names
("Suzhou, Anhui" vs "Suzhou, Jiangsu").

The 2,349-code overlay comes from the GeoNames 2,352-row dump at
prefecture level: same-name prefectures disambiguated by province,
Tibetan/Uyghur romanization aliases mapped, 12 admin1 misfiles
corrected, 79 municipality/direct-admin links at L1; zero
cross-prefecture codes; no new area rows.

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
names carry their suffixes per the UPU province list. Districts
(raions) are intentionally not bundled: the second tier spans
parallel administrative and municipal systems with no
consolidated per-subject source. The tier is parked pending
the GAR extract, not cancelled — see the
[Russia tier-2 research log](17-russia-tier2-research.md) for
the usage evidence, the rejected options, and the resume
checklist.

Russian addresses are formatted as street lines, locality, subject,
a 6-digit postcode, and country — country last, per the UPU IB
recommendation. Domestic Russian convention prints the postcode after
the country instead; the formatter deliberately deviates.

## Germany

The bundled `GermanyGeographyProvider` supplies the 16 Länder as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('DE')` after
countries are seeded.
The 401 districts ship as level-2 areas under their states, typed
`rural_district` (294) and `urban_district` (107) from the source
table's Form column and grouped under the shared `district` level
and role. Aachen, Hanover, and Saarbrücken ride with rural: they
hold a different statute (Kommunalverband besonderer Art) but are
Rural-form and district-level. Row keys keep the historical
`de:district:*` shape (twin cities take parent-scoped keys:
`de:district:bayern:munich` is the kreisfreie Stadt).

State names use German official forms (`Bayern`, `Sachsen`); the
seven common English exonyms (`Bavaria`, `Lower Saxony`, `North
Rhine-Westphalia`, `Rhineland-Palatinate`, `Saxony`, `Saxony-Anhalt`,
`Thuringia`) are kept as aliases.

German addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. No `D-`
prefix is ever added. Types are labelled with the German terms
(`state` → `Land`, `rural_district` → `Landkreis`, `urban_district` →
`Kreisfreie Stadt`); none ever prints on mail, and the Stadtstaaten
need no per-state override.

## France

The bundled `FranceGeographyProvider` supplies the 18 regions (13
metropolitan, 5 overseas) as `State` rows in a two-level
administrative hierarchy. The 101 departments and overseas
collectivities remain global `State` rows only; they are not imported
as areas. It is selected with
`SeedCountryGeographiesAction::execute('FR')` after countries are seeded.
The 101 departments plus the Lyon Metropolis ship as level-2 areas under their regions.

The 20,315-code overlay comes from the GeoNames 51,611-row dump at
department level: CEDEX suffixes strip to the base delivery code, 31
cross-department codes dual-linked, Lyon split via the metro commune
list (69001–69009 to the metropolis), Clipperton 98799 dropped
(uninhabited); no new area rows.

French addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. CEDEX
suffixes are not generated. Types are labelled `Région` and
`Département`; the 973 region row is the endonym `Guyane` (matching
the department row and the corrected states.json entry) with the
English `French Guiana` kept as an alias.

## Italy

The bundled `ItalyGeographyProvider` supplies the 20 regions as
`State` rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('IT')` after countries are seeded.
The 82 provinces, 15 metropolitan cities, 6 free consortiums, 4 decentralization entities and 2 autonomous provinces ship as level-2 areas under their regions.

Region names use Italian official forms (`Toscana`, `Sicilia`); the
eight common English exonyms (`Piedmont`, `Aosta Valley`, `Lombardy`,
`Trentino-South Tyrol`, `Tuscany`, `Apulia`, `Sicily`, `Sardinia`)
are kept as aliases.

The 4,735-code overlay joins the GeoNames 18,415-row dump on province
sigla: 105 of 109 L2 direct (Aosta Valley links at L1, no provinces),
Sardinia's 160 CAPs point-remapped to the 8 post-2025 provinces, 9
cross-province codes dual/triple-linked; no new area rows.

Italian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality} {province}` with a 5-digit postcode, and
country. The two-letter province abbreviation comes from the optional
`province_code` address component and is omitted when absent. The
`region` type is labelled `Regione`; second-level sigla stay in the
code column (search aliases ship for state-level abbreviations only).

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
rows are typed `city`/`town`/`village`/`ward` (792/743/189/23) from the
kanji suffix (市/町/村/区) and grouped under the shared `municipality`
level and role; row keys keep the historical `jp:municipality:*` shape.
Thirteen same-prefecture name twins exist (Tomari ×2 in
Hokkaido, Fuchu city/town in Hiroshima, Toshima ward/village in Tokyo,
and ten more) plus ~100 cross-prefecture twins (Date, Fuchu) — filter
by `code` and parent, never by name alone. Ordinance-designated-city
wards (e.g. Osaka's 24 ku) are sub-municipal and intentionally not
bundled. Level-2 names are validated mechanically against the MIC
table (code↔parent consistency, kind totals, twin coverage);
row-by-row external name verification of all 1,747 rows remains
future work.

The 120,682-code overlay comes from the Japan Post ken_all official
gazette at municipality level (stored in dashed NNN-NNNN form):
designated-city wards roll up to their city codes, firm codes and
abolished rows excluded, 1,741 of 1,747 municipalities covered (the 6
Northern Territories villages codeless); no new area rows.

Japanese addresses are formatted per the UPU western layout: street
lines, `{city}, {prefecture}`, and `{postcode} {country}` on the last
line — all three detailed UPU examples join code and country
(`231-0012 JAPAN`); the schematic's split lines are the outlier.
Prefecture, city, town, village, and ward need no type labels (the
accepted collective and kind terms).

## United States

The bundled `UnitedStatesGeographyProvider` supplies 56 states,
districts, and territories as `State` rows (50 states, the District
of Columbia, American Samoa, Guam, the Northern Mariana Islands,
Puerto Rico, and the U.S. Virgin Islands) with 3,143 counties and
county equivalents as level-2 areas in a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('US')` after countries are seeded.

Counties carry 5-digit FIPS GEOIDs and parent their state row, typed
by Census flavor (`county`, `parish`, `borough`, `census_area`,
`city` for the 38 Virginian plus Baltimore, St. Louis, and Carson
City independents, `municipality` for Anchorage and Skagway, and
`planning_region` for Connecticut's 9 post-2022 regions). Puerto
Rico's municipios stay owned by the Puerto Rico provider and the
District of Columbia has no county child (it is its own
county-equivalent); both are intentionally absent here. County rows
come from the 2025 Census Gazetteer (public domain).

The military postal regions (`AA`, `AE`, `AP`) and the Minor Outlying
Islands (`UM`) are postal constructs, not addressable geography, and
are intentionally not areas.

American addresses are formatted per USPS Publication 28: street
lines, `{locality} {ST} {ZIP}` with the state abbreviation resolved
from a full-name map (already-abbreviated values pass through
uppercased), and country. All 56 states, territories, and DC carry
their USPS abbreviation as a searchable alias (the formatter map is
the source; military AA/AE/AP excluded with the areas). County
flavors need no type labels (parish, borough, and census_area render
correctly as distinct types).

## Spain

The bundled `SpainGeographyProvider` supplies 69 `State` rows — the 17 autonomous
communities plus Ceuta and Melilla, and all 50 provinces — with one
administrative hierarchy: 19 communities/cities → 50 provinces. It is
selected with
`SeedCountryGeographiesAction::execute('ES')` after countries are seeded.

Community names use short English forms (`Extremadura`, `Asturias`,
`Murcia`, `Madrid`) while provinces keep official local spellings
(`A Coruña`, `Bizkaia`, `Gipuzkoa`, `Araba`, `Ourense`, `Illes
Balears`). Only the 19 communities link to level-1 areas; provinces are
additionally assignable through the `province` role with their community
selected first.

Spanish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the province on its
own line, and country. Community, city, and province need no type
labels (the community names are English exonyms by documented
convention, mirroring states.json).

## Poland

The bundled `PolandGeographyProvider` supplies the 16 voivodeships as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('PL')` after
countries are seeded.
The 314 land counties and 66 city counties ship as level-2 areas under their voivodeships.

Voivodeship names use standard English exonyms (`Mazovia`, `Lesser
Poland`) since the official Polish forms are adjectives, not
standalone proper nouns. Powiats and gminas are intentionally not
bundled.

Polish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `NN-NNN` postcode, and country.

## Netherlands

The bundled `NetherlandsGeographyProvider` supplies the 12 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NL')` after
countries are seeded.
The 342 municipalities ship as level-2 areas under their provinces.

Province names use Dutch official forms (`Noord-Brabant`,
`Noord-Holland`, `Zuid-Holland`).

Dutch addresses are formatted per the UPU layout: street lines,
`{postcode}  {locality}` with an uppercased `NNNN LL` postcode and
two spaces before the locality, and country. Types are labelled
`Provincie` and `Gemeente`.

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
line, and country. The `lga` type is labelled `LGA`.

## Ethiopia

The bundled `EthiopiaGeographyProvider` supplies 14 regions and city
administrations as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ET')` after countries are seeded.
The 118 zones and 9 Harari woredas ship as level-2 areas under their regions.

The Southern Nations, Nationalities, and Peoples' Region was dissolved
in August 2023 (split into Sidama, Southwest, South, and Central
Ethiopia). Seeding deletes any `SN` straggler rows, and the bundled
state data no longer ships the code. South Ethiopia (`SE`) and Central
Ethiopia (`CE`) have no ISO codes yet; those codes are provisional
and will be updated when ISO assigns them.

Ethiopian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
`region` type is labelled `Kilil`.

## Democratic Republic of the Congo

The bundled `DemocraticRepublicOfCongoGeographyProvider` supplies the
26 provinces as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CD')` after countries are seeded.
The 145 territories ship as level-2 areas under their provinces
(post-2015 découpage mapping; Kinshasa is terminal).

Congolese addresses are formatted per the UPU layout: street lines,
an optional commune line, `{postcode} {province}` with a 7-digit
postcode, and country. Types are labelled `Province` and
`Territoire`.

## Tanzania

The bundled `TanzaniaGeographyProvider` supplies the 31 regions
(including Songwe, split from Mbeya in 2016) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TZ')` after countries are seeded.
The 193 districts ship as level-2 areas under their regions,
reflecting post-2021 splits (Busokelo, Madaba, Bumbuli, Chalinze,
Mpimbwe, Itigi) verified against government council registers.
"Nanyumbu Urban" ships under its official town name Nanyamba Town.

Tanzanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line, and country. Wards are intentionally not bundled.

## Kenya

The bundled `KenyaGeographyProvider` supplies the 47 counties as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('KE')` after
countries are seeded.
The 290 constituencies ship as level-2 areas under their counties
(IEBC numbering 1–290 verified complete). Sub-counties are not
bundled: no single reliable county-by-county list exists, and they
coincide with constituencies outside the urban splits.

Kenyan addresses are formatted per the UPU postal layout: street or
P.O. Box lines, the 5-digit postcode on its own line, then the town,
and country. The county line is omitted when a postcode is present.

## Sudan

The bundled `SudanGeographyProvider` supplies the 18 states as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SD')` after countries are seeded.
The 188 districts ship as level-2 areas under their states (UN OCHA
table; "Aj Jazirah" typo and "Gedaref" spelling mapped; the Abyei
PCA row excluded as disputed while the West Kordofan-parented Abyei
district row ships).

Sudanese addresses are formatted per the UPU layout: street lines, a
5-digit postcode on its own line above the locality, and country.

## Suriname

The bundled `SurinameGeographyProvider` supplies the 10 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SR')` after
countries are seeded.
The 63 ressorten ship as level-2 areas under their districts.

Suriname has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line.

## Uganda

The bundled `UgandaGeographyProvider` supplies the four regions as
`State` rows in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UG')` after countries are seeded.
The 135 districts and 11 cities ship as level-2 areas under their
regions, anchored to the UBOS 2024 census (no new districts since
July 2020, superseding the earlier volatility exclusion). Only the
10 operational regional cities plus Kampala ship; the 5 approved-but-
unfunded cities (Kabale, Moroto, Wakiso, Nakasongola, Entebbe) are
excluded.

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
Distrito Federal as `State` rows with 5,571 municipalities as
level-2 areas in a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BR')` after
countries are seeded.

Municipalities carry 7-digit IBGE codes (2-digit UF prefix) and
parent their state row, sourced from the IBGE Localidades API.
Fernando de Noronha is typed `district` (a Pernambuco state
district, not a municipality); Brasília parents the Distrito
Federal row. The set includes Boa Esperança do Norte, Mato Grosso
(5101837, effective January 2025).

Brazilian addresses are formatted per the UPU layout: street lines,
`{locality} - {ST}` with the two-letter state abbreviation resolved
from a full-name map, the `NNNNN-NNN` postcode on its own line, and
country. All 27 states and the federal district carry their UF
abbreviation as a searchable alias. Types are labelled `Estado`,
`Distrito Federal`, `Município`, and `Distrito`.

## Mexico

The bundled `MexicoGeographyProvider` supplies the 32 federal
entities as `State` rows (all typed `state`, including Ciudad de
México, which has been state-equivalent since 2016) with 2,479
municipalities as level-2 areas in a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MX')` after countries are seeded.

Municipalities carry 5-digit INEGI CVEGEO codes (state prefix plus
municipio number) and parent their state row. The 16 Ciudad de
México alcaldías are typed `borough`, everything else `municipality`.
Recent adds included: Villa Juárez, Aguascalientes (01012, created
August 2026); Villa de Pozos, San Luis Potosí (24059); Eldorado
(25019) and Juan José Ríos (25020), Sinaloa. Municipio names and
codes were sourced from Wikidata P3801 claims (CC0), verified
against the Spanish Wikipedia state annexes (CC-BY-SA) and the INEGI
2024 national count of 2,478 (plus Villa Juárez).

The 32,448-code overlay (01000–99998) joins the GeoNames 144,655-row
dump on INEGI state+municipality code: zero misses, zero
cross-municipality codes, 2,457 of 2,479 municipalities covered (22
codeless are post-dump creations and splits); no new area rows.

Mexican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}, {abbrev}` with the state abbreviation from
the UPU list (`CDMX`, `EDOMEX`, `Q. ROO`, `TAMPS`), and country. All
32 states carry their UPU abbreviation as a searchable alias. Types
are labelled `Estado`, `Municipio`, and `Alcaldía` (the capital's 16
boroughs).

## Canada

The bundled `CanadaGeographyProvider` supplies the 10 provinces plus
the 3 territories as `State` rows with 5,028 census subdivisions as
level-2 areas in a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CA')` after
countries are seeded.

Subdivisions carry 7-digit SGC codes (province plus division plus
subdivision) and parent their province row, sourced from the 2024
StatCan boundary file DBF. Types collapse the 60 CSDTYPE codes to
three: `municipality` for municipal governments, `indigenous_reserve`
for Indian reserves, and `unorganized` for unorganized areas. Names
follow StatCan recognition, including reserve spellings; filter by
code and parent, never by name alone, since 152 names repeat across
provinces.

Canadian addresses are formatted per the UPU layout: street lines,
`{locality} {PR} {postcode}` with the two-letter province abbreviation
and uppercased `ANA NAN` postcode, and country. All 13 provinces and
territories carry their postal abbreviation as a searchable alias
(plus accented `Québec`); province, territory, municipality,
indigenous reserve, and unorganized need no type labels.

## Australia

The bundled `AustraliaGeographyProvider` supplies the 6 states plus
the 2 mainland territories as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AU')` after countries are seeded.

The 537 local government areas ship as level-2 areas (128 NSW,
79 VIC, 78 QLD, 137 WA, 68 SA, 29 TAS, 18 NT; the ACT has no
local government and stays childless). All eight LGA types
(city, shire, town, region, borough, municipality, rural
city, council) share the `lga` assignment role. Excluded:
Lord Howe Island and the Unincorporated Far West (NSW),
Christmas Island and Cocos Islands shires (external
territories, not WA LGAs), and the Gerard, APY, and
Maralinga Aboriginal councils (SA communities, not LGAs).

External territories (Norfolk Island, Christmas Island, Cocos
Islands) carry their own postcodes and are intentionally not areas.

Australian addresses are formatted per the UPU layout: street lines,
`{locality}  {ST}  {postcode}` with two spaces between each part, and
country. All 8 states and territories carry their postal abbreviation
as a searchable alias; the eight LGA types render correctly and need
no type labels.

## Argentina

The bundled `ArgentinaGeographyProvider` supplies the 23 provinces
plus the Autonomous City of Buenos Aires as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AR')` after countries are seeded.

The 379 departments, 135 Buenos Aires partidos and 15 CABA comunas ship as level-2 areas under their provinces.

Argentine addresses are formatted per the UPU layout: street lines,
`{CPA} {locality}` with the `XNNNNLLL` postcode left of the locality,
and country. Types are labelled `Provincia`, `Ciudad`, `Comuna`, and
`Departamento`; the capital row is the endonym `Ciudad Autónoma de
Buenos Aires` (matching the corrected states.json entry) with the
English name kept as an alias.

## Colombia

The bundled `ColombiaGeographyProvider` supplies the 32 departments
plus Bogotá D.C. as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CO')` after countries are seeded.

The 1101 municipalities, 20 Bogota localities and 19 non-municipalized areas ship as level-2 areas under their departments.

Colombian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 6-digit postcode, the department on
its own line, and country. Types are labelled `Departamento`,
`Distrito Capital`, `Municipio`, `Localidad`, and
`Área No Municipalizada`.

## Peru

The bundled `PeruGeographyProvider` supplies the 25 regions plus the
Lima metropolitan municipality as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PE')` after countries are seeded.

The 196 provinces ship as level-2 areas under their regions. The `Huánuco`
spelling is corrected at seed.

Peruvian addresses are formatted per the UPU layout: street lines, a
5-digit postcode on its own line, the department on its own line, and
country.

## Vietnam

The bundled `VietnamGeographyProvider` supplies the post-merger 34
provincial-level divisions (25 provinces, 9 municipalities:
Hà Nội, Hải Phòng, Huế, Đà Nẵng, Cần Thơ, Hồ Chí Minh,
Quảng Ninh, Bắc Ninh, Đồng Nai) as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('VN')` after
countries are seeded.

The June 2025 merger (63 → 34, districts eliminated) is reflected as
shipped; the bundle carries post-merger names (`Huế`) and
municipality typing for Hải Phòng, Hồ Chí Minh, and Huế. The 2026 city
upgrades retype Quảng Ninh, Bắc Ninh, and Đồng Nai as
municipalities (all effective by September 2026).

The 3,321 commune-level units ship as level-2 areas (2,599
communes, 709 wards, 13 special zones) from the GSO official
list service, parented by province code with post-2026 typing
(22 xã→phường upgrades included). Seven rows carry mechanical
normalizations only (NFC, collapsed whitespace, lowercase
`xã` prefix on 06325); Hòa/Hoà spelling variants ship
source-faithful. All three types share the `commune`
assignment role.

Vietnamese addresses are formatted per the UPU layout: street and
ward lines, `{province} {postcode}` with a 5-digit postcode, and
country.

## Vanuatu

The bundled `VanuatuGeographyProvider` supplies the 6 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('VU')` after
countries are seeded.
The 60 area councils and 3 municipalities ship as level-2 areas
under their geographic provinces with HASC codes (Lenakel added
manually as the 2008 third municipality; municipalities are
parented geographically though administratively independent).

Vanuatu has no postcode system. Addresses are formatted per the UPU
layout: street lines, locality, and country.

## Thailand

The bundled `ThailandGeographyProvider` supplies the 76 provinces
plus Bangkok and Pattaya as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TH')` after countries are seeded.
Pattaya (ISO TH-S, `Phatthaya`) is a special administrative city
inside Chon Buri, modeled as a metropolitan_administration row
like Bangkok.

The 878 amphoe and 50 Bangkok khet ship as level-2 areas with
4-digit DOPA geocodes (Mueang prefixes restored on capital
districts except Ayutthaya; Bueng Kan's 8 recoded 43xx→38xx).
Pattaya is terminal. Sub-districts (tambon) are not bundled.

Thai addresses are formatted per the UPU layout: street lines,
`{district}, {province}`, the 5-digit postcode on its own line, and
country. The `province` type is labelled `Changwat` (amphoe, khet,
and metropolitan administration render correctly); Bangkok carries
its official `Krung Thep Maha Nakhon` name alongside the row name.

## Philippines

The bundled `PhilippinesGeographyProvider` supplies the 82 provinces
plus the National Capital Region as areas in a three-level
administrative hierarchy, including the
2022 Maguindanao split (`Maguindanao del Norte` / `Maguindanao del
Sur`) and `Davao de Oro`. It is selected with
`SeedCountryGeographiesAction::execute('PH')` after countries are seeded.

The 1,656 municipalities and cities ship as level-2 areas (149
cities, 1,493 municipalities, 14 Manila sub-municipalities) and
the 42,011 barangays as level-3 areas, all from the PSA
Philippine Standard Geographic Code 2025-2Q release (30 June
2025, used with acknowledgement per its use constraints; PSA
direct download is bot-walled so the build pulls a mirror of
the official files). NCR ships as a pseudo-province area mapped
to the existing region state, following PSGC's own model;
highly urbanized and independent cities parent to their
geographic province, the 8 Bangsamoro Special Geographic Area
municipalities to Cotabato, and Manila's 14 districts sit as
L2 siblings of Manila City (which is therefore barangay-less).
The other 16 regions stay global `State` rows only (with
`Bangsamoro` and `Cordillera Administrative Region` name
corrections at seed): regions are churny (ARMM→BARMM in 2019,
Negros Island Region re-created in 2024 without an ISO code),
while provinces are the address-relevant unit.
`Samar` keeps `Western Samar` as an alias.

Filipino addresses are formatted per the UPU layout: street lines,
the municipality on its own line with `{postcode} {province}` below
it for provincial addresses (`{postcode} {municipality}, METRO MANILA`
for Metro Manila, `{postcode} {locality}` when no province is set),
and country.

## South Korea

The bundled `SouthKoreaGeographyProvider` supplies the 17
provincial-level divisions as `State` rows (9 provinces including the
special self-governing Gangwon State, Jeju, and Jeonbuk State — the
2023/2024 official renames, with `Gangwon` and `North Jeolla` aliased
— 6 metropolitan cities, Seoul, and Sejong) and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KR')` after countries are seeded.

The 77 cities, 82 counties and 69 autonomous districts ship as level-2 areas under their provinces and cities.

South Korean addresses are formatted per the UPU layout: street
lines, `{province or city} {postcode}` with a 5-digit postcode, and
country.

## Taiwan

The bundled `TaiwanGeographyProvider` supplies the 22 divisions (6
special municipalities, 3 cities, 13 counties) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TW')` after countries are seeded.

The 368 townships, county-administered cities, and districts ship
as level-2 areas with 8-digit household-registration codes and
Traditional Chinese native names (122 rural townships, 38 urban,
24 mountain indigenous townships, 14 county-administered cities,
164 districts, 6 mountain indigenous districts). All six types
share the `district` assignment role; 12 cross-division name
twins are parent-scoped. Villages (li) are not bundled.

Taiwanese addresses are formatted per Chunghwa Post (no UPU sheet is
published for Taiwan): street lines, `{locality} {postcode}` with the
6-digit 3+3 postcode, and country.

## Ukraine

The bundled `UkraineGeographyProvider` supplies the 24 oblasts plus
Kyiv, Sevastopol, and the Autonomous Republic of Crimea as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UA')` after countries are seeded.

The 136 post-2020 raions ship as level-2 areas under their oblasts. Oblast names use
the ISO adjectival forms (`Kyivska`, `Lvivska`).

Ukrainian addresses are formatted per the UPU layout: street lines,
locality, oblast, a 5-digit postcode on its own line, and country.

## Iraq

The bundled `IraqGeographyProvider` supplies the 19 governorates as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IQ')` after
countries are seeded.

`Iqlim Kurdistan` is ISO-listed as a region (`IQ-KR`) overlapping
Erbil, Dohuk, Sulaymaniyah, and Halabja — not a governorate. Since
governorates are the address-relevant unit, seeding deletes any `KR`
straggler rows and the bundled state data no longer ships the code.
Halabja (governorate in Kurdistan since 2014, federally since April
2025) has no ISO code yet; `HL` follows UK government usage pending
ISO assignment. The 119 districts ship as level-2 areas under their governorates.

Iraqi addresses are formatted per the UPU layout: street lines,
`{city}, {governorate}`, the 5-digit postcode on its own line, and
country. Types are labelled `Muhafaza` and `Qadaa`.

## Ghana

The bundled `GhanaGeographyProvider` supplies the 16 regions
(including the six created in 2019) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GH')` after countries are seeded.
The 261 metropolitan, municipal, and district assemblies ship as
level-2 areas under their regions (6 metropolitan + 113 municipal
+ 142 district).

Districts are intentionally not bundled.

Ghanaian addresses are formatted per the UPU layout: street or P.O.
Box lines, `{locality} {postcode}` (accepting both short and digital
`GA-183-8164` forms as given), the region on its own line, and
country.

## Angola

The bundled `AngolaGeographyProvider` supplies the 21 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AO')` after
countries are seeded.
The 326 post-reform municipalities ship as level-2 areas,
extracted from the 21 annexes of Law 14/24 itself (Diário da
República, 5 September 2024 — one map page per municipality,
titles parsed and counted to exactly 326). Names are
title-cased from the gazette's all-caps with Portuguese
particles kept lowercase; official spellings omit apostrophes
(`Mbanza Kongo`, `Nzeto`) per current government usage.

Law 14/24 (gazetted 5 Sept 2024) split Cuando Cubango into `Cuando`
and `Cubango`, carved `Icolo e Bengo` out of Luanda, and `Moxico
Leste` out of Moxico; the new provinces were formally instituted
with appointed governors in December 2024 and the 2024 census
tabulates all 21, so the bundled data tracks the 21 as operational.
The retired `Cuando Cubango` row is removed (a split has no single
successor to alias). ISO 3166-2:AO still lists only the former 18,
so the bundled codes `CUA`/`CUB`/`IEB`/`MLE` are provisional
pending ISO.

Angola has no postcode system, so the formatter stacks street lines,
city, and country with no postcode line. Types are labelled with the
Portuguese gazette terms (`province` → `Província`, `municipality` →
`Município`).

## Cameroon

The bundled `CameroonGeographyProvider` supplies the 10 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CM')` after
countries are seeded.

The 58 departments ship as level-2 areas under their regions.

Cameroon has no postcode system, so the formatter stacks street
lines, city, and country with no postcode line. Types are labelled
`Région` and `Département`.

## Madagascar

The bundled `MadagascarGeographyProvider` supplies the 6 provinces
as `State` rows and a three-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MG')` after
countries are seeded.
The 24 regions ship as level-2 areas under their provinces, and the
114 districts ship as level-3 areas under their regions
(INSTAT/Wikipedia list with French↔Malagasy name variants mapped).
Ambatosoa is the 24th region, created by Law 2023-012 (annex
7 June 2023; promulgated 11 August 2023) from the Maroantsetra and
Mananara Avaratra districts of northern Analanjirofo; the bundled
rows parent both districts under Ambatosoa.
The bundled 114 districts treat Antananarivo-Renivohitra as a single
district; the legal total is 119, and the census aggregation to 114
follows the INSTAT RGPH-3 Atlas, which counts the six Antananarivo
districts as one. Communes (1,695 per the Law 2023-012 annex) and
fokontany (18,251 per the MEF consolidated table under Décret
2015-592, unchanged by the 2023 law) are not bundled.

The regions have no ISO codes (ISO 3166-2:MG still lists the 6
former faritany); the 6 remain postally relevant since the
postcode's first digit routes by old province.

Malagasy addresses are formatted per the UPU layout: street lines,
`{postcode} {town}` with a 3-digit postcode, and country. Types are
labelled `Faritany`, `Faritra`, and `Distrika`.

## Afghanistan

The bundled `AfghanistanGeographyProvider` supplies the 34 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AF')` after
countries are seeded.

The 401 districts ship as level-2 areas from the OCHA Common
Operational Dataset on Administrative Boundaries (COD-AB v03,
valid 1 June 2025), which carries a UN p-code (`AF0101`-style)
and an explicit province parent per district. Afghan government
sources disagree on the district count over time (398/399/407 in
various CSO/IDLG/SIGAR vintages), so the COD — the operational
standard used by the UN and humanitarian community — is the
bundled source of truth; 33 provincial centres and Kabul city
ship as their own district rows. The `Ghor` and `Kunduz`
spellings are corrected at seed.

Afghan addresses are formatted per the UPU layout: street lines,
the locality on its own line, `{postcode} {province}` with a 6-digit
postcode (new province-encoded system from 1 October 2024), and
country. Province and district need no type labels (the bundled data
and COD source both use the English terms); provincial centres and
Kabul city stay typed `district` — capital status is an attribute,
not a distinct addressing tier.

## Mozambique

The bundled `MozambiqueGeographyProvider` supplies the 10 provinces
plus Maputo City as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MZ')` after countries are seeded.

The 129 districts ship as level-2 areas under their provinces, plus
the 7 municipal districts under Maputo City. `Maputo Province` and
`Maputo City` are disambiguated at seed. Maxixe is excluded (city,
not a district).

The 113-code overlay files Mapanet rows to districts via the
posto→district table (101 of 136 districts; city municipalities and
11 dropped codes excluded as admin-separate or anomalous);
Maputo-city codes district-mapped; no new area rows.

Mozambican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the province on its
own line, and country. Types are labelled `Província`, `Cidade`,
and `Distrito`.

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
own line (omitted when it duplicates the city), and country. Cities are labelled `Shahar`.

## Myanmar

The bundled `MyanmarGeographyProvider` supplies the 7 regions, 7
states, and Naypyidaw as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MM')` after countries are seeded.
The 80 districts ship as level-2 areas from the OCHA
Common Operational Dataset on Administrative Boundaries
(MIMU Place Codes, valid 15 February 2024), which carries a
p-code (`MMR016001`-style) and an explicit parent per
district. Operational reality wins over announcement
reality: the April 2022 MOI announcement (Notifications
319–333, 76 + 46 = 121) was never operationalized, and the
reference table churns between counts, so the 80-district
MIMU operational list is the bundled source of truth.
MIMU splits Bago into East/West and Shan into East/North/
South (18 admin-1 units); those split parents are rolled up
into the ISO `Bago` and `Shan` states (Shan 16, Bago 4).
Townships are not bundled.

Myanmar addresses are formatted per the UPU layout: street lines,
`{locality}, {postcode}` with a 7-digit postcode, the region or state
on its own line, and country. Region, state, union territory, and
district need no type labels (the MIMU English terms render
correctly).

## Cambodia

The bundled `CambodiaGeographyProvider` supplies the 24 provinces plus
Phnom Penh municipality as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KH')` after countries are seeded.
The 163 districts, 33 municipalities and 14 Phnom Penh sections ship as level-2 areas under their provinces.

Code `18` seeds the official `Preah Sihanouk` name with `Sihanoukville`
kept as an alternative area name. Communes (khum/sangkat) below the
districts are intentionally not bundled.

Cambodian addresses are formatted per the UPU layout: street lines,
the city above `{province} {postcode}` with a 6-digit postcode, and
country.

## Laos

The bundled `LaosGeographyProvider` supplies the 17 provinces plus the
Vientiane Prefecture as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LA')` after countries are seeded.
The 148 districts ship as level-2 areas under their provinces.

The Vientiane province (`VI`) and Vientiane Prefecture (`VT`) are
separate areas sharing a name.

Laotian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the province on its
own line when both are set, and country. Types are labelled
`Khoueng` and `Muang`.

## Timor-Leste

The bundled `TimorLesteGeographyProvider` supplies the 13 ISO 3166-2
municipalities (Oecusse is a special administrative region) plus
Atauro — split from Dili in 2022 with provisional code `AT`, since
ISO has not assigned one yet — as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TL')` after countries are seeded.

Timor-Leste joined ASEAN as the 11th member in October 2025.
The 67 administrative posts ship as level-2 areas under their municipalities.

Timorese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a `TL` + 5-digit postcode, and country.
Distinct city and municipality join as `{city} - {municipality}
{postcode}`; equal values print once.

## Armenia

The bundled `ArmeniaGeographyProvider` supplies the 10 regions plus
Yerevan as `State` rows and a two-level administrative hierarchy.
It is selected with
`SeedCountryGeographiesAction::execute('AM')` after countries are seeded.
The 70 municipalities and 12 Yerevan districts ship as level-2 areas under their regions and city.

Armenian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the region on its
own line when both are set, and country. Regions and municipalities
are labelled `Marz` and `Hamaynk`; Yerevan city and its districts keep
English headlines.

## Azerbaijan

The bundled `AzerbaijanGeographyProvider` supplies the 66 districts
(rayonlar), 11 cities (şəhərlər), and the Nakhchivan Autonomous
Republic as `State` rows and a two-level administrative hierarchy.
It is selected with
`SeedCountryGeographiesAction::execute('AZ')` after countries are seeded.
All 78 first-level rows seed as states under the state-kind `district`
level; the 11 cities keep the `municipality` area type and Nakhchivan
the `autonomous_republic` type. State-kind levels carry no assignment
role — only `local_municipality` is assignable.

The 685 local municipalities (bələdiyyə) ship as level-2 areas
from the State Statistical Committee classification (4,455
rows; municipality rows end in `007` plus 9 suffixed rows),
parented by the 3-digit district prefix. Baku's 12 intra-city
rayons parent to Baku city; where SSC codes a city and its
district together (Şəki, Lənkəran, Yevlax), the eponymous
municipality goes to the city and the rest to the district.
Liberated-territory districts and Aghdara are absent from SSC
and stay childless. Type and role are `local_municipality`
(the `municipality` area type is already taken by L1 cities).

The Lankaran, Shaki, and Yevlakh municipality/district pairs share
names by design, as do Nakhchivan city and the Nakhchivan Autonomous
Republic; filter by type.

Azerbaijani addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `AZ` + 4-digit postcode, the district
or region on its own line when both are set, and country.

## Bhutan

The bundled `BhutanGeographyProvider` supplies the 20 dzongkhags as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BT')` after
countries are seeded. The 205 gewogs ship as level-2 areas under their districts.

Bhutanese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the dzongkhag on its
own line when it differs, and country.

## Cyprus

The bundled `CyprusGeographyProvider` supplies the 6 districts with
bilingual names as `State` rows and a single-level administrative
hierarchy plus 752 postal localities in a postal hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CY')` after
countries are seeded. Localities follow the GeoNames postal dump
place names (quarters keep their `Town (Quarter)` labels); the
dump covers all six districts including Keryneia.

Cypriot addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
Inbound international mail prefixes `CY-`; the formatter prints the
postcode exactly as supplied.

## Georgia

The bundled `GeorgiaGeographyProvider` supplies the 9 regions plus
the Abkhazia and Adjara autonomous republics and Tbilisi as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GE')` after countries are seeded.
The 65 municipalities, 16 districts and 4 self-governing cities ship as level-2 areas under their regions, republics and Tbilisi.

Georgian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the region on its
own line when both are set, and country. The `region` type is
labelled `Mkhare`.

## Haiti

The bundled `HaitiGeographyProvider` supplies the 10 departments
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('HT')` after
countries are seeded.
The 42 arrondissements ship as level-2 areas under their
departments.

Haitian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `HT`-prefixed postcode, and
country. Types are labelled `Département` and `Arrondissement`.

## Honduras

The bundled `HondurasGeographyProvider` supplies the 18
departments as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('HN')` after countries are seeded.
The 298 municipalities ship as level-2 areas under their
departments.

Honduran addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the department,
and country. Types are labelled `Departamento` and `Municipio`.

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
(provinces) as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('IR')` after countries are seeded.

The 429 counties (shahrestan) ship as level-2 areas from the
UN OCHA Common Operational Dataset v01, parented by province
pcode with English names and Persian native names. Vintage
warning: the dataset is stamped May 2019 and misses every
county split since (current claims run 480–491 but no
complete, parent-mapped, officially backed list exists — the
reference page disagrees with itself and cites a 2007 atlas,
and the Statistical Centre of Iran is unreachable). Refresh
from SCI when accessible.

Iranian addresses are formatted per the UPU layout: street lines,
the locality, the province, the 10-digit postcode on its own line,
and country. Types are labelled `Ostan` and `Shahrestan`.

## Kazakhstan

The bundled `KazakhstanGeographyProvider` supplies the 17 regions
plus Almaty, Astana, and Shymkent as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KZ')` after countries are seeded.
The 170 districts ship as level-2 areas under their regions.

The Almaty region and Almaty city share a name by design; filter by
type.

Kazakh addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with either the legacy 6-digit or the new
`A99A9A9` postcode, the region on its own line when both are set, and
country. Types are labelled `Oblys`, `Qala`, and `Audan`.

## Kyrgyzstan

The bundled `KyrgyzstanGeographyProvider` supplies the 7 regions plus
Bishkek and Osh as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KG')` after countries are seeded.
The 44 districts ship as level-2 areas under their regions.

The Osh region and Osh city share a name by design; filter by type.

Kyrgyz addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the region on its
own line when both are set, and country. Types are labelled
`Oblus`, `Shaar`, and `Raion`.

## Lebanon

The bundled `LebanonGeographyProvider` supplies the 9 governorates
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LB')` after
countries are seeded. The 25 cazas ship as level-2 areas under
their governorates (Beirut has none — the governorate is the city).
Keserwan-Jbeil (split from Mount Lebanon in 2017) carries the
provisional code `KJ`: ISO 3166-2:LB still lists the 8 old
governorates.

The 683-code overlay files Mapanet rows to caza via per-point
reverse-geocode (Beirut links at L1, no caza; 16 zero rows
adjudicated, 10 ties + 3 row-majority multis); spaced/compact 8-digit
sector suffixes strip to the base 4-digit code at lookup; no new area
rows.

Lebanese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with the optional 4+4-digit LibanPost code,
the governorate on its own line when both are set, and country.
The `governorate` type is labelled `Muhafaza`.

## Maldives

The bundled `MaldivesGeographyProvider` supplies the 18 atolls plus
the Addu, Malé, Fuvahmulah, Kulhudhuffushi, and Thinadhoo cities as
`State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MV')` after countries are seeded.
The 192 islands ship as level-2 areas under their atolls.
Malé is typed city (ISO MV-MLE), not an atoll. Gnaviyani atoll is
retired: Fuvahmulah city covers it entirely (ISO still lists MV-29).
Fuvahmulah (`FVM`), Kulhudhuffushi (`KUH`), and Thinadhoo (`THD`)
codes are invented pending ISO assignment.

Maldivian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the atoll on its own
line when both are set, and country.

## Mongolia

The bundled `MongoliaGeographyProvider` supplies the 21 aimags
(provinces) plus Ulaanbaatar as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MN')` after countries are seeded.
The 330 sums and 9 Ulaanbaatar düüregs ship as level-2 areas under
their provinces.

Mongolian addresses are formatted per the UPU layout: street lines,
the district above `{province} {postcode}` with a 5-digit postcode
(`-NNNN` extensions pass through), and country. Types are labelled
`Aimag`, `Sum`, and `Düüreg`.

## Nepal

The bundled `NepalGeographyProvider` supplies the 7 federal provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NP')` after
countries are seeded. The 77 districts ship as level-2 areas under their provinces.

Nepali addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the province on its
own line when both are set, and country. Types are labelled
`Pradesh` and `Jilla`.

## North Korea

The bundled `NorthKoreaGeographyProvider` supplies the 9 provinces
plus Kaesong, Nampo, Pyongyang, and Rason as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KP')` after countries are seeded.
The 179 districts (si/gun) ship as level-2 areas from the OCHA
Common Operational Dataset on Administrative Boundaries
(valid 24 June 2019), which carries a p-code (`KP1102`-style)
and an explicit parent per district; Nampo nests cleanly with
6 children (no double placement). Two modelling notes:
COD-AB covers 11 of the 13 first-level units, so Kaesong and
Rason ship without subdivisions, and Pyongyang ships with 3
rows (city core plus Kangdong and Unjong counties) rather
than its full guyok set.

North Korea has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, and country; any supplied
code prints on its own line. Province, city, and the generic district
(mixed si/gun rows) need no type labels; si/gun cannot split further
because the COD table carries no kind column.

## Palestine

The bundled `PalestineGeographyProvider` supplies the 16 West Bank
and Gaza governorates as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PS')` after countries are seeded.
Localities (~500) are not bundled: no consolidated machine-readable
administrative locality list with stable identifiers and governorate
parents exists (OCHA COD stops at governorates), and Gaza geography is
in flux. Separately, the Ministry of Telecommunications and Digital
Economy [postal-zone table](https://site.mtde.gov.ps/home/PostalCodes),
accessed 2026-09-25, contains 755 locality/code rows and 603 distinct
P3 codes under all 16 governorates. Every code falls within its
governorate range in [Palestinian Instruction No. 1/2022](https://mjr.ogb.gov.ps/Decrees/ViewText/32052),
and no code appears under multiple governorates. The overlay links
only those published P3 codes to the bundled governorate areas; it
does not infer unlisted codes or add locality areas. The [UPU Palestine
profile](https://www.upu.int/UPU/media/upu/PostalEntitiesFiles/addressingUnit/pseFr.pdf)
(05/2025) confirms the P+7 format and P126/P144/P610 examples. The
Ministry table publishes no edition date or data-reuse terms.

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

The 2121-code overlay comes from the Department of Posts Post Code
Directory (2022): each office row carries its postal division, mapped
to the 25 districts (APR and AR/Akkaraipattu both Ampara), with the
Colombo 01–15 zones completed from the book's scanned zone table.
Office-level codes link their district (Romania precedent); no new
area rows.

## Syria

The bundled `SyriaGeographyProvider` supplies the 14 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SY')` after
countries are seeded.
The 66 districts ship as level-2 areas under their provinces.

Syria has no live postcode system (a 4-digit scheme was announced but
never confirmed). Addresses print street lines, the locality, and
country; any supplied code prints on its own line.

## Tajikistan

The bundled `TajikistanGeographyProvider` supplies Khatlon, Sughd,
Gorno-Badakhshan, Dushanbe, and the Districts under Republic
Administration as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TJ')` after countries are seeded.
The 51 districts and 18 regional-subordination cities ship as level-2 areas under their regions.

Tajik addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, the region on its
own line when both are set, and country. Types are labelled `Viloyat`, `Nohiya`, and `Shahr`.

## Turkmenistan

The bundled `TurkmenistanGeographyProvider` supplies the 5 regions
plus Ashgabat as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TM')` after countries are seeded.
The 58 districts ship as level-2 areas under their regions.

Turkmen addresses are formatted per the UPU layout: street lines,
the locality, the region when it differs, the 6-digit postcode on
its own line, and country.

## Yemen

The bundled `YemenGeographyProvider` supplies the 21 governorates
plus Amanat Al Asimah (the Sanaa municipality) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('YE')` after countries are seeded.
The 333 districts ship as level-2 areas under their governorates.

Yemen has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the governorate when it differs,
and country; any supplied code prints on its own line.

## Benin

The bundled `BeninGeographyProvider` supplies the 12 departments
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BJ')` after
countries are seeded.
The 77 communes ship as level-2 areas under their departments.

Benin has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, and country; any supplied code
prints on its own line. Types are labelled `Département` and `Commune`.

## Botswana

The bundled `BotswanaGeographyProvider` supplies the 10 districts,
Gaborone, Francistown, and 5 towns as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BW')` after countries are seeded.
`Orapa` town is bundled with provisional code `OR`; ISO 3166-2:BW
has not assigned it a code.
The 23 subdistricts ship as level-2 areas under 8 of the 10
districts; Chobe and North-East have no subdistrict tier and the
cities and towns are terminal. The Central "Serowe - Palapye" entry
ships as two subdistricts (Serowe, Palapye).

Botswana has no postcode system. Addresses are formatted per the UPU
layout: P.O. box or private bag lines, the town, and country; any
supplied code prints on its own line.
## Burkina Faso

The bundled `BurkinaFasoGeographyProvider` supplies the 17 regions
as `State` rows plus the 47 provinces nested at level 2 under their
regions, in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BF')` after countries are seeded.
The July 2025 reform renamed all 13 regions, renamed 5 provinces
(`Koosin`, `Gobnangou`, `Djelgodji`, `Sandbondtenga`, `Bassitenga`),
and added 4 regions plus `Karo-Peli` and `Dyamongou` provinces.
Renamed divisions keep their former codes (each successor region
keeps the old INSD code of the region it principally continues,
e.g. Bankui keeps `01` from Boucle du Mouhoun); region codes
`14`–`17` and province codes `KAR`/`DYA` are provisional pending
ISO 3166-2:BF. French Wikipedia's region table uses a different
unofficial numbering (e.g. Tapoa `16` / Sourou `17` swapped, and
different `01`–`13` assignments); no INSD or ISO publication for
the new numbering is known, so the succession scheme stands until
an official source rules.

Compositions follow the published reform details: Sourou region holds
`Koosin`, Nayala, and Sourou; Sirba holds Gnagna and Komondjari;
Tapoa holds `Gobnangou` and `Dyamongou` (Kantchari was a Tapoa
department); Goulmou holds Gourma and Kompienga; Liptako holds
Oudalan, Séno, and Yagha; Soum holds `Djelgodji` and `Karo-Peli`
(Arbinda was a Soum department); Bankui holds the remaining Balé,
Banwa, and Mouhoun; every other region keeps its pre-reform
composition under its new name. Provinces remain `State` rows for
compatibility and link their level-2 areas.

Spelling evidence (Sept 2026): `Koosin` follows the decree table and
Burkina Information Agency usage — the `Kossin` form appears only in
the Presidency communiqué prose quoted by news outlets. `Gobnangou`
follows the decree table; English Wikipedia still lists `Tapoa`
because its province page predates the reform. `Kuilsé` follows the
English Wikipedia primary article; the UK PCGN factfile prefers
`Koulsé` ("also seen Kuilsé").

Burkinabe addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line when both are set, and country.
## Burundi

The bundled `BurundiGeographyProvider` supplies the 5 provinces
(Buhumuza, Bujumbura, Burunga, Butanyerera, Gitega) as `State` rows
and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BI')` after countries are
seeded. The 42 communes ship as level-2 areas under their provinces
(7/11/7/8/9 per province), verified against the RGPH 2024 census
commune tables; Gitega's Karusi spelling follows the census. The
July 2025 reform replaced the former 18 provinces; ISO
3166-2:BI has not issued new codes, so the bundled codes 01-05 are
provisional local numbers pending ISO.

Burundi has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the commune, the province, and country; any
supplied code prints on its own line.

## Cape Verde

The bundled `CapeVerdeGeographyProvider` supplies the 22
municipalities plus the Barlavento and Sotavento island groups as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CV')` after
countries are seeded. The 32 parishes ship as level-2 areas under
their municipalities; the island groups are terminal.
Cabo Verde is the official name since 2013;
the bundled data keeps the `Cape Verde` spelling.

Cape Verdean addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit (or 7-digit
`NNNN-NNN`) postcode, and country. Types are labelled `Concelho`,
`Região Geográfica`, and `Freguesia`.

## Central African Republic

The bundled `CentralAfricanRepublicGeographyProvider` supplies the
20 prefectures — 18 administrative plus the Nana-Grébizi and
Sangha-Mbaéré economic prefectures — as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CF')` after countries are seeded.
The 80 subprefectures ship as level-2 areas under their prefectures.
The December 2020 law added `Lim-Pendé` (Paoua), `Mambéré` (Carnot),
and `Ouham-Fafa` (Batangafo), and retyped Bangui from commune to
prefecture; Sangha-Mbaéré is modelled as an economic prefecture.
ISO 3166-2:CF still lists only the former 17, so the bundled codes
`LP`/`ME`/`OF` are provisional pending ISO.

The country has no postcode system. Addresses are formatted per the
UPU layout: P.O. box lines, the locality, and country; any supplied
code prints on its own line. Types are labelled `Préfecture`,
`Préfecture Économique`, and `Sous-préfecture`.
## Chad

The bundled `ChadGeographyProvider` supplies the 23 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TD')` after
countries are seeded.
The 63 departments ship as level-2 areas under their provinces.

Chad has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the province when it differs,
and country; any supplied code prints on its own line. Types are
labelled `Province` and `Département`.

## Chile

The bundled `ChileGeographyProvider` supplies the 16 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CL')` after
countries are seeded.
The 56 provinces ship as level-2 areas under their regions.

Chilean addresses are formatted per the UPU layout: street lines,
`{postcode} {commune}` with a 7-digit postcode, the region, and
country. Types are labelled `Región` and `Provincia`.

## Comoros

The bundled `ComorosGeographyProvider` supplies the 3 islands as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('KM')` after
countries are seeded.
The 16 prefectures ship as level-2 areas under their islands.

Comoros has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the island when it differs,
and country; any supplied code prints on its own line. Types are
labelled `Île` and `Préfecture`.

## Congo

The bundled `CongoGeographyProvider` supplies the 15 departments
of the Republic of Congo as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CG')` after countries are seeded.
The 89 districts ship as level-2 areas under their departments.
Laws 25/26/27-2024 (8 Oct 2024) added `Congo-Oubangui` (Bokoma,
Loukoléla and Mossaka from Cuvette plus Liranga from Likouala),
`Nkéni-Alima` (five districts from Plateaux), and `Djoué-Léfini`
(five districts from Pool). ISO 3166-2:CG still lists only the
former 12, so the bundled codes `17`/`18`/`19` are provisional
local numbers pending ISO.

Congo has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line. Types are labelled `Département` and
`District`.

## Ivory Coast

The bundled `IvoryCoastGeographyProvider` supplies the 12 districts
plus the Abidjan and Yamoussoukro autonomous districts as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CI')` after countries are seeded.
The 31 regions ship as level-2 areas under their districts; Abidjan
and Yamoussoukro are terminal (undivided autonomous districts).
Departments are third-level and not bundled.

Ivory Coast has no postcode system; the 2-digit office code on box
lines is routing, not a postcode. Addresses print street lines, the
locality, and country; any supplied code prints on its own line.
Types are labelled `District Autonome`, `District`, and `Région`.

## Costa Rica

The bundled `CostaRicaGeographyProvider` supplies the 7 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CR')` after
countries are seeded.
The 84 cantons ship as level-2 areas under their provinces.

Costa Rican addresses are formatted per the UPU layout: street
lines, the locality, the 5-digit postcode on its own line above the
country, and country. Types are labelled `Provincia` and `Cantón`.

## Cuba

The bundled `CubaGeographyProvider` supplies the 15 provinces plus
the Isla de la Juventud special municipality as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CU')` after countries are seeded.
The 168 municipalities ship as level-2 areas under their provinces.

Cuban addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode (the `CP` prefix
passes through when supplied), and country. Types are labelled
`Provincia`, `Municipio Especial`, and `Municipio`.

## Djibouti

The bundled `DjiboutiGeographyProvider` supplies the 5 regions
plus Djibouti City as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DJ')` after countries are seeded.

The 20 sub-prefectures ship as level-2 areas (Ali Sabieh 3,
Arta 2, Dikhil 4, Djibouti City 1, Obock 4, Tadjourah 6),
each parented per its town/place article since the reference
lists are flat. `Adailou` follows the town-article spelling
(the flat lists print `Adaylou`); `Lac Assal` follows the
French local name.

Djiboutian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
Types are labelled `Région`, `Ville`, and `Sous-préfecture`.

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
Types are labelled `Región`, `Provincia`, `Distrito`, and `Municipio`.

158 municipalities ship as level-3 `municipality` areas under their
province (Baitoa, Matanzas, San Víctor verified via Senate creation
laws), with a `postal` hierarchy (region > municipality, refined by
province) and the `postal_locality` role. The 528-code overlay comes
from INPOSDOM's official postcode-finder dataset (1403 sector rows;
sector codes link their municipality): DN sectors 10100–10699 link the
L2 district directly, Moca city sectors use 53xxx overflow codes and
Urb. Henríquez uses 58081 (both outside the published ranges), 2 junk
rows (empty code, `Sin titulo`) are excluded, and 71100 (Pueblo Viejo
primary, Guayabal secondary) plus 81100 (Cabral primary, Jaquimeyes
secondary) are dual-linked largest-first.

## El Salvador

The bundled `ElSalvadorGeographyProvider` supplies the 14
departments as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SV')` after countries are seeded.
The 44 municipalities ship as level-2 areas under their departments
(post-May-2024 reform; the former 262 are now districts and are not
modelled).

Salvadoran addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and
country. Types are labelled `Departamento` and `Municipio`.

## Equatorial Guinea

The bundled `EquatorialGuineaGeographyProvider` supplies the 2
regions as `State` rows with the 8 provinces as level-2 areas
(3 Insular, 5 Río Muni) in a two-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('GQ')`
after countries are seeded.

Equatorial Guinea has no postcode system. Addresses are formatted
per the UPU layout: street lines, the locality, the province when it
differs, and country; any supplied code prints on its own line.
Types are labelled `Región` and `Provincia`.

## Eritrea

The bundled `EritreaGeographyProvider` supplies the 6 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ER')` after
countries are seeded.
The 58 subregions ship as level-2 areas under their regions.

Eritrea has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line. The `region` type is labelled `Zoba`.

## Gabon

The bundled `GabonGeographyProvider` supplies the 9 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GA')` after
countries are seeded.
The 49 departments ship as level-2 areas under their provinces.

Gabonese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 2-digit zone, and country. The full
UPU line adds the delivery-office code right (`NN LOCALITY NN`);
only the zone is represented since the office half has no field.
Types are labelled `Province` and `Département`.
## Gambia

The bundled `GambiaGeographyProvider` supplies the 5 regions plus
Banjul and Kanifing as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GM')` after countries are seeded.
The 42 districts ship as level-2 areas under their regions and
Banjul; Kanifing is terminal. The first tier is modelled as regions
(the divisions were renamed in 2007), and Kanifing ships as its own
first-level city rather than a Banjul district.

Gambia has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the region when it differs,
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
Types are labelled `Région`, `Gouvernorat`, and `Préfecture`.

## Guinea-Bissau

The bundled `GuineaBissauGeographyProvider` supplies the 8
regions plus the Bissau autonomous sector as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GW')` after countries are seeded.
The 38 sectors ship as level-2 areas under their regions. Leste,
Norte, and Sul are statistical groupings, not administrative
states, and are intentionally not shipped.

Bissau-Guinean addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
Types are labelled `Região`, `Sector Autónomo`, and `Sector`.

## Guyana

The bundled `GuyanaGeographyProvider` supplies the 10 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GY')` after
countries are seeded.
The 10 towns and 66 neighbourhood democratic councils ship as
level-2 areas under their regions.

Guyanese addresses are formatted per the UPU layout: street lines,
the locality, the postcode on its own line below the locality, and
country.

## Lesotho

The bundled `LesothoGeographyProvider` supplies the 10 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LS')` after
countries are seeded.
The 80 constituencys ship as level-2 areas under their districts.

Basotho addresses are formatted per the UPU layout: P.O. box lines,
`{locality} {postcode}` with a 3-digit postcode, and country.
## Liberia

The bundled `LiberiaGeographyProvider` supplies the 15 counties
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LR')` after
countries are seeded.
The 127 districts ship as level-2 areas under their countys.

The 31-code overlay (1000–7520) comes from the post-office list at
county level (codes do not resolve to districts): UPU-anchored
Monrovia 1000 and Buchanan 4000, Monrovia 1000-xx delivery units strip
to base at lookup, Gbarpolu codeless; no new area rows.

Liberian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
system is officially defined but flagged not-in-use by UPU, so codes
stay optional; Monrovia zone suffixes pass through as supplied.
## Libya

The bundled `LibyaGeographyProvider` supplies the 22 popularates
(sha'biyat) as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LY')` after countries are seeded.
The 100 baladiyas ship as level-2 areas from IOM DTM Libya's
Mobility Tracking baseline (Round 50, Oct–Dec 2023: `Baladiya
Main` sheet with a `LY021102`-style p-code and an explicit
mantika parent per baladiya). The 100-set is stable: Round 62
(Mar–Apr 2026) carries the identical 100 (mantika, baladiya)
pairs, both rounds' summaries assert `# Baladiyas: 100`, and
IOM reports have used 100 consistently since 2017. Rival
counts (99 gazetted 2013, ~101, 106–114 claimed) lose to this
operational consensus. DTM mantika spellings map onto the ISO
popularates (Ejdabia→Al Wahat, Tobruk→Al Butnan,
Ubari→Wadi al Hayaa, Zwara→Nuqat al Khams).

Libya has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line. Popularate and baladiya render correctly and
need no type labels.
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

The bundled `MaliGeographyProvider` supplies the 19 regions plus
the Bamako district as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ML')` after countries are seeded.
The 159 cercles ship as level-2 areas under their regions with
4-digit codes (region prefix + sequence, gap-free per region);
Bamako is terminal (no cercles, per the district statute).
Laws 2023-006/007 added Nioro (`11`), Kita (`12`), Dioila (`13`),
Nara (`14`), Bougouni (`15`), Koutiala (`16`), San (`17`), Douentza
(`18`), and Bandiagara (`19`). The bundled `9`/`10` numbering follows
the national law (Taoudénit `09`, Ménaka `10`) and therefore diverges
from ISO 3166-2:ML, which still assigns `ML-9` to Ménaka and `ML-10`
to Taoudénit.

Mali has no postcode system. Addresses are formatted per the UPU
layout: street lines, the quarter, the locality, and country; any
supplied code prints on its own line. Types are labelled
`District`, `Région`, and `Cercle`.

## Mauritania

The bundled `MauritaniaGeographyProvider` supplies the 15 regions
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MR')` after
countries are seeded.
The 63 departments ship as level-2 areas under their regions.

Mauritania has no postcode system. Addresses are formatted per the
UPU layout: P.O. box lines, the locality, and country; any supplied
code prints on its own line. Types are labelled `Wilaya` and
`Moughataa`.

## Mauritius

The bundled `MauritiusGeographyProvider` supplies the 9 districts
plus Agaléga, Rodrigues, and Saint Brandon as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MU')` after countries are seeded.
The city of Port Louis, the 4 towns, and 137 villages ship as
level-2 localities under their districts (16 villages spanning two
districts parent to the first-listed district); the 3 Agaléga
villages parent to the Agaléga dependency. Rodrigues and Saint
Brandon are terminal.

Mauritian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode (`R` + 4 digits on
Rodrigues), and country.
## Namibia

The bundled `NamibiaGeographyProvider` supplies the 14 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NA')` after
countries are seeded.
The 121 constituencies ship as level-2 areas under their regions
(Tondoro and Oshikunde verified against the Electoral Commission
register; the Wikipedia list table omits both rows).

The 149-code overlay comes from the NamPost official postcode table
(5-digit 2018+ system, all 14 regions): offices link at L1 region
(delivery points sit below constituency granularity), stale pre-2018
ZA-era codes excluded; no new area rows.

Namibian addresses are formatted per the UPU layout: street or box
lines, the locality, the 5-digit postcode on its own line, and
country.
## Niger

The bundled `NigerGeographyProvider` supplies the 7 regions plus
the Niamey urban community as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NE')` after countries are seeded.
The 66 departments and 5 Niamey communes ship as level-2 areas under their regions.

Nigerien addresses are formatted per the UPU layout: P.O. box lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
Types are labelled `Région`, `Communauté Urbaine`, `Département`,
and `Commune`.

## Nicaragua

The bundled `NicaraguaGeographyProvider` supplies the 15
departments plus the 2 Costa Caribe autonomous regions as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NI')` after countries are seeded.
The 153 municipalities ship as level-2 areas under their
departments and regions.

Nicaraguan addresses are formatted per the UPU layout: street
lines, the 5-digit postcode on its own line above the locality,
and country. Types are labelled `Departamento`,
`Región Autónoma`, and `Municipio`.

## Rwanda

The bundled `RwandaGeographyProvider` supplies the 4 provinces
plus Kigali as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RW')` after countries are seeded.
The 30 districts ship as level-2 areas under their provinces.

Rwanda has no postcode system. Addresses are formatted per the UPU
layout: P.O. box lines, the locality, the province when it differs,
and country; any supplied code prints on its own line.
## Sao Tome and Principe

The bundled `SaoTomeAndPrincipeGeographyProvider` supplies the 6
districts plus the Príncipe autonomous region as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ST')` after countries are seeded.
Districts are terminal; localidades below them are localities,
not administrative units.

The country has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, and country; any supplied
code prints on its own line.
## Senegal

The bundled `SenegalGeographyProvider` supplies the 14 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SN')` after
countries are seeded.
The 46 departments ship as level-2 areas under their regions.

Senegalese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode (often written `CP
NNNNN`), and country.
## Seychelles

The bundled `SeychellesGeographyProvider` supplies the 27 districts
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SC')` after
countries are seeded.
Districts are the only administrative tier, so they are terminal
(no level-2).

Seychelles has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, the island, and country; any
supplied code prints on its own line.
## Sierra Leone

The bundled `SierraLeoneGeographyProvider` supplies the 4 provinces
plus the Western Area as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SL')` after countries are seeded.
The 16 districts ship as level-2 areas under their provinces.

Sierra Leone has no postcode system. Addresses are formatted per the
UPU layout: street lines, the locality, the province when it differs,
and country; any supplied code prints on its own line.
## Somalia

The bundled `SomaliaGeographyProvider` supplies the 18 regions
(gobolka) as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SO')` after countries are seeded.
The 89 districts ship as level-2 areas under their regions.

Somalia has no operational postcode system; the UPU paper format
(`AA NNNNN` right of the locality) was never taken into use.
Addresses print P.O. box lines, the locality, and country; any
supplied code prints on its own line.
## South Sudan

The bundled `SouthSudanGeographyProvider` supplies the 10 states
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SS')` after
countries are seeded.
The 88 countys ship as level-2 areas under their states.

South Sudan has no postcode system. Addresses are formatted per the
UPU layout: street or box lines, the town, the state when it differs,
and country; any supplied code prints on its own line.
## Eswatini

The bundled `EswatiniGeographyProvider` supplies the 4 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SZ')` after
countries are seeded.
The 55 inkhundlas ship as level-2 areas under their regions.

Eswatini addresses are formatted per the UPU layout: P.O. box lines,
the locality, the region-letter + 3-digit postcode on its own line,
and country.
## Togo

The bundled `TogoGeographyProvider` supplies the 5 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TG')` after
countries are seeded.
The 39 prefectures ship as level-2 areas under their regions.

Togo has no postcode system. Addresses are formatted per the UPU
layout: P.O. box or street lines, the locality, the region when it
differs, and country; any supplied code prints on its own line.
## Tunisia

The bundled `TunisiaGeographyProvider` supplies the 24 governorates
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TN')` after
countries are seeded.
The 279 delegations ship as level-2 areas under their governorates
(INS 2024 figure, superseding the older 264).

Tunisian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Zambia

The bundled `ZambiaGeographyProvider` supplies the 10 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ZM')` after
countries are seeded.
The 116 districts ship as level-2 areas under their provinces.

Zambian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, and country. Codes
are routinely omitted in practice, so the formatter never requires
one.
## Zimbabwe

The bundled `ZimbabweGeographyProvider` supplies the 10 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ZW')` after
countries are seeded.
The 64 districts ship as level-2 areas under their provinces.

Zimbabwe has no postcode system. Addresses are formatted per the UPU
layout: street lines, the suburb, the city, and country; any supplied
code prints on its own line.

## Albania

The bundled `AlbaniaGeographyProvider` supplies the 12 counties
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AL')` after
countries are seeded.
The 61 municipalities ship as level-2 areas under their counties.

Albanian addresses are formatted per the UPU layout: street lines,
the 4-digit postcode on its own line above the locality, the county
when it differs, and country. Types are labelled `Qark` and `Bashki`.
## Andorra

The bundled `AndorraGeographyProvider` supplies the 7 parishes
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AD')` after
countries are seeded.

Andorran addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `AD` + 3-digit postcode, and country.
Parishes are labelled `Parròquia` (Catalan).
## Austria

The bundled `AustriaGeographyProvider` supplies the 9 states
(Bundesländer) as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AT')` after countries are seeded.
The 79 districts (Bezirke) and 14 statutory cities
(Statutarstädte) ship as level-2 areas under their states.

Austrian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Belarus

The bundled `BelarusGeographyProvider` supplies the 6 oblasts
plus Minsk as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BY')` after countries are seeded.
The 118 raions ship as level-2 areas under their oblasts.
The Minsk oblast and Minsk city share a name by design; filter by type.

Belarusian addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with a 6-digit postcode, the oblast on its
own line when both are set, and country. City and district keep
English headlines (the country is bilingual; no single local term).
## Belgium

The bundled `BelgiumGeographyProvider` supplies the 3 regions
as `State` rows with the 10 provinces as level-2 areas (5 Flanders,
5 Wallonia; Brussels-Capital childless) in a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BE')` after countries are seeded.

Belgian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. `B-`
and `BE-` prefixes are forbidden by bpost and are never added.
Flanders overrides tiers to Dutch (`Gewest`, `Provincie`) and Wallonia
to French (`Région`, `Province`); bilingual Brussels keeps English
headlines, so there is no country-wide label.
## Bosnia and Herzegovina

The bundled `BosniaAndHerzegovinaGeographyProvider` supplies the
Federation, Republika Srpska, and Brčko District as `State` rows
and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BA')` after countries are seeded.
The 143 municipalities ship as level-2 areas under their entities.

Bosnian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
Types are labelled `Entitet`, `Distrikt`, and `Općina`, with
Republika Srpska overriding the municipality label to `Opština`.

## Bulgaria

The bundled `BulgariaGeographyProvider` supplies the 28 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BG')` after
countries are seeded.
The 265 municipalities ship as level-2 areas under their provinces.

Bulgarian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
## Croatia

The bundled `CroatiaGeographyProvider` supplies the 20 counties
plus the City of Zagreb (code `21`, county-level city) as `State`
rows and a two-level administrative hierarchy. It is selected
with `SeedCountryGeographiesAction::execute('HR')` after countries
are seeded. The 428 municipalities and 128 towns ship as level-2
areas under their counties.

Croatian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
Inbound international mail prefixes `HR-`; the formatter prints the
postcode exactly as supplied. Types are labelled `Županija`,
`Općina`, and `Grad`.

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
Types are labelled `Kraj`, `Hlavní Město`, and `Okres`.

## Denmark

The bundled `DenmarkGeographyProvider` supplies the 5 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('DK')` after
countries are seeded.
The 98 municipalities ship as level-2 areas under their regions.

Danish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
optional `DK-` prefix passes through when supplied. Types are
labelled `Region` and `Kommune`.

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
Types are labelled `Maakond`, `Vald`, and `Linn`.

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
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('FI')` after
countries are seeded.
The 185 municipalities and 107 cities ship as level-2 areas under their regions; Aland is covered by the AX provider.

Finnish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. The
optional `FI-` prefix passes through when supplied. Types are
labelled `Maakunta`, `Kaupunki`, and `Kunta`.

## Greece

The bundled `GreeceGeographyProvider` supplies the 13
administrative regions plus Mount Athos (code `69`) as `State` rows
and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GR')` after countries are seeded.
The 332 Kallikratis municipalities including the 2019 island splits ship as level-2 areas under their regions.

Greek addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `NNN NN`,
and country. Types are labelled `Periféreia` and `Dímos`.

## Hungary

The bundled `HungaryGeographyProvider` supplies the 19 counties,
23 cities with county rights, and Budapest as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('HU')` after countries are seeded.
The 174 county districts and 23 Budapest districts ship as level-2 areas under their counties.
Budapest districts II, XIII, XV, and XVI ship under numbered names
matching the source list.

Hungarian addresses follow international one-line practice: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
(Domestic Hungarian order prints the postcode on its own line below
the street, but the locality-before-street domestic layout does not
fit the package's lines-first convention.) Types are labelled
`Vármegye`, `Megyei Jogú Város`, `Főváros`, and `Járás`.

## Iceland

The bundled `IcelandGeographyProvider` supplies the 8 regions
as `State` rows with a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IS')` after
countries are seeded. The 61 municipalities ship as level-2 areas
under their regions; 3 pre-2024 municipalities merged away and 3
rows were renamed to official names.

Icelandic addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 3-digit postcode, and country.
Types are labelled `Landsvæði` and `Sveitarfélag`.

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
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('XK')` after
countries are seeded. Kosovo has no ISO 3166-2 subdivision entry,
so district codes are an internal scheme. The 38 municipalities
ship as level-2 areas under their districts.

Kosovar addresses are formatted per the postal convention: street
lines, `{postcode} {locality}` with a 5-digit postcode, and country.
Types are labelled `Rajoni` and `Komuna`.

## Latvia

The bundled `LatviaGeographyProvider` supplies the 35
municipalities plus 7 state cities as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LV')` after countries are seeded.
The Jelgava, Rēzekne, and Ventspils municipality/city pairs share
names by design; filter by type.
Varakļāni Municipality merged into Madona on 1 July 2025; its
state row is deleted on seed and its town and parishes ship
under Madona.

The 511 parishes, 71 towns, and 3 cities ship as level-2 areas
under their municipality (state cities are childless). All
three types share the `parish` assignment role; Sala and
Pilskalne parishes are parent-scoped.

Latvian addresses are formatted per the UPU layout: street lines,
`{locality}, {postcode}` with an `LV-NNNN` postcode, and country.
Types are labelled `Novads`, `Valstspilsēta`, `Pagasts`, and
`Pilsēta`.

## Liechtenstein

The bundled `LiechtensteinGeographyProvider` supplies the 11
communes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LI')` after countries are seeded.
Postal services follow Swiss rules.

Liechtenstein addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
The tier is labelled `Gemeinde`.

## Lithuania

The bundled `LithuaniaGeographyProvider` supplies the 10 counties
as `State` rows with a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LT')` after
countries are seeded. The 43 district municipalities, 10 plain
municipalities and 7 city municipalities ship as level-2 areas
under their counties; Marijampolė was retyped from district to
plain municipality with a new source id.
The Alytus, Kaunas, Šiauliai, and Vilnius city/district pairs differ
by type (`city_municipality` vs `district_municipality`) and name
(`Vilniaus miestas` vs `Vilnius`); the district slugs keep their code
suffix (e.g. `vilnius-58`) for stability. Klaipėda, Palanga, and
Panevėžys cities use the same `miestas` convention.

Lithuanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
International mail prefixes `LT-`; the formatter prints the postcode
exactly as supplied. Types are labelled `Apskritis`,
`Rajono Savivaldybė`, `Miesto Savivaldybė`, and `Savivaldybė`.

## Luxembourg

The bundled `LuxembourgGeographyProvider` supplies the 12 cantons
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LU')` after
countries are seeded. Canton codes follow current ISO 3166-2:LU
The 100 communes ship as level-2 areas under their cantons.
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
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MD')` after countries are seeded.

The 915 communes and 66 cities/towns ship as level-2 areas
under their district, municipality, or autonomous unit
(Transnistria included de jure; component villages are L3 and
not bundled). `city` spans both levels (Uzbekistan pattern):
municipalities keep the `city` role, district cities share the
`commune` assignment role. Six same-district city/commune name
pairs carry type parentheticals; 67 cross-district twins are
parent-scoped.

Moldovan addresses are formatted per the UPU layout: street lines,
`{postcode}, {locality}` with an `MD-NNNN` postcode, and country.
The prefix passes through as supplied. Types are labelled `Raion`, `Comună`, and `Oraș`.
## Monaco

The bundled `MonacoGeographyProvider` supplies the 17 quarters
as `State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('MC')` after
countries are seeded.

These are the ISO 3166-2:MC traditional quarters, which ISO still
defines unchanged (verified Sept 2026) — not the 2013 sovereign
ordinance's town-planning layer of 7 wards plus the Monaco-Ville
and Ravin de Sainte-Dévote reserved sectors. Under that ordinance
La Colle merged into Jardin Exotique, but the `La Colle` ISO row is
retained since the wards carry no ISO codes. Revisit if ISO updates
the MC entry. The tier is labelled `Quartier`.

Monegasque addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit `98xxx` postcode, and country.
## Montenegro

The bundled `MontenegroGeographyProvider` supplies the 25
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ME')` after countries are seeded.

Montenegrin addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 5-digit postcode, and country.
The tier is labelled `Opština`.

## North Macedonia

The bundled `NorthMacedoniaGeographyProvider` supplies the 80
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MK')` after countries are seeded.

Macedonian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
tier is labelled `Opština`.

## Norway

The bundled `NorwayGeographyProvider` supplies the 15 counties
plus Svalbard and Jan Mayen as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NO')` after countries are seeded.
The 357 municipalities ship as level-2 areas under their counties.
The old 4-digit `N-` prefix is obsolete and never added.

Norwegian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.
Types are labelled `Fylke` and `Kommune`.

## Papua New Guinea

The bundled `PapuaNewGuineaGeographyProvider` supplies the 20
provinces plus Bougainville and Port Moresby as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PG')` after countries are seeded.
The 96 districts ship as level-2 areas under their provinces
(post-2022 electorate count, including the 3 National Capital
District seats under Port Moresby).

The 62-code overlay files Mapanet cells to districts via seat LLGs
(60 of 96 districts, 36 rural codeless): NCD codes suburb-mapped to
the 3 seats, 4 multi-district codes with seat primaries; no new area
rows.

Papua New Guinean addresses are formatted per the UPU layout: street
lines, `{locality} {postcode}` with a 3-digit postcode, and country.
## Portugal

The bundled `PortugalGeographyProvider` supplies the 18
districts plus the Azores and Madeira as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PT')` after countries are seeded.
The 18 mainland districts (`distrito`) plus the Azores and Madeira
(`autonomous_region`, statutorily autonomous) ship as L1 `State` rows
sharing the `district` assignment role, with the 308 municipalities
(`município`) as level-2 areas under them. The statutory `Concelho`
synonym appears as a common alias for one municipality.

Portuguese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 7-digit `NNNN-NNN` postcode, and country.
## Romania

The bundled `RomaniaGeographyProvider` supplies the 41 departments
plus Bucharest as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RO')` after countries are seeded.

The 2,861 communes, 217 towns, 102 county municipalities, and 6
Bucharest sectors ship as level-2 areas (per-county lists, each
count-asserted against its prose; totals match the official
103/217/2,861 with Bucharest as the 103rd municipality).
Maramureș's Breb bullet is a village inside Ocna Șugatag and is
dropped; Constanța's Băneasa ships as a town though listed
under communes; legacy ş/ţ spellings are normalized to ș/ț.
`municipality` spans both levels (Uzbekistan pattern):
Bucharest keeps the `municipality` role, county municipalities
share the `commune` assignment role. 355 cross-county twins
are parent-scoped.

Romanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 6-digit postcode, and country.
## Saint Kitts and Nevis

The bundled `SaintKittsAndNevisGeographyProvider` supplies the 2
islands as `State` rows with the 14 parishes as level-2 areas (9
Saint Kitts, 5 Nevis) and 92 villages as level-3 areas in a
three-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KN')` after countries are
seeded. Village→parish membership follows the parish articles;
Lodge (claimed by both neighbours) sits in Christ Church Nichola
Town, New Road in Saint Peter Basseterre, and Keys in Saint Mary
Cayon. Postcodes link at parish level because delivery districts
run below village granularity.

Kittitian and Nevisian addresses are formatted per the UPU layout:
street lines, the locality, the island, the `KN`-prefixed postcode
on its own line, and country.
## San Marino

The bundled `SanMarinoGeographyProvider` supplies the 9
municipalities (officially castelli) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SM')` after countries are seeded.

Sammarinese addresses are formatted per the UPU layout (Italian CAP
system): street lines, `{postcode} {locality}` with a `47890–47899`
postcode, and country.
## Serbia

The bundled `SerbiaGeographyProvider` supplies the 29 districts,
2 provinces, and Belgrade as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RS')` after countries are seeded.
The 117 municipalities, 23 cities and 17 Belgrade city-municipalities ship as level-2 areas under their districts.
`city` spans both levels (Romania pattern): Belgrade keeps the
`city` role while county cities share the `municipality`
assignment role.

Serbian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit delivery-office number, and
country. The street-level 6-digit PAK has no field and is not printed.
## Slovakia

The bundled `SlovakiaGeographyProvider` supplies the 8 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SK')` after
countries are seeded.
The 79 districts ship as level-2 areas under their regions.

Slovak addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country. Types are labelled `Kraj` and `Okres`.
## Slovenia

The bundled `SloveniaGeographyProvider` supplies the 200
municipalities plus 12 urban municipalities as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SI')` after countries are seeded.
Urban municipalities share the `municipality` assignment role since
they are municipalities with city status.

Slovenian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. An
`SI-` prefix passes through when supplied.
## Solomon Islands

The bundled `SolomonIslandsGeographyProvider` supplies the 9
provinces plus Honiara as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SB')` after countries are seeded.
The 183 wards ship as level-2 areas under their provinces with
SINSO pcodes (OCHA COD gazetteer; per-province counts cross-checked
against Statoids).

Solomon Islands have no postcode system. Addresses are formatted
per the UPU layout: street lines, locality, and country.
## Sweden

The bundled `SwedenGeographyProvider` supplies the 21 counties as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SE')` after
countries are seeded.
The 290 municipalities ship as level-2 areas under their counties.

Swedish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country. An `SE-` prefix passes through when supplied.
## Switzerland

The bundled `SwitzerlandGeographyProvider` supplies the 26
cantons as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CH')` after countries are seeded.
The 146 districts, regions and constituencies ship as level-2 areas under their cantons.

Swiss addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode (office numbers and
canton abbreviations pass through), and country.
## Aland

The bundled `AlandGeographyProvider` supplies the 16
municipalities as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AX')` after countries are seeded.
Municipality codes 01–16 are dataset-invented: ISO defines no
Åland subdivisions, and the official Finnish 3-digit kuntakoodi are
not used, so the codes carry no external meaning.

Åland addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit `22xxx` postcode, and country.
International mail prefixes `AX-`; the formatter prints the postcode
exactly as supplied. Municipalities are labelled `Kommun` (Swedish).
## Faroe Islands

The bundled `FaroeIslandsGeographyProvider` supplies the 6
regions as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('FO')` after countries are seeded.
The 29 municipalitys ship as level-2 areas under their regions.

Faroese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `FO-NNN` postcode, and country. Old
Danish `38xx` codes are obsolete. Municipalities are labelled `Kommuna` (Faroese).
## Guernsey

The bundled `GuernseyGeographyProvider` supplies the 10 parishes
plus Alderney and Sark as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GG')` after countries are
seeded. Alderney and Sark ship as dependencies, not parishes.

The 10-code overlay (GY1–GY10) cross-checks GeoNames against the
postcode-area table at parish level: Herm and Jethou stay St Peter
Port, 3 shared codes dual-linked with alphabetical primaries; no new
area rows.

Guernsey follows the UK postcode system (`GY` prefix, not `GG`).
Addresses print street lines, the post town, the postcode on its own
line, and country.

## Jersey

The bundled `JerseyGeographyProvider` supplies the 12 parishes as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('JE')` after
countries are seeded.
The 48 vingtaines, 2 cantons and 6 cueillettes ship as level-2 areas under their parishes.

The 2-code overlay (JE2/JE3 outward codes) comes from the
postcode-area table at parish level (large-user, PO-box and
bespoke-delivery ranges excluded as non-geographic; outward codes
cannot reach vingtaine level); no new area rows.

Jersey follows the UK postcode system (`JE` prefix). Addresses print
street lines, the post town, the postcode on its own line, and country.
## Isle of Man

The bundled `IsleOfManGeographyProvider` supplies the 6 sheadings
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('IM')` after
countries are seeded. Sheadings are the former administrative
The 13 parishes, 4 towns, 2 districts and 2 villages ship as level-2 areas under their sheadings.
partition (today only a loose coroners/electoral layer), but they
remain the only island-wide geography, so they are modeled as the
address level.

The 9-code overlay (IM1–IM9) parish-maps GeoNames localities at L2
(PO-box/large-user codes and 4 conflicted singles excluded); no new
area rows.

The Isle of Man follows the UK postcode system (`IM` prefix).
Addresses print street lines, the post town, the postcode on its own
line, and country. The formatter prints `Isle of Man` rather than
the database's inverted `Man (Isle of)` spelling.

## Tonga

The bundled `TongaGeographyProvider` supplies the 5 divisions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TO')` after
countries are seeded.

The 23 districts ship as level-2 areas with ISO 3166-2 codes
(7 Tongatapu, 6 Vava'u, 6 Ha'apai, 2 'Eua, 2 Niuas; the source
table duplicates TO-024, so Ha'ano carries the correct TO-025).
Villages are not bundled.

Tonga has no postcode system; the formatter prints any supplied
code on its own line.

## American Samoa

The bundled `AmericanSamoaGeographyProvider` supplies the 3
districts and 2 atolls as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AS')` after countries are
seeded.

The 15 counties ship as level-2 areas (5 Western, 5 Eastern, 5
Manu'a, including Fofo; Aunu'u island belongs to Sa'ole county).
Rose and Swains atolls are childless. Villages are not bundled.

American Samoan addresses use the US ZIP layout
(`{locality} AS {ZIP}`, ZIP+4 supported).

## Wallis and Futuna

The bundled `WallisAndFutunaGeographyProvider` supplies the 3
kingdoms (administrative precincts) as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('WF')` after countries are
seeded.

Only Uvea is further subdivided: its 3 districts (Hihifo, Hahake,
Mu'a) ship as level-2 areas. Alo and Sigave are childless.
Villages are not bundled.

The formatter prints the code left of the locality
(`98600 MATA-UTU`); Futuna uses 98620.

## Marshall Islands

The bundled `MarshallIslandsGeographyProvider` supplies the 24
municipalities and 2 chains as `State` rows and a two-level
administrative hierarchy (restructure: chains promoted to the
state level, municipalities nested beneath). It is selected with
`SeedCountryGeographiesAction::execute('MH')` after countries are
seeded.

The 24 inhabited municipalities ship as level-2 areas under
their census chain (14 Ralik, 10 Ratak). Uninhabited atolls are
not bundled.

Marshallese addresses use the US ZIP layout
(`{locality} MH {ZIP}`); Ebeye uses 96970.

## Guam

The bundled `GuamGeographyProvider` supplies the 19 villages as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GU')` after
countries are seeded. Villages are municipalities governed by
elected mayors; there is no administrative tier below them (the
North/Central/South regions are statistical groupings only).

Guamanian addresses use the US ZIP layout
(`{locality} GU {ZIP}`).

## Guatemala

The bundled `GuatemalaGeographyProvider` supplies the 22
departments as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GT')` after countries are seeded.
The 340 municipalities ship as level-2 areas under their
departments.

Guatemalan addresses are formatted per the UPU layout: street
lines, `{postcode} - {locality}` with a 5-digit postcode, and
country. Types are labelled `Departamento` and `Municipio`.

## Nauru

The bundled `NauruGeographyProvider` supplies the 14 districts as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NR')` after
countries are seeded. The 169 villages are historical (1908
expedition source, merged into a single coastal settlement, no
current admin function) and are intentionally not bundled.

Nauru has a sole national postcode, NRU68, printed on its own
line below the district.

## Niue

The bundled `NiueGeographyProvider` supplies the 14 villages as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NU')` after
countries are seeded. Villages double as municipalities and
electoral districts; there is no tier below them.

Niue has a sole island code, 9974, printed right of the
locality.

## Micronesia

The bundled `MicronesiaGeographyProvider` supplies the 4 states
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('FM')` after
countries are seeded.

The 73 municipalities and 2 cities ship as level-2 areas (40
Chuuk, 4 Kosrae, 11 Pohnpei, 20 Yap). Weno and Kolonia are typed
city; Tol ships as a municipality though the source table bolds
it. The table's duplicate Piherarh row is shipped once; Utwe
carries the table's `Utwa` spelling as an alternative name.
Villages (including the capitals Palikir, Tofol, and Colonia,
which sit inside municipalities) are not bundled.

Micronesian addresses use the US ZIP layout
(`{locality} FM {ZIP}`); Pohnpei uses 96941, Chuuk 96942.

## Kiribati

The bundled `KiribatiGeographyProvider` supplies the 3 island
groups as `State` rows and a two-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('KI')`
after countries are seeded.

The 24 local councils ship as level-2 areas (20 Gilbert, 3
Line, 1 Canton under Phoenix). Tarawa's three councils ship as
Betio, North Tarawa, and South Tarawa; isolated Banaba is
parented to Gilbert. Villages are not bundled.

Kiribati postcodes print right of the island
(`Sth Tarawa KI0108`, `Kiritimati KI0303`).

## Tuvalu

The bundled `TuvaluGeographyProvider` supplies the 1 town
council and 7 island councils as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TV')` after countries are
seeded. The councils are the local government (Falekaupule Act);
Niulakita is administered as part of Niutao. Villages have no
separate admin function and are intentionally not bundled.
Funafuti's town council shares the `island_council` assignment
role as the capital's local government.

Tuvalu has no postcode system; the formatter prints any supplied
code on its own line.

## Palau

The bundled `PalauGeographyProvider` supplies the 16 states as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('PW')`
after countries are seeded. Hamlets are traditional (no visible
boundaries, single settlements) and 9 of 16 states have no
hamlet list at all, so no reliable tier-2 exists and states
stay terminal.

Palauan addresses use the US ZIP layout
(`{locality} PW {ZIP}`, ZIP+4 supported).

## Samoa

The bundled `SamoaGeographyProvider` supplies the 11 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('WS')` after
countries are seeded.

The 342 census villages ship as level-2 areas under their
district (2021 census via citypopulation; constituency parents
mapped through the geo-ref table: Alataua i Sisifo to
Vaisigano, Salega to Satupa'itea, Lefaga & Falease'ela to A'ana,
greater-Apia constituencies to Tuamasaga). Same-district
name twins are disambiguated CN-style (Matautu, Falelatai /
Lefaga; Mulivai, Safata / Vaimauga); 12 cross-district twins
are parent-scoped.

Samoan postcodes print right of the locality
(`Apia WS1330`).

Samoa Post's official village table, accessed 2026-09-25, lists 224
postcode pairs grouped by Upolu and Savai'i but gives no district
crosswalk. Compared with the bundled village rows, 159 entries match
uniquely, 7 are ambiguous, and 58 have no matching row. The official
list has no published reuse terms; no partial postcode overlay is
bundled pending an authoritative crosswalk and reuse terms.

## Cayman Islands

The bundled `CaymanIslandsGeographyProvider` supplies the 3
islands as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KY')` after countries are
seeded.

The 7 districts ship as level-2 areas (5 Grand Cayman, Cayman
Brac and Little Cayman self-parented). The prose "6 districts"
count merges the Sister Islands into one; the district table
lists all 7, and each nests on exactly one island, so the
earlier cross-cut ruling is superseded.

Caymanian postcodes print right of the island
(`Grand Cayman  KY1-1103`).

## Anguilla

The bundled `AnguillaGeographyProvider` supplies the 14
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AI')` after countries are
seeded. Districts are terminal; there is no tier below them.

Anguillan postcodes print on their own line below the locality
(`The Valley`, `AI-2640`).

## Antigua and Barbuda

The bundled `AntiguaAndBarbudaGeographyProvider` supplies the 6
parishes and 2 dependencies (Barbuda and uninhabited Redonda)
as `State` rows and a single-level administrative hierarchy. It
is selected with `SeedCountryGeographiesAction::execute('AG')`
after countries are seeded. Parishes and dependencies are
terminal.

Antigua and Barbuda has no postcode system.

## Aruba

The bundled `ArubaGeographyProvider` supplies the 8 regions
and the capital Oranjestad as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AW')` after countries are
seeded. Regions are statistical and terminal.

Aruba has no postcode system.

## Bahamas

The bundled `BahamasGeographyProvider` supplies the 31
districts and 1 island as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BS')` after countries are
seeded. Districts are terminal.

The Bahamas has no postcode system; Nassau P.O. boxes serve
as the locality line.

## Barbados

The bundled `BarbadosGeographyProvider` supplies the 11
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BB')` after countries are
seeded. Parishes are terminal.

Barbadian postcodes print right of the parish
(`St. Peter BB26028`).

## Belize

The bundled `BelizeGeographyProvider` supplies the 6 districts
as `State` rows and a single-level administrative hierarchy. It
is selected with `SeedCountryGeographiesAction::execute('BZ')`
after countries are seeded. City, town, village, and community
councils exist below the districts but no consolidated
district-mapped list ships, and the 31 constituencies are
electoral only, so districts stay terminal.

Belize has no postcode system.

## Bermuda

The bundled `BermudaGeographyProvider` supplies the 9
parishes as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BM')` after countries are
seeded. The City of Hamilton and the Town of St George ship as
level-2 municipalities under Pembroke and Saint George's
parishes respectively.

Bermudian postcodes print right of the locality
(`SMITH'S FL 07`).

## Bolivia

The bundled `BoliviaGeographyProvider` supplies the 9 departments
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BO')` after
countries are seeded.
The 112 provinces ship as level-2 areas under their departments.

Bolivia has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the department, and country;
any supplied code prints on its own line. Types are labelled
`Departamento` and `Provincia`.

## Caribbean Netherlands

The bundled `CaribbeanNetherlandsGeographyProvider` supplies
the 3 special municipalities (Bonaire, Saba, Sint Eustatius) as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BQ')`
after countries are seeded. Municipalities are terminal.

Addresses print the island as its own line below the town
(`KRALENDIJK`, `Bonaire`). The tier is labelled
`Bijzondere Gemeente`.

## Dominica

The bundled `DominicaGeographyProvider` supplies the 10
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DM')` after countries are
seeded. Parishes are terminal.

Dominica has no postcode system.

## Grenada

The bundled `GrenadaGeographyProvider` supplies the 6 parishes
and the Carriacou dependency as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GD')` after countries are
seeded. Parishes and the dependency are terminal.

Grenada has no postcode system.

## Jamaica

The bundled `JamaicaGeographyProvider` supplies the 14
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('JM')` after countries are
seeded. Parishes are terminal; postal towns below them are not
administrative.

Jamaican addresses print street lines, locality, post town,
parish, and country.

## Saint Lucia

The bundled `SaintLuciaGeographyProvider` supplies the 10
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LC')` after countries are
seeded. Districts are terminal.

Saint Lucian postcodes print right of the locality
(`CASTRIES, LC04  101`).

## Saint Vincent and the Grenadines

The bundled `SaintVincentAndTheGrenadinesGeographyProvider`
supplies the 6 parishes as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('VC')` after countries are
seeded. Parishes are terminal.

Vincentian postcodes print on their own line below the town
(`KINGSTOWN`, `VC0120`).

## Trinidad and Tobago

The bundled `TrinidadAndTobagoGeographyProvider` supplies the
5 boroughs, 7 regions, 2 cities, and 1 ward as `State` rows and
a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TT')` after countries are
seeded. The first level is already the municipal level, so
there is no tier-2.

Postcodes print right of the locality (`CHAGUANAS 500234`).

## Turks and Caicos

The bundled `TurksAndCaicosGeographyProvider` supplies the 6
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TC')` after countries are
seeded. Districts are terminal.

The UK-style postcode prints on its own line (`TKCA 1ZZ`).

## Montserrat

The bundled `MontserratGeographyProvider` supplies the 4
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MS')` after countries are
seeded. Parishes are terminal; villages below them have no
separate administration. Saint Patrick (code `04`) ships even
though it is uninhabited (volcanic exclusion zone, incl. Plymouth).

Montserrat postcodes print right of the locality
(`Brades, MSR1110`).

## Greenland

The bundled `GreenlandGeographyProvider` supplies the 5
municipalities as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GL')` after countries are
seeded. Municipalities are terminal; towns are municipal seats,
not administrative units.

Greenlandic postcodes print left of the locality
(`3900 Nuuk`). The tier is labelled `Kommune`.

## Saint Martin

The bundled `SaintMartinGeographyProvider` supplies the single
overseas collectivity as the `State` row and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MF')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97150 SAINT-MARTIN`).

## Saint Pierre and Miquelon

The bundled `SaintPierreAndMiquelonGeographyProvider` supplies
the single overseas collectivity as the `State` row and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PM')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97500 Saint-Pierre`).

## Saint-Barthélemy

The bundled `SaintBarthelemyGeographyProvider` supplies the
single overseas collectivity as the `State` row and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BL')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97133 Gustavia`).

## Réunion

The bundled `ReunionGeographyProvider` supplies the 4
arrondissements as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RE')` after countries are seeded.
The 24 communes ship as level-2 areas under their arrondissements.

Réunionese addresses follow the French layout with the code left
of the locality (`97400 Saint-Denis`).

## French Guiana

The bundled `FrenchGuianaGeographyProvider` supplies the single
overseas region as the `State` row and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GF')` after countries are
seeded. The 22 communes ship as level-2 areas.

Addresses follow the French layout with the code left of the
locality (`97300 CAYENNE`). Types are labelled `Région` and
`Commune`.

## French Polynesia

The bundled `FrenchPolynesiaGeographyProvider` supplies the 5
administrative subdivisions as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PF')` after countries are
seeded. The 48 communes ship as level-2 areas under their
subdivisions.

Addresses follow the French layout with the code left of the
locality (`98714 PAPEETE`). Types are labelled `Subdivision` and
`Commune`.

## Guadeloupe

The bundled `GuadeloupeGeographyProvider` supplies the 2
arrondissements (Basse-Terre, Pointe-à-Pitre) as `State` rows and
a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GP')` after countries are
seeded. The 32 communes ship as level-2 areas under their
arrondissements.

Addresses follow the French layout with the code left of the
locality (`97100 BASSE TERRE`). Types are labelled
`Arrondissement` and `Commune`.

## Martinique

The bundled `MartiniqueGeographyProvider` supplies the 4
arrondissements as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MQ')` after countries are
seeded. The 34 communes ship as level-2 areas under their
arrondissements.

Addresses follow the French layout with the code left of the
locality (`97220 LA TRINITE`). Types are labelled
`Arrondissement` and `Commune`.

## New Caledonia

The bundled `NewCaledoniaGeographyProvider` supplies the 3
provinces as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NC')` after countries are
seeded. The 33 communes ship as level-2 areas under their
provinces.

Addresses follow the French layout with the code left of the
locality (`98800 NOUMEA`). Types are labelled `Province` and
`Commune`.

## French Southern Territories

The bundled `FrenchSouthernTerritoriesGeographyProvider`
supplies the 5 districts as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TF')` after countries are
seeded. The territory is uninhabited apart from research
stations; districts are terminal.

Addresses print the base and port lines with no postcode.

## US Minor Outlying Islands

The bundled `USMinorOutlyingIslandsGeographyProvider` supplies
the 9 islands as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UM')` after countries are
seeded. The islands are uninhabited (military and wildlife
stations) and terminal.

Addresses print the station and island lines with no postcode.

## Puerto Rico

The bundled `PuertoRicoGeographyProvider` supplies the 78
municipalities as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PR')` after countries are
seeded.

The 901 barrios ship as level-2 areas (827 barrios + 74
barrio-pueblos) from the Census 2024 Gazetteer county-subdivision
file, parented by GEOID county digits; the 38 fictitious
`Municipio subdivision not defined` rows are excluded, as are
subbarrios (a third layer in 23 municipios). Both types share
the `barrio` assignment role.

Puerto Rican addresses use the US ZIP layout
(`SAN JUAN PR 00926-0221`, ZIP+4 supported).

## U.S. Virgin Islands

The bundled `USVirginIslandsGeographyProvider` supplies the 3
districts as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('VI')` after countries are
seeded.
The 20 subdistricts ship as level-2 areas under their districts.

Virgin Islander addresses use the US ZIP layout
(`ST THOMAS VI 00802-1222`, ZIP+4 supported).

## Saint Helena

The bundled `SaintHelenaGeographyProvider` supplies the 8
Saint Helena districts plus Ascension and Tristan da Cunha as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SH')`
after countries are seeded. Districts and islands are terminal;
settlements below them (Jamestown, Georgetown, Edinburgh) have
no separate administration. Ascension and Tristan da Cunha ship
as provisional states.json rows (Atauro convention).

Postcodes print right of the locality
(`JAMESTOWN STHL 1ZZ`, `Georgetown ASCN 1ZZ`).

## Mayotte

The bundled `MayotteGeographyProvider` supplies the 17
communes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('YT')` after countries are
seeded. The first level is already the municipal level, so
there is no tier-2.

Mahoran addresses follow the French layout with the code left
of the locality (`97600 MAMOUDZOU`).

## New Zealand

The bundled `NewZealandGeographyProvider` supplies the 16
regions plus Chatham Islands as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('NZ')` after countries are
seeded.

The 67 territorial authorities ship as level-2 areas (53
districts, 12 cities, Auckland and Chatham Islands councils).
Seven authorities straddle regional boundaries; each is
parented to its largest-share region. All three types share
the `district` assignment role.

1201 postal localities (suburbs, towns, rural-delivery names)
ship as level-3 `locality` areas under their district, with a
`postal` hierarchy (region > locality, refined by district)
and the `postal_locality` role. The 1737-code overlay links
each code to its locality (0110–9893; 1081 shared by Ostend
and Surfdale, Ostend primary). 58 places needed manual
resolution: GeoNames rows with empty admin2 (Tararua and
Waitaki Valley clusters, Petone, Turangi), name variants
(Puk-kura = Pukekura, Weheka = Fox Glacier), and Waioruarangi
7300 assigned to Kaikōura on NZ Post delivery rather than its
Waiau-side dump coordinate.

New Zealand postcodes print left of the locality
(`6011 Wellington`).

## Numeric state codes

Bahrain, Italy, South Korea, Saudi Arabia, Türkiye, Morocco, France,
Japan, Poland, Kenya, Tanzania, Algeria, Thailand, Vietnam, Ukraine,
Myanmar, Bhutan, Cyprus, Iran, Kazakhstan, Sri Lanka, Mongolia,
Maldives, North Korea, Burkina Faso, Congo, Gabon, Mali,
Mauritania, Niger, Rwanda, Sao Tome and Principe, Seychelles,
Tunisia, Zambia, Albania, Andorra, Austria, Bulgaria, Croatia,
Czech Republic, Denmark, Estonia, Finland, Greece, Iceland, Latvia,
Liechtenstein, Lithuania, Malta, Montenegro, North Macedonia,
Norway, Portugal, San Marino, Serbia, and Slovenia use numeric
ISO subdivision codes at the state-mapping level. Aland, Guernsey,
Jersey, and Isle of Man use dataset-invented numeric codes instead
(ISO defines no subdivisions for them). PHP casts numeric-string array keys to
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
