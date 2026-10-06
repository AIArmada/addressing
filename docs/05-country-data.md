---
title: Country Data
---

# Country Data

For per-provider depth and dataset status at a glance, see the
[Provider Coverage Registry](./14-provider-coverage.md).

## Bundled Dataset

The package always bundles ISO 3166-1 country/territory data.

File location: `resources/data/countries.json`

The bundled `MalaysiaGeographyProvider` supplies Malaysia's State/Federal Territory catalog, two explicit address hierarchies, the AddressArea hierarchy, and State↔AddressArea mappings. The primary administrative/land hierarchy is `region → division → district → subdivision` (state/wilayah_persekutuan → division → district/minor_district → city, municipality, mukim, subdistrict, bandar, pekan, daerah_kecil); the secondary postal/address hierarchy is `region → locality` (state/wilayah_persekutuan → locality/precinct), refined by the picked `administrative_district`. It is selected with `SeedCountryGeographiesAction::execute('MY')` after countries are seeded.

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

## Malaysia district-tier model

`minor_district` and `daerah_kecil` are deliberately distinct types even
though both translate to "small district". They sit at different tiers,
carry different roles, and must never be merged.

`minor_district` (5 rows, level 2 under the state, `my:district:` ids) is
the peninsular district tier: Genting, Gebeng, Jelai, and Muadzam Shah in
Pahang plus Lojing in Kelantan. Each is UPI-listed with its own mukim
children and gazette/PW creation notices (the Pahang three date to
2019–2021), behaves as a district (`administrative_district` role), and may
hold postcodes directly — Muadzam Shah holds the 26700 primary itself.
State labels render the local terms: Kelantan (03) uses Jajahan for
districts and Jajahan Kecil for minor districts; Pahang (06) uses Daerah
Kecil for minor districts.

`daerah_kecil` (23 rows: 6 Sabah + 17 Sarawak, level 4 under the district,
`my:subdistrict:district:` ids) is the East Malaysian subdivision tier:
genuine below-district administrative units, `administrative_subdivision`
role, administrative-only with no postal links. Where the town shares the
unit's name, the bare name stays on the `daerah_kecil` row and a separate
Pekan locality takes the postal links (Pekan Tamparuli, Pekan Menumbok,
Pekan Debak, and the rest).

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

Book-to-row completion (Oct 2026): diffed the UPI 2024 book for WPKL
against the CSV in both directions. The 7 mukims were already rowed.
Added the 12 missing gazetted towns: Bandar Kuala Lumpur (44), Bandar
Petaling Jaya (55), Bandar Bandar Baharu Sungai Besi (66), Bandar Sungai
Besi (67), Pekan Batu (70), Pekan Batu Caves (71, gazetted PW561/2018),
Pekan Kepong (72), Pekan Kuala Pauh (73), Pekan Petaling (74), Pekan Salak
South (75), Pekan Sungai Penchala (76), and Pekan Sungai Besi (79).
Pekan codes 77/78 are genuinely absent. All 12 hang directly under the
territory at level 2 with `bandar-`/`pekan-` slugs (bare names belong to
the parliamentary localities) and stay linkless: KL postal routing lives
on the state (290 primaries) plus the constituency localities by design,
and moving city codes onto the admin towns would gut that design. Bandar
Petaling Jaya is the KL-side gazette of the PJ town whose principal row
(and 94 codes) stays in Selangor.

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

Book-to-row completion (Oct 2026): diffed the UPI 2024 book for Selangor
against the CSV in both directions (GIS witness unavailable; town codes
corroborated against the bulk postcode SQL dump and addressed sightings).
Added 148 rows: Klang 8 (2 mukims exist; Bandar Klang, Bandar Sultan
Sulaiman, Bandar Shah Alam, and 5 pekans), Kuala Langat 19, Kuala
Selangor 18, Sabak Bernam 13 (no bandars gazetted), Hulu Langat 30,
Hulu Selangor 12, Petaling 23, Gombak 11, Sepang 14. Retyped two postal
localities to gazetted towns in place: Balakong to bandar (keeps the
43300 secondary) and Serdang to pekan (keeps 43400). All 9 districts now
match UPI counts exactly (12/29/28/20/39/26/33/20/20). Genuine code gaps
kept: Kuala Selangor mukim 02, Hulu Langat pekan 70/72/74, Sepang pekan
73. Kuala Selangor code 81 is one entity (entity table KAMPONG, rajah
KAMPUNG); Hulu Langat code 75 Pekan Tarun is coded but TIADA warta.

Naming: new towns take the mukim-consistent stem with the pure-UPI form
as the alternative (Bandar Hulu Langat / Hulu Yam / Hulu Bernam I+II /
Hulu Klang; Pekan Batu 18 Hulu Langat; Bandar Hulu Yam Baharu carries
both the Ulu and the Baru forms). Bandar+pekan pairs follow the
bandar-principal rule: the code links live on the bandar, the pekan stays
linkless unless it has its own code (Jenjarom, Sijangkang, Tanjong
Karang, Subang Jaya, Kuang, Sungai Buloh, Baru Salak Tinggi, Sungai
Merab, Cheras, Kajang, Semenyih, Batu 26 Beranang). Genuine doubles kept:
Bandar Shah Alam under Klang (01/43) and Petaling (08/41) — the Klang row
keeps the prefixed slug against the principal Petaling town row and stays
linkless; Bandar Baru Bangi under Hulu Langat and Sepang (43650
secondary on the Sepang row).

Town-code primaries moved to the new rows (92 moves): the 39 Klang town
codes to Bandar Klang; 42200-set to Pekan Kapar; the TPG set to Bandar
Telok Panglima Garang; 42600 off the district to Bandar Jenjarom; the
Kuala Selangor sets to their bandar/pekan rows; the 45200-set to Pekan
Sabak; 45100 off the district to Pekan Sungai Air Tawar; the Cheras,
Hulu Langat, Kajang, Semenyih, Ampang, and Beranang sets to their rows;
the Batang Kali, Kerling, Rasa, and Serendah sets; the 7 Rawang codes to
Bandar Rawang; the 47000-set cross-district from Petaling's Mukim Sungai
Buloh to Gombak's Bandar Sungai Buloh (gazetted 09/48+76; town under MPS
administration); 48100 off the district to Bandar Batu Arang; 43900 to
Bandar Sepang; the 43800-set to Pekan Dengkil; and 64000 off the district
to Bandar Lapangan Terbang Antarabangsa Sepang, each with a
covering-mukim secondary. 42920 likewise moved off the Klang district
row to the new Bandar Pulau Indah town row (Oct 2026; former name
Pulau Lumut kept as the alternative — postcode.my headers the code
"42920 Pulau Lumut" and the cell is Pos-live), with Mukim Klang as
the covering secondary. Petaling city primaries (PJ, Shah Alam, Subang
Jaya, Puchong) all stay; the new rows take shared-code secondaries only.

Shared-code secondaries on the new rows: 42000 Sultan Sulaiman/Pandamaran;
40460 Bukit Kemuning; 41000 Telok Menegun (addressed sightings); 42500
Sijangkang; 42800 Pekan Batu; 42700 Bukit Changgang/Chodoi/Kelanang Batu
Enam/Morib/Permatang Pasir/Sungai Manggis/Sungai Raba/Tanjong
Duabelas/Telok Datok; 45700 Asam Jawa; 45600 Kampong Kuantan/Simpang Tiga
Ijok; 45000 Pasir Penambang/Bukit Belimbing; 45800 Simpang Tiga/Parit
Mahang/Sungai Sembilang/Tambak Jawa; 45500 Kampong Baru Hulu Tiram Buruk;
45300 Parit Enam/Parit Sembilan/Simpang Lima/Sungai Haji Dorani; 45400
Sekinchan Site/Sungai Nibong; 45209 Bagan Terap; 45200 Sungai Sepintas;
45100 Parit Baru; 43000 Country Height/Bangi Lama/Kampung Sungai
Tangkas/Simpang Balak/Sungai Kembong; 43200/43207 Batu 9 Cheras; 43700
Batu 26 Beranang; 43500 Kachau/Tarun/Kampung Pasir Batu 14/Batu 23 Sungai
Lalang; 43100 Batu 18 Hulu Langat/Desa Raya/Dusun Tua/Sri Nanding/Sungai
Lui; 44300 Hulu Yam/Hulu Yam Baharu/Sungai Chik; 44100 Kalumpang; 48000
Simpang Sungai Choh; 40150 Glenmarie/Hicom/Subang/Baru Subang/Batu Tiga;
40160 Merbau Sempak/Baru Sungai Buloh; 46000 Sungai Penchala/Penaga; 47400
Kayu Ara; 47100/47170/47180/47190 Kinrara; 47170 Puchong Jaya; 47150
Puchong Perdana; 47500 Sunway; 43300 Baru Sungai Besi; 48020/48050
Kundang; 68100 Selayang/Templer; 48000 Templer; 48050 Pengkalan Kundang;
43900 Baru Salak Tinggi/Salak/Tanjung Mas/Tanjung Mas 1; 43000 Sungai
Merab; and the cross-state/cross-district set: 35900 (Perak-held) on both
Hulu Bernam bandars, 52100 (KL-held) on Bandar Kepong, 52200 on Bandar
Sri Damansara, 53100 on Bandar Sungai Pusu and Gombak Setia (which also
rescues three previously linkless Sept bandars: Kundang, Selayang, Gombak
Setia), 54200 on Bandar Hulu Klang, and 60000 on Pekan Sungai Penchala.

Two consolidations supersede Sept placements: the sabak-bernam locality
removed (Pekan Sabak is the town; Sabak Bernam kept as the preferred
common name) and the teluk-panglima-garang locality removed (same town as
the new bandar). Linkless for lack of own-code evidence: Batu Empat,
Kancung, Kancung Darat, Simpang Morib, Telok, Tongkah, Kuala Sungai
Buloh, Bukit Talang, Taman PKNS, Bagan Nakhoda Omar, Pasir Panjang
(pekan), Batu 18 Semenyih, Bukit Sungai Raya, Rumah Murah Sungai Lui,
Sungai Makau, Peretak, Damansara, Petaling Jaya Selatan, Cempaka, Country
Height (Petaling), Desa Puchong, Baru Hicom, Batu (Gombak pekan),
Mimaland, Batu 1 Sepang, Bukit Bisa, Bukit Prang, and Dato' Bakar Baginda
— plus the 12 bandar-principal pekans and the Klang Shah Alam row. Batu
Caves, Seri Kembangan, and Saujana stay as they were (town inside a
mukim / non-gazetted / no code anchor).

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

Book-to-row completion (Oct 2026): the Sept audit diffed rows against the
book but never the book against the rows, so 36 UPI bandars and pekans were
missing. Diffed the UPI 2023 book for Pahang plus the JUPEM UPI MapServer
Admin3 layer (independent GIS witness) against the CSV in both directions.
Added 14 bandars — Bandar Bentong, Bandar Tanah Rata, Bandar Kuala Lipis,
Bandar Pekan, Bandar Mentakab, Bandar Triang, Baharu Rompin, Rompin I–IV,
Bandar Pontian, Bandar Endau, Bandar Tioman — and 22 pekans: Telemung,
Lubok Tamang, Pekan Ringlet, Pekan Kuala Tembeling, Jeransang, Pekan
Beserah, Tanjung Lumpur, Pekan Kuala Pahang, Nenasi, Pekan Raub, Pekan
Dong, Pekan Tras, Cheroh, Sang Lee, Sungai Ruan, Sungai Kelau, Pekan
Kerdau, Pekan Tioman, Pekan Chenor, Sri Jaya, Durian Tawar, Mengkuang.
Retyped Gambang to bandar and Benta + Padang Tengku to pekan; Mengkarak
converts from postal locality to pekan (gazetted UPI pekan all along).
Child counts after completion: Rompin 14, Raub 16, Cameron Highlands 7,
Bera 6.

Town-code primaries moved to the new rows: the 39000-set to Bandar Tanah
Rata, 39200 to Pekan Ringlet, the 26600-set to Bandar Pekan, the 28400-set
to Bandar Mentakab, 28100 to Pekan Chenor, the 28300-set to Bandar Triang,
27400 to Pekan Dong, the 28700-set to Bandar Bentong, and 26100 to Pekan
Beserah (primary moves; the secondary stays on the Beserah mukim). The
27500 district-held primary moves to Sungai Ruan pekan. 26150 stays primary
on Sungai Karang for lack of Beserah-town evidence. Pekan Raub stays
linkless: the Raub town codes (27600/27630) already sit on the Raub bandar
row from the September audit, and UPI lists the pekan as a separate
entity. New rows without town codes are linkless. Spelling: `Telemung` keeps the UPI form with `Telemong`
as alternative; Cameron's `Lubok Tamang` kept as the UPI form.

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

Book-to-row completion (Oct 2026): the Sept audit diffed rows against the
book but never the book against the rows. Diffed the UPI 2024 book for
Johor against the CSV in both directions (GIS witness unavailable — the
MyGeoportal UPI MapServer no longer resolves; spellings corroborated
against the Sept alternative-name verdicts, town codes against postcode
directories and addressed sightings). Added 16 bandars — Bandar Tebrau,
Bandar Paloh, Bandar Rengam, Bandar Jemaluang, Mersing Kanan, Bandar
Padang Endau, Bandar Bukit Kepong, Bandar Parit Jawa, Bandar Benut, Bandar
Bekok, Bandar Buloh Kasap, Bandar Jementah, Bandar Labis, Bukit Kangkar,
Parit Bunga, Bandar Serom — and 2 pekans: Gemas Bahru and Pekan Grisek.
Retyped Panchor to bandar (UPI lists only Bandar Panchor 06/43); removed
the Bandar Segamat mukim duplicate (UPI has no such entity — the Sept
removal had regressed). All 10 districts now match UPI counts exactly
(19/8/11/11/18/17/14/18/5/12).

Town-code primaries moved to the new rows: 82200 to Bandar Benut,
84150/84160 to Bandar Parit Jawa, 86600 to Bandar Paloh, 86300 to Bandar
Rengam, 85200/85210/85220 to Bandar Jementah, 85300 to Bandar Labis, 86500
to Bandar Bekok, 85010 to Bandar Buloh Kasap, and 84700/84710 to Pekan
Grisek. The gazetted town takes the primary from the common-spelling
locality, which stays secondary (Renggam, Gerisek); covering-mukim
secondaries were added on the eight main town codes per the Sept
covering-areas rule. 85010 is Buloh Kasap's own code (Wikipedia +
addressed sightings), not Segamat town's. Shared-code secondaries: 84600
(Pagoh) on Bandar Bukit Kepong, 73400 (N9 Gemas) on Gemas Bahru — this
supersedes the sweep's "Gemas Baharu (weak evidence)" skip — 84000 (Muar
town) on Parit Bunga, and 84400 (Sungai Mati) on Bandar Serom and Bukit
Kangkar. Linkless for lack of own-code evidence: Bandar Tebrau (Tebrau
addresses use 81100 Johor Bahru), Bandar Jemaluang (86800 Mersing),
Mersing Kanan, and Bandar Padang Endau. Spelling verdicts stand: modern
rows with archaic alternatives (Niyor/Nyior, the Sungei pair); the new
rows carry Gemas Baru, Renggam, and Gerisek alternatives.

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

Sabah sweep: superseded by the below-district rectification below — the
level-4 tier is now `daerah_kecil` (6 gazetted units) plus `locality`
towns, with 14 town localities added (Pekan Tamparuli, Pekan Menumbok,
Pekan Membakut, Pekan Sook, Pekan Paitan, Pekan Kalabakan, Pekan Nabalu,
Pekan Lok Kawi, Bandau, Langkon, Pekan Matunggong, Pekan Sikuati, Pekan
Karakit, Merotai Besar). Still skipped: Kanibongan (no verified postcode
evidence).

Sarawak sweep: superseded by the below-district rectification below —
the level-4 tier is now `daerah_kecil` (17 gazetted units) plus `locality`
towns, with 16 town localities added (Pekan Debak, Pekan Spaoh, Pekan
Roban, Pekan Sundar, Pekan Trusan, Pekan Nanga Medamit, Pekan Oya, Pekan
Balingian, Pekan Engkilili, Pekan Long Lama, Pekan Bario, Pekan Sematan,
Pekan Sadong Jaya, Pusa, Sebauh, Tatau). Still skipped: Bako (fishing
village); Long Bedian and Long Akah (council-styled pekan without any
postcode evidence).

WP sweep: no additions — KL's 11 parliamentary `locality` rows plus 7
mukims, Putrajaya's precincts, and Labuan's 28 kampung `locality` rows
already model each territory. KL neighborhoods (Bangsar, Mont Kiara,
and the like) deliberately skipped as sub-localities.

Putrajaya verification (Oct 2026): no JUPEM UPI book exists for
Putrajaya — the territory has no mukim/bandar/pekan structure, so there
is nothing to diff. Verified instead against Perbadanan Putrajaya
material: exactly 20 precincts (Presint 1–20), all rowed at level 2 with
complete postcode links (town codes on the lead precincts, agency/locked-
bag codes on the territory). No changes.

## Perlis

Perlis has no districts (`TIADA DAERAH`) and exactly 22 mukims, all already
bundled with exact names. Book-to-row completion (Oct 2026): added the
two missing gazetted town rows that pair with bare mukims — Bandar Arau
(code 40) and Pekan Kuala Perlis (code 70) — and retyped the two bare
postal localities that the book gazettes as towns: Kangar to bandar
(code 41, keeps the 01000 primary) and Kaki Bukit to pekan (code 72,
keeps 02200). The 02600-set primaries moved from Mukim Arau to Bandar
Arau and 02000 from Mukim Kuala Perlis to Pekan Kuala Perlis, each with
covering-mukim secondaries; town codes corroborated against the bulk
postcode SQL dump. Padang Besar and Simpang Empat have no book entity
and stay postal localities (02100/02700 primaries kept). All 28
subdistricts hang directly under the state at level 2.

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

Book-to-row completion (Oct 2026): reverses the Sept "missing entities are
not added" rule for Melaka's mukim/town duals. Diffed the UPI 2024 book in
both directions (no GIS witness; town codes from the v-swiss directory and
addressed sightings). Added 24 rows: 1 mukim (Padang Semabok), 4 bandars
(Bandar Bukit Baru, Bandar Merlimau, Bandar Masjid Tanah, Bandar Pulau
Sebang), and 19 pekans (Ayer Molek, Batu Berendam, Bukit Rambai, Kandang,
Klebang, Paya Rumput, Sungai Udang, Tangga Batu, Tanjong Kling, Batang
Malaka, Chin Chin, Kesang Pajak, Nyalas, Selandar, Sempang Bekoh, Sungai
Rambai, Durian Tunggal, Kuala Sungai Baru, Rembia). Converted the Lubok
China postal locality to a gazetted pekan in place (78100 follows). All 3
districts now match UPI counts exactly (40/31/38).

Town-code primaries moved to the new rows: 76400/76409 to Pekan Tanjong
Kling, 77300/77309 to Bandar Merlimau, 77500 to Pekan Selandar, the
78300-set to Bandar Masjid Tanah, 76100/76109 to Pekan Durian Tunggal,
78200 to Pekan Kuala Sungai Baru, 76300 to Pekan Sungai Udang, and
77400/77409 to Pekan Sungai Rambai, each with a covering-mukim secondary.
Suburb-town secondaries (v-swiss town labels keep the locality on the
city): 75150 Bandar Bukit Baru, 75200 Klebang, 75260 Pekan Bukit Rambai,
75350 Pekan Batu Berendam, 75460 Pekan Ayer Molek and Pekan Kandang, 76450
Pekan Paya Rumput, 76400 Pekan Tangga Batu, 77000 Kesang Pajak and Pekan
Chin Chin, 77100 Pekan Nyalas, 78000 Pekan Rembia, 75050 Padang Semabok,
and cross-state 73000 (Tampin-held) on Bandar Pulau Sebang. Linkless for
lack of code evidence: Pekan Batang Malaka and Sempang Bekoh. Spelling:
the new Sungai-stem pekans carry Pekan Sungei alternatives; Chin Chin is
a genuine double (DOSM census lists "Pekan Chinchin"), and Sempang Bekoh
follows UPI+DOSM against the tempting Simpang normalization.

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

Two-hierarchy relocation: the removed postal towns returned as `locality`
rows under their corrected districts (Kubang Semang corrected to SPT Mukim
5's district); 14200's primary sits on the Sungai Jawi locality.

Book-to-row completion (Oct 2026): verified every numbered and lettered
mukim against the UPI 2024 book — SPT Mukim 1-21, SPU Mukim 1-14+16 (the
15 skip is genuine), SPS Mukim 1-16, Timor Laut Mukim 13-18, Barat Daya
Mukim 1-12 plus Mukim A-J — all already rowed. The asterisk-form town
codes parse as bandars: SPT code 40 Bukit Mertajam and Timor Laut code 44
George Town, both already rowed. Added the 3 missing gazetted bandars:
SPS code 41 Bandar Sungai Bakap (book spelling SUNGEI kept as the
alternative), Timor Laut code 47 Tanjong Tokong, and code 48 Tanjong
Pinang (gazetted WKP 174/6.5.2004). Postal: 10470's 57 bulk-dump streets
are all Tanjong Tokong / Tanjung Pinang / Mt Erskine area, so the
primary moved off Bandar George Town to Tanjong Tokong with a secondary
on Tanjong Pinang; 14200's primary stays on Sungai Jawi (the principal
postal town) with a secondary on Bandar Sungai Bakap. Bertam, Batu
Kawan, and Teluk Bahang have no book entity and stay postal localities.

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

Book-to-row completion (Oct 2026): diffed the UPI 2024 book for
Terengganu against the CSV in both directions (GIS witness unavailable;
town codes corroborated against the bulk postcode SQL dump). Added 13
rows: Pekan Kampung Raja, Pekan Kuala Besut, Pekan Kuala Paka, Mukim
Cukai, Pekan Air Jernih, Pekan Air Putih, Pekan Kemasik, Pekan Kijal,
Pekan Cabang Tiga, Pekan Kuala Berang, Pekan Bukit Payung, Mukim Tasik
(Setiu), and Mukim Pakoh (Kuala Nerus). All 8 districts now match UPI
counts exactly (19/13/17/21/10/8/7/4). Parser notes: Besut 71's "TRGN" is
the gazette prefix (TRGN 507/76), not part of the name; the Dungun "code
28 Mukim Kuala Dungun" is a page-number artifact, not a double.

Town-code primaries moved to the new rows (15 moves): 22200 to Pekan
Kampung Raja; the 22300-set to Pekan Kuala Besut; the 24200-set to Pekan
Kemasik; the 24100-set to Pekan Kijal; 21700 to Pekan Kuala Berang; 21400
to Pekan Bukit Payung; and 24050 to Pekan Air Putih, each with a
covering-mukim secondary. Bandar Cukai keeps the 24000-set with new
covering secondaries on Mukim Cukai (added as `mukim-cukai` since the
bare slug belongs to the established Sept town row); 23100's primary
stays on the Paka postal town with a new secondary on Pekan Kuala Paka.
Linkless for lack of own-code evidence: Air Jernih, Cabang Tiga, Tasik,
and Pakoh.

Two consolidations supersede Sept placements: the bukit-payong locality
removed (same town as Pekan Bukit Payung; Payong kept as the
alternative), and the ayer-puteh mukim removed — it was Pekan Air Putih
misclassified, renamed to the gazetted Air Putih form with Ayer Puteh
(postal usage) kept as the alternative.

Cross-district 21040 (Oct 2026): keeps the Bandar Kuala Terengganu
primary and gains Marang secondaries on Mukim Jerung and Mukim Bukit
Payung for Kampung Temiang, Kampung Jerong Seberang, and Kampung Jerong
Tuan (Pengkalan Berangan/Jerung cluster; "Jerong" is the kampung/school
spelling of Mukim Jerung — SK Jerong polls the Jerung Surau voting
district, and the gazette places Pengkalan Berangan under Mukim Jerung).

## Perak mukim audit

Every Perak subdivision row was diffed against the JUPEM UPI boundary book
for Perak (Sept 2026; 13 districts), cross-checked against PLANMalaysia
kod-mukim, DOSM census divisions, state gazettes, and land-title records.
Structural fixes: the combined Larut-Matang-dan-Selama district row was split
into Larut Matang (15 rows) and Selama (3 rows); Sungai Sumun moved from
Hilir Perak to Bagan Datuk; Trolak moved from Batang Padang to Muallim as
Pekan Terolak. Ipoh town split into Bandar Ipoh (U) and Bandar Ipoh (S) with
all 112 town postcodes linking the Kinta district (Muadzam precedent; the
U/S line runs east-west across the town centre per plan PW 5296). Retyped 17
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

Book-to-row completion (Oct 2026): the Sept audit diffed rows against the
book but never the book against the rows. Diffed the UPI 2024 book for
Perak against the CSV in both directions (GIS witness unavailable — the
MyGeoportal UPI MapServer no longer resolves; town codes corroborated
against the v-swiss postcode directory, the bulk postcode SQL dump, and
addressed sightings). Added 75 rows: 3 mukims (Tronoh, Temelong,
Temengor), 22 bandars, and 50 pekans. Retyped three postal localities to
gazetted pekan in place: Simpang Lima, Changkat Jering (keeps the 34850
primary), and Rantau Panjang (keeps 34140, superseding the Sept uncertain
district link, as does 34850). All 13 districts now match UPI counts
exactly (15/21/22/17/19/20/10/15/6/18/6/8/14 in book district order).

Town-code primaries moved to the new rows (47 moves): the Bidor,
Chenderiang, and Sungkai codes to their bandars; 32100/32200 to Bandar
Lumut; 32700 to Pekan Beruas; the 32000-set to Pekan Sitiawan; 31800 to
Pekan Tanjong Tualang; 34300/34310 to Bandar Bagan Serai; 34350 to Bandar
Kuala Kurau; 34200 to Bandar Parit Buntar; 34250 to Pekan Tanjong
Piandang; the Sungai Siput set to its bandar; 34600 to Bandar Kamunting;
the 34500-set to Pekan Batu Kurau; 34700 to Pekan Simpang; 34800 to Pekan
Terung; the Gerik, Pengkalan Hulu, and Lenggong sets to their bandars;
the 34100-set to Bandar Selama; the 31900-set to Bandar Kampar; 36100 to
Pekan Bagan Datuk; 36400 to Pekan Hutan Melintang; the 36300-set to Pekan
Sungai Sumun; and 31750 to Bandar Tronoh, each with a covering-mukim
secondary per the Sept covering-areas rule. Shared-code secondaries on
the new rows: 31900 Ayer Kuning, 35400 Banir, 35600 Bikam, 35350 Temoh
Station, 32200 Damar Laut/Segari, 32000 Kampong Koh/Kampong
Sitiawan/Gurney, 32300 Pasir Bogak/Sungai Pinang Kechil, 34400 Bukit
Merah, 34300 Sungai Gedong, 33000 Jerlun, 33600 Karai, 33020 Kati, 31100
Salak, 36000 Batak Rabit, 36700 Degong, 33300 Lawin, 32600 Bota Kanan,
32800 Kampong Buloh Akar/Tanjong Belanja, 35950 Proton, 36400
Jendarata/Simpang Empat/Simpang Tiga, 35800 Pekan Slim, 36810 Pekan Kota
Setia, 33010 Pekan Lubok Merbau, and 31600/31610 Kota Baharu. Linkless
for lack of own-code evidence: Sungai Lesong, Kampong Baharu, Pekan
Pengkalan Baharu, Jalan Baru, Gunong Pondok, Pondok Tanjong, Sungai
Bayur, Batu Dua Puloh, Kampong Sungai Haji Mohamed, and the seven
district-mesh Kinta rows (Jelapang, Menglembu, Papan, Seputeh, Kanthan,
Simpang Pulai, Bandar Sungai Raya). Spelling: UPI forms kept with common
alternatives (Teluk Bharu/Baru, Kampong/Kampung forms, Batu Dua
Puloh/Puluh); the book's Sungai Pinang Kechil spelling verified as
printed.

Two consolidations supersede Sept placements. Tronoh: the book gazettes
Mukim Tronoh (WK.3747/29.09.2022) and Bandar Tronoh (code 56) under
Kinta, so the Sept kampar:tronoh postal locality was a wrong-district
duplicate — removed, 31750 primary to Bandar Tronoh. Trong: the book
gazettes only Mukim + Pekan Terung under Larut-Matang, and Sept already
judged Trong the same place — locality removed as redundant. Genuine
doubles kept: Pekan Simpang Empat under both Kerian (code 72, 34400) and
Bagan Datuk (code 76, 36400 secondary), and the Layang-Layang pair.
Ayer Kuning verdict: gazetted Pekan Ayer Kuning is Batang Padang (code
70, 31900 secondary correct); the 34850 Ayer Kuning is a namesake kampung
near Changkat Jering, not a gazetted entity. The seven remaining postal
localities (Jeram, Kampung Kepayang, Seri Manjung, Behrang Stesen,
Enggor, Sauk, Ulu Kinta) have no book entity and stay.

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

Book-to-row completion (Oct 2026): the Sept audit diffed rows against the
book but never the book against the rows. Diffed the UPI 2024 book for
Kedah against the CSV in both directions (GIS witness unavailable — the
MyGeoportal UPI MapServer no longer resolves; town codes corroborated
against the v-swiss postcode directory, courier zone lists, and addressed
sightings). Added 71 rows: 2 mukims (Teloi Kiri, Bukit Raya — the latter
pre-corroborated by the Sept Bukit Paya verdict), 18 bandars, and 51
pekans. Retyped the four mistyped town mukims (Bandar Jitra, Bandar Sungai
Petani, Bandar Baling, Bandar Kulim) to bandar in place, and converted the
Tikam Batu postal locality to a gazetted bandar (08700 primary stays
Jeniang, Tikam Batu keeps its secondary). Consolidated three duplicated
towns onto their bare bandars, moving every link: ~80 Alor Setar codes,
the 06700-set to Pendang, and 06350/06400 to Pokok Sena, collapsing 9
duplicate secondaries; removed the linkless Pekan Sik mukim duplicate.
All 12 districts now match UPI counts exactly
(29/36/18/10/28/11/8/18/24/13/16/8; Yan 11 counts the book's Guar
Cempedak/Chempedak code-40 spelling variant once).

Town-code primaries moved to the new rows: the 07000-set to Bandar Kuah,
06500/06507 to Bandar Langgar, the 08300-set to Bandar Gurun, the 08400-set
to Bandar Merbok, 06900/06910 to Bandar Yan, the 08200-set to Bandar Sik,
09200 to Bandar Kupang, 09700 to Pekan Karangan, and 09800/09810 to Bandar
Serdang, each with a covering-mukim secondary per the Sept covering-areas
rule. Shared-code secondaries on the new rows: 05250 Alor Merah, 06200
Bukit Pinang, 06250 Alor Janggus, 06550 Bandar Anak Bukit, 06650/06660
Tokai, 06300 Bukit Tembaga/Padang Sanai/Durian Burung, 06350/06400 Naka,
06400 Kebun 500, 06700 Tanah Merah, 06750 Sungai Tiang, 06800 Kobah, 06100
Padang Sera/Sintok/Pekan Sanglang, 06150 Kuala Sanglang/Kerpan/Sungai
Korok, 06000 Bandar Tunjang/Napoh, 08000 Sungai Lalang/Bandar Aman Jaya,
08010 Bukit Selambau, 08600 Tikam Batu, 09300 Merbau Pulas, 09200 Parit
Panjang, 09700 Pekan Mahang/Sungai Kob, 09800 Lubuk Buntar, 08800 Guar
Cempedak, 08100 Bandar Semeling, 08110/08400 Pekan Singkir and Tanjung
Dawai, 08200 Charok Padang, 08210 Gulau, 06710 Lubok Merbau (PPD Padang
Terap but postally Pendang — post office and SMK both 06710), 14390 Sungai
Kechil Ilir (Penang-held code, school-address evidence), and 09310 Pekan
Tawar. This supersedes three sweep skips: Naka, Tanjung Dawai, and
Sintok/Napoh. Linkless for lack of own-code evidence: Padang Lalang,
Telok Datai, Bandar Padang Mat Sirat, Kampung Tanjung, Batu Lima Sik,
Gajah Puteh, Kampung Baru Kejai, Kampung Lalang, Malau, Pekan Pulai, Labu
Besar, Sungai Karangan, Pekan Junjong, Pekan Padang Meha, Pekan Relau,
Selama, Bukit Jenun, Kubur Panjang, Kampung Baru, and Teroi (v-swiss hits
for Teroi, Gajah Puteh, Tawar, and Pulai are all cross-district
namesakes). Spelling: UPI forms kept with common alternatives (Telok
Datai/Teluk Datai, Lubok/Lubuk Merbau, Gajah Puteh/Putih). Methodology
notes: the "10 BANDAR BAHARU" table-header echo is not an entity (the
town is code-40 Bandar Bandar Baharu); Pekan Kebun 500 is gazetted 27 Dec
2018 (PW 2665).

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

Book-to-row completion (Oct 2026): the Sept audit diffed rows against the
book but never the book against the rows, so 81 UPI entities were missing.
Diffed the UPI 2023 book for Kelantan plus the JUPEM UPI MapServer Admin3
layer (independent GIS witness) against the CSV in both directions. Added
77 mukims — 62 in Kota Bharu (89 total, incl. Ketereh Barat/Timor, Mukim
Pasir Mas 02/60, Pulau, Duson Rendah, Che Latiff), 7 in Bachok, Apa-Apa +
Kuala Kelar in Pasir Mas, 3 in Pasir Puteh, Wakaf
Delima in Tumpat, and Balar + Sigar completing Lojing's 7 — plus 4 pekan
rows: Pekan Rantau Panjang, Pekan Temangan, Pekan Selising, and Jelawat.
Retyped the six lone `Bandar X` mukims (Bachok, Tumpat, Pasir Puteh, Kuala
Krai, Machang, Gua Musang) to bandar; type-swapped Tanah Merah (bare row
is the UPI mukim, prefixed row the bandar); removed the `Bandar Pasir Mas`
mukim duplicate (10 town primaries move to the bare bandar, 3 redundant
secondaries dropped). Town-code primaries moved to the new pekans: 17200,
18400, 16810, and 16070 to Jelawat (8 addressed sightings, incl. a federal
gazette listing "16070 Jelawat"). New mukims are linkless (no postcode
evidence); Jeli verified clean at 7 mukims with no bandar.

Naming rules applied here for same-stem UPI pairs: mukim `X` + bandar
`Bandar X` (Johor Kluang precedent), mukim `X` + pekan `Pekan X`. Kept
judgment calls: `Kampung Laut`/`Kampung Wakaf`/`Kampung Sireh` keep the
common Kampung spelling with the UPI Kampong form as alternative;
`Lundang` kept over the book's doubled "Lundang Lundang" (GIS and upstream
agree on the single form); `Kuala Stong` kept despite the book's 1985
"diganti dengan Dabong" note (current land titles still read Kuala Stong).

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

Book-to-row completion (Oct 2026): a JUPEM UPI 2024 book for Negeri
Sembilan surfaced after the Sept audit, superseding its "no book"
methodology note — diffed it in both directions (no GIS witness; town
codes from a bulk postcode dataset with post-office towns, corroborated by
addressed sightings). Added 85 rows: 10 bandars (Bandar Kuala Klawang,
Bandar Port Dickson, Teluk Kemang, Bandar Seremban Utama, Bandar Mantin
Utama, Bandar Baru Kota Sri Mas, Bandar Nilai Utama, Bandar Sri Sendayan,
Bandar Gemas, Serting) and 75 pekans. Retyped Chengkau from pekan to
mukim (UPI lists mukim 04 plus pekan 76 — the inverted-row counterpart to
the Kelantan Tanah Merah swap) and the Bandar Seremban / Bandar Seri
Jempol town mukims to bandar in place (70 + 3 codes follow). All 7
districts now match UPI counts exactly (16/23/21/28/41/18/15; Seremban 41
counts the book's double-listed Bandar Seremban once).

Town-code primaries moved to the new rows: the 71600-set to Bandar Kuala
Klawang, 73100/73109 to Pekan Johol, the 71000-set to Bandar Port Dickson,
71150/71159 to Pekan Linggi, the 71900-set to Pekan Labu, the 71100-set to
Pekan Rantau, the 73400-set to Bandar Gemas, and the 73500-set to Pekan
Rompin, each with a covering-mukim secondary. Locked-bag codes follow the
post-office town, not the named subscriber (71659 Titi bag stays with
Kuala Klawang, 71409 Pedas bag stays with Rembau, 71109 Siliau bag stays
with Rantau). Shared-code secondaries on the new rows: 71650 Titi and
Sungai Muntoh (+71659 Titi bag), 71600 Petaling, 73100 Dangi and Air
Mawang, 72000 Senaling/Melang/Parit Tinggi/Juasseh, 72500 Juasseh, 71550
Gunung Pasir, 72200 Bukit Gelugor and Serting Tengah, 71050 Teluk Kemang
and Sungai Menyala, 71150 Pengkalan Kempas and Lubok China, 71960 Chuah,
71000 Bagan Pinang, 71100 Jemima, 71250 Pasir Panjang, 71400 Pedas/
Chembong/Merbau Sembilan (+71409 Pedas bag), 71300 Chembong and Chengkau,
71350 Chengkau and Seri Kendong, 71800 Baru Kota Sri Mas and Nilai Utama,
71950 Sri Sendayan, 71750 Lenggeng/Broga/Ulu Beranang, 71700 Setul and
Pajam, 70300 Mambau/Rasah Jaya, 71900 Tiroi, 70400 Paroi/Bukti/Sikamat/
Shah Bandar/Paroi Jaya, 70200 Bukit Kepayang and Ulu Temiang, 70100 Dusun
Setia, 71450 Sungai Gadut, 70450 Seremban Jaya, 73000 Tampin Tengah and
Repah, 73200 Air Kuning and Gemencheh Bahru, 73300 Batang Melaka, 73400
Pasir Besar, 72100 Kuala Jelai and Mahsan, 72120 Ladang Geddes and
Serting. Linkless: the lower-tier twin rows (Pekan Kuala Klawang, Pekan
Port Dickson, Pekan Teluk Kemang, Pekan Rembau, Pekan Bahau — codes stay
on the bandar), Dangi Baru, Bukit Pelanduk, PD Air Kuning, Tanah Merah
Utara/Selatan, Kampong Batu, Seri Kota, Seremban Utama, Mantin Utama,
Pancor, Taman Seremban (taman sprawl across 70100/70300/70450, no single
town code), Rahang Baru, Air Kuning Selatan (split 73200/73300 evidence),
Repah Jaya, Repah Permai, and Pekan Pertang. Spelling: gazetted Sepri and
Serting Hulu stand over the book's SPRI/ULU (alternatives already
stored); new rows keep UPI Kampong Batu and Gemencheh Bahru with Kampung
Baru-form alternatives; Tampin's Batang Melaka and Jasin's Batang Malaka
each keep their own book's form.

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

## Sabah below-district rectification

All 154 level-4 rows were re-verified one by one against the SPR
polling-district rolls for P167-P191 (21 Nov 2025 gazette) plus DUN pages,
the Paitan/Kalabakan/Sugut administrative record, four postcode directories
(v-swiss, all.json, J&T, City-Link), poskod.com entries, council and
department notices (MD Kota Marudu, Sabah JANS/JPP, MOH PeKa B40), Sabah
gazettes, and addressed shop/clinic/school evidence. Only about one in
twelve rows was a genuine below-district administrative unit; the rest were
towns (kept as `locality`), electoral names, villages, islands, estates, or
placeholders.

New `daerah_kecil` type (6 rows, all level 4 under their district,
administrative-only with no postal links): Tamparuli (Tuaran), Menumbok
(Kuala Penyu), Banggi and Matunggong (Kudat), Kemabong (Tenom), Pagalungan
(Nabawan). This is deliberately not the peninsular `minor_district` type:
peninsular minor districts sit at the district tier (level 2 under the
state, e.g. Genting, Lojing) and a district-tier town row may hold a
primary (Muadzam Shah holds 26700), while Sabah daerah kecil sit at the
subdivision tier (level 4 under the district) and their towns are separate
`locality` rows. Sabah's subdivision tier now renders `Daerah Kecil`; the
district and subdivision static fallback labels list it.

Name-sharing splits: Tamparuli and Menumbok keep the bare name as
`daerah_kecil` while new Pekan Tamparuli (89250-set) and Pekan Menumbok
(89760-set) localities take the primaries; Matunggong splits the same way
with Pekan Matunggong taking the 89050 secondary. The bare Penampang
primaries (89500-set) moved to Pekan Donggongon and the bare row was
removed; the Membakut district-held primaries (89720-set) moved to the new
Pekan Membakut locality. Bare Kota Kinabalu is the city itself, so it
retyped to `locality` in place keeping all primaries.

Added localities (14): Pekan Tamparuli, Pekan Menumbok, Pekan Membakut,
Pekan Sook (89000 shared), Pekan Paitan (90107 shared), Pekan Kalabakan
(91000 shared), Pekan Nabalu (89150 shared; the tourist town — not Kampung
Nabalu, Ranau), Pekan Lok Kawi (Papar, 89600 shared), Bandau and Langkon
(Kota Marudu, 89100/89050 shared), Pekan Matunggong, Pekan Sikuati, Pekan
Karakit (Kudat, 89050 shared), and Merotai Besar (Tawau, 91000 shared; the
pekan is Merotai Besar, so the DUN-named Merotai row was removed).
Retyped to `locality` (49): every verified town, including the
shared-code small towns Kiulu (89250), Kimanis, Kinarut, Benoni, Lok Kawi
(89600), Sindumin and Mesapol (89850), Melalap (89900), Apin-Apin (89000),
Sepulot and Pensiangan (89950), Tungku (91100), and Weston (89800).

Removed 98 rows: bare-X district-town duplicates (23, all secondaries
duplicative of the town primaries, including bare Kalabakan which Pekan
Kalabakan replaces); DUN/polling-district/kampung names with no town and
no code (Klias, Lumadan in both districts, Bingkor, Liawan, Sukau, Lamag,
Elopura, Tanjong Papat, Sri Tanjung, Balung, Segama, Madai, and the like);
islands (Bum-Bum, Jambongan-as-village); estates and scheme areas (Lumadan,
Kerukan); city suburbs with no own code (Luyang, Sembulan, Kepayan); the
synonym-duplicate Pekan Kinabatangan; and wrong-district duplicates (Pitas
and Tandek under Kudat, Lumadan under Sipitang).

District corrections: Pamol (own code 90400, SMK Pamol) moved Beluran to
Paitan with its primary (Sugut-DUN + Jalan Pamol tender evidence); Pekan
Pitas takes the 89100 secondary (three addressed town usages beat the
directory's 89050 area entries); Tandek (Kota Marudu) takes the 89100
secondary (Oct-2026 retry: MOH addressed "PEKAN TANDEK 89100" + school
PO-box zone + directory 89100 beat the directory-only 89050 with zero
addressed usage — same addressed-beats-directory rule as the Pitas call).

Reinstated 4 Kota Marudu office codes with their links (Oct 2026):
89130 Pingan Pingan delivery plus 89137-89139 PO-box/window/lockbag,
each with its own postcode.my locality page, all in a Pos-live cell.
They were briefly dropped as phantoms (absent from the rectification
directories), but the coherent four-type office set plus exact
directory pages satisfies the concrete-proof standard. The single
89137-for-Kiau sighting stays discounted as a probable 89150 typo
(zone-incoherent). Not phantoms: Telupid's 89327-89329 (poskod.com PO boxes) and Tongod's
89330-set (MOH "Pos Mini Tongod, Pekan Tongod 89330"), kept although
unlisted in the courier directories. 90108/91208 were verified nonexistent
(Pos Malaysia skips them), so the Beluran/Kunak sets stay as they are.

Residual uncertainties: Langkon keeps its 89050 secondary on hold
(directory-unanimous for town delivery vs KM school-box zone, zero
addressed either way — one addressed sighting flips or seals it);
Pekan Paitan (90107) and Pekan Sook (89000) are medium (town+zone
proven, direct addressed string still wanted); Pekan Karakit (89050)
medium-high (optional seal). Menggatal 88400 / Kinarut 89500 leads
rejected as single-MOH outliers (revisit only on 2+ new signals).

Revisit 2026-10-06 (open-log #2 retry: Tandek 89050→89100 move +
28 secondary-only towns swept; `gate_my.py` ALL PASS): Melalap,
Sepulot, and Benoni upgraded weak → PROVEN (postcode.my + OSM +
school-zone/addressed usage); Tulid no-row vindicated as a kampung
(5 signals); all 28 Sabah secondary-only towns CONFIRMED shared
except Tandek (moved) and Langkon (held). Office-code discipline
held: Kundasang 89308, Sindumin 89859, and Tungku 91122 rejected as
counter-ticket / locked-bag / PO-box-range codes.

## Sarawak audit

Sarawak has no mukim tier. The earlier below-district audit below is
superseded by the rectification record that follows it: the old gazetted
list mixed real daerah kecil with a district (Padawan), polling and DUN
areas (Igan, Nanga Merit, Balai Ringin, Lapok), a resettlement scheme
(Sungai Asap), a national park (Mulu), and a longhouse bazaar without
gazette evidence (Long Bedian), and it misplaced Bario under Miri district
instead of Marudi. Moved 9 rows: Tapah to Siburan,
Moyan and Tambirat to Asajaya, Triso to Pusa, Roban to Kabong, Nanga Medamit
to Limbang, Belawai to Tanjung Manis, Paloh to Daro, and Niah to Subis.
Removed 13 rows: stale post-split duplicates (Balingian, Bekenu, Long Lama,
Lingga, Kabong, Sebauh, Tatau, Entabai), the Kuala Balingian duplicate, the
Pusat Mel Miri mail centre (98070 moves to Miri), the Baram region name
(Marudi postcodes move to Marudi), the Poyut/Nibong conflated name, and the
Sebelak river name. Renamed Budu to the official Nanga Budu form.

## Sarawak below-district rectification

All 138 level-4 rows were re-verified one by one against the SPR
polling-district rolls for P192-P222 plus DUN pages, three postcode
directories (v-swiss layout PDF, all.json, postcode.info), the Sarawak
Government Gazette via Sinar Project, council records (Majlis Daerah
Subis, Dalat & Mukah, Marudi, Bau), school/church/shop/clinic addressed
evidence, and the MS Wikipedia daerah-kecil articles with their district
maps. Seventeen rows are genuine gazetted daerah kecil; the rest were
towns (kept as `locality`), electoral names, villages, longhouses,
islands, parks, suburbs, or placeholders.

New `daerah_kecil` rows (18, all level 4 under their district,
administrative-only with no postal links): Debak and Spaoh (Betong),
Roban (Kabong), Maludam (Pusa), Nanga Budu (Saratok), Sematan (Lundu),
Sadong Jaya (Asajaya), Sundar and Trusan (Lawas), Nanga Medamit
(Limbang), Long Lama (Telang Usan), Oya (Dalat), Balingian (Mukah),
Engkilili (Lubok Antu), Bario (Marudi), Sibuti and Niah-Suai (Subis),
and Padawan (Kuching — rejoined in the Oct-2026 L3-program pass, see
the Revisit below).
Proof highlights: MS daerah-kecil articles with district maps (Debak,
Roban, Sadong Jaya, Sematan, Nanga Budu, Nanga Medamit, Oya, Sundar,
Trusan); gazetted sub-district offices (Balingian 2004-2021, Engkilili
2016/2018, Long Lama 2007-2020, Bario 2019/2020); the Majlis Daerah
Subis PDF (Sibuti + Niah-Suai); the UKM Daerah Kecil Spaoh study; the
Maludam article (Pejabat Daerah Kecil Maludam under Pusa). Not daerah
kecil: Song (gazette shows Pejabat Daerah Song — a district office),
Beluru (the old Beluru sub-district office predates the Beluru district
upgrade), Ba'kelalan (nine-village highland cluster, removed), and
Batang Ai (dam/lake area, no row). Padawan was wrongly listed here as
"a district" — the Oct-2026 L3-program pass rebutted that (state
portal sub-district column + Kuching division DK office + 11-Aug-1983
gazette history), so it rejoined as the 18th DK row instead.

Name-sharing splits (13): each keeps the bare name as `daerah_kecil`
while a new Pekan locality takes the postal links — Pekan Debak
(95500), Pekan Spaoh (95600), Pekan Roban (95300), Pekan Sundar (98800),
Pekan Trusan (98850 shared; gazette "Pekan Trusan, 98850 Lawas" plus
SJK(C) Chung Hua Trusan), Pekan Nanga Medamit (98750), Pekan Oya
(96410 shared; council "Pekan Kecil Oya"), Pekan Balingian (96350),
Pekan Engkilili (95800; "BAZAAR ENGKILILI" petrol-station address),
Pekan Long Lama (98300), Pekan Bario (98060 own code plus 98050
shared), Pekan Sematan (94500 shared), and Pekan Sadong Jaya (94600
shared; bank "Pekan Sadong Jaya" and addressed shop usage). The
district-held primaries moved to new bare-form capital towns: 94950 to
Pusa, 97100 to Sebauh, 97200 to Tatau. Bare-form towns with own codes
retyped in place: Marudi (98050-set), Song (96850), Belaga (96900-set),
Bekenu (98150-set), Belawai (96150), Daro, Matu, Simunjan, Sebuyau,
Lingga, Lubok Antu, Niah (98200), and Lutong (98100-set, the Miri
township with single-entry directory confirmation).

Added secondaries on proven shared-code towns: Selangau (96000; "Pekan
Selangau" Pan-Borneo town), Tebedu (94760;
v-swiss "Tebedu" entry plus border town), Beluru (98000; SJK(C) Hua
Kwong at "Pekan Beluru, Bakong" — plus a 98050 dual leg since the
Oct-2026 retry: gazette + Sarawak-gov SKAS + school, mirroring the
Bario dual), Pantu (95000; district since 2021
per MS Daerah Pantu, admin centre Pekan Pantu), and Batu Niah (98200;
SJK(C) Chee Mung Niah). Pakan owns the 96510 primary since the
Oct-2026 retry (gazette x2 + bank x2 + school; bundled Bintangor
primary had zero directory support) and lost its directory-only 96100
secondary (unlocatable Petron snippet, zero addressed town usage).
Siniawan (94000) and Sematan-town (94500) keep
their shared links as notable historic/beach towns; Sibu Jaya keeps
96010 (all.json plus addressed Waze/foodpanda usage against two
directory gaps).

Removed 79 rows: longhouse and kampung pollings (Long Teru, Long
Jegan, Long Semado, Merapok, Lio Matu, Long Akah, Long Bedian, Long
San, Padeh, Sepupok, Triso, Majau, Wuak, Arip); Nanga confluence
names (Merit, Engkuah, Entabai, Dap, Tada); DUN-area names (Machan,
Tamin, Meluan, Repok, Kemena, Muara Tuang, Jemoreng, Katibas, Batu
Danau); river, island, park, and scheme geography (Batang Igan,
Skrang, Pelagus rapids, Pulau Babi, Mulu, Lambir, Sungai Asap, Long
Murum); city/industrial suburbs and resort areas (Batu Kawa, Matang,
Semariang, Santubong, Jepak, Kidurong, Bakam, Sungai Merah, Jakar);
municipal and port areas (Tanjung Manis — Padawan rejoined as a DK
row in the Oct-2026 L3-program pass); villages without town evidence (Buso, Krokong,
Tondong, Musi, Biawak, Nyabor, Lapok, Kemuyang, Pasai Siong, Paloh,
Semah, Serdeng, Igan, Moyan, Semera, Tambirat, Rangawan, Terasi,
Nyelong, Tulai, Balai Ringin, Tapah, Amo, Lemanak, Batu Lintang,
Undop, Kubong, Lidong); Suai (river/longhouse area inside Niah-Suai,
no town proof); Tebakang (kampung cluster, no pekan proof); Ba'kelalan
(village cluster); Gedong (kampung-only directory entries, no postal
town); and the jetty place-name Pangkalan Tebang.

Corrections: 98060 moved Marudi to Pekan Bario (eight "Bario, 98060"
Tripadvisor addresses; the directories carry only 98050 Baram for the
highlands). Reinstated 94111 on the Lundu town row (Oct 2026): it is Tanjung
Datu's special postcode, issued to the Tanjung Datu Lighthouse with
Malaysia Book of Records recognition (Sarawak Tribune, Nov 2024), so
it resolves on Lundu although the directories omit it. The single
94100 hotel-listing sighting still reads as a 94500 typo. Not phantoms: 96010 Sibu Jaya (kept
per the S6-positive rule plus addressed usage) and 97300 Tanjung
Kidurong (stays on the Bintulu city row as a suburb code).

Residual uncertainties: Bario's 98050 secondary alongside its
98060 primary reflects dual real-world addressing. The L3 tier is
complete at 45/45 districts vs the state portal table (Oct-2026
L3-program pass); the "Kota Samarahan" district name vs the state's
"Samarahan" is a pre-existing naming note, kept as is.

Revisit 2026-10-06 (open-log #2 retry: 96510→Pakan move, 96100 Pakan
drop, Beluru 98050 dual, Lachau + Sungai Tenggang rows; `gate_my.py`
ALL PASS): the 1873 "daerah kecil Song" was UPGRADED to a full
district on 2 Apr 1973, so Song town-row-only stands closed as a
non-entity; Lachau and Sungai Tenggang gained `locality` rows +
95000 secondaries (gazette + SKAS-gov Pasar/bazaar + directory +
OSM + MS pekan status); all 11 Sarawak secondary-only towns
CONFIRMED shared except Pakan (moved) and Beluru (dual-added).
Sematan 94100 rejected as one stale lineage (zero directory support;
MOH corrected itself to 94500).

Revisit 2026-10-06 (open-log #3 L3 program: zero L3 adds + Padawan L4
DK re-add; `gate_my.py` ALL PASS): the bundled L3 tier already matches
the official state district list 45/45 (names + division parents), so
no Padawan district row was added — the "Padawan is a district"
premise is rebutted (state portal lists it under Sub-Districts;
Kuching division runs Pejabat Daerah Kecil Padawan; gazetted as a
sub-district 11 Aug 1983; full-district status still a proposal per
2023–2024 press). Padawan rejoined as L4 `daerah_kecil` under Kuching
instead (admin-only, no postal links). Unblock for any future flip: a
Sarawak Gazette district proclamation — revisit only on 2 new
positive signals.

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

Re-verified Oct 2026 against the URA-sourced district/sector table and the
URA-cited planning-area list: all 81 links, all 55 names with parents, and
the five CDC states match with zero changes. (Cross-check note: the
Regions of Singapore overview table misstates North-East as 9 areas; the
planning-area list itself confirms 7.)

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

There is no postal hierarchy and no `refinedBy`: the single
administrative chain province → regency/city → district → village already
scopes every level, and villages are administrative rows rather than postal
localities. The 9,361-code postal overlay links each code to exactly one
regency/city (all 514 covered); see `18-postal-overlays.md` for the
methodology. Residual gaps are only unassigned ranges (18xxx, 47xxx–49xxx,
88xxx) plus deliberately dropped rows: 29 Timor-Leste (`dili`) codes and
38 typo-dupe/stale rows (transposed or mistyped codes such as
98011 for Soppeng 90811, whose correct forms are covered independently).

Revisit record: all 38 province codes verified against ISO 3166-2:ID, every
province's regency/city split reconciled (416 + 98), and all 91,599 rows
checked for dangling parents and Kemendagri code shape with zero violations.
Known lag: BPS counts 7,288 districts (2025) and 84,048 villages (2024)
against the bundled 7,285 and 83,762 — upstream `lokabisa-oss/region-id`
has no release newer than v1.0.1, so refresh when it does rather than
hand-patching rows.

October 2026 re-verification: the full 91,599-row tree was diffed against
Kepmendagri 300.2.2-2138/2025 with zero code differences and 107 name
corrections applied (upstream parse artefacts: neighbour/province tokens
appended to 32 regency/city names, four truncated names restored, spacing
and punctuation fixes). Deliberate deviations kept: `DKI Jakarta` / `DI
Yogyakarta` (state-matched, documented above), Jakarta municipalities
without the `Administrasi` qualifier (display convention shared with
upstream), and `Kepulauan Siau Tagulandang Biaro` spelled out (the decree
abbreviates `Kep.`). The postal overlay was re-verified code by code
against GeoNames place names resolved through the village tree plus
coordinates and postal references: 1,912 links corrected (34%), fixing
systematic block rotations in Jakarta (all five municipalities cycled),
North Sumatra, West Sumatra, Aceh, NTT, Southeast Sulawesi, South
Sulawesi, Lampung, Kalimantan blocks, Maluku, North Maluku, and Papua,
plus cross-province misfiles (323xx OKU Timur filed under Kalsel, 983xx
Manokwari-raya filed under Yapen, 996xx south-Papua filed under Nabire).
GeoNames admin2 codes proved unreliable (same rotations; stale pre-split
Papua and invented 94xx codes) and were used only for place names and
coordinates, never for verdicts. Follow-up gap-fill from the Pos Indonesia
postcode book (prangko.nl mirror: 7 regional + 8 city volumes) crossed
with GeoNames added 3,613 codes with the same place-vote adjudication,
closing Surabaya, Semarang, Medan-core, Makassar-core, Malang-core,
Surakarta-core and all other real-range gaps (Ambon verified complete and 18 more Semarang-city codes added via worldpostalcode town pages); coverage is now 514 of 514
regencies/cities.

Indonesian addresses are formatted as street lines, `kelurahan`/`desa` and
`kecamatan` components, `{kota} {postcode}`, province, and country, per the
UPU addressing layout.

Revisit 2026-10-06 (open-log #1 retry: 6 relinks + 2 drops, 9 proven,
3 held; `gate_id.py` ALL PASS): the official Pos Indonesia directory
is directly queryable (POST `kodepos.posindonesia.co.id/CariKodepos`,
field `kodepos=`) and settled most items. Structural finding: the
GeoNames-family directories (GN dump, kodepos.id, kode-pos.com,
indonesiapostcode, mamikos, prangko book) are an OLD vintage — whole
districts renumbered since (official-affirmed codes treated as alive;
official-silent codes with unanimous old-vintage L2 kept with a
stale-suspect flag). Relinks: 99674 Mappi→Boven Digoel, 20524/20525
Deli Serdang→Medan, 92661 Bulukumba→Sinjai (Ujungloe is 92562 now),
98865 Paniai→Dogiyai, 84111 Bima regency→Kota Bima. Drops: 52191 (WPC
page error; Sumurpanggang is 52141 Kota) and 34663 (zero-source code).
Proven: 26235 Payakumbuh, 23612 Aceh Barat, 42114 Kota Serang, 42181
Kab Serang, 46371 Pangandaran, 97611 Kota Tual, 74714/74715 Sukamara,
93915 Kolaka Utara. Held: 91954 Lutra, 46166 Kota Tasik, 87256 Sumba
Barat. 9,359 codes; follow-up vintage program (official-new codes
absent from the overlay) recorded in the retry report.

## Brunei

The bundled `BruneiGeographyProvider` supplies the four ISO 3166-2
districts as `State` rows and one administrative hierarchy: 4 districts →
39 mukims (18 in Brunei-Muara, 8 each in Belait and Tutong, 5 in
Temburong). It is selected with
`SeedCountryGeographiesAction::execute('BN')` after countries are seeded.

Mukim names follow Brunei government spellings (`Pengkalan Batu`, with
`Pangkalan Batu` aliased). Bruneian postcodes are six characters — two
uppercase letters (district, then mukim) followed by four digits.

Verification (Oct 2026): the 39 mukim names match the published mukim
list exactly, and all 394 bundled postcodes were checked code by code
against Brunei Post finder data (post.gov.bn extract): every code exists
with the matching mukim, each linking exactly once as primary. The two
shared prefixes are genuine — `BE` spans Gadong A/B by kampung and `BK`
spans Sungai Kebun/Kedayan (which also holds `BN`). The 34 finder codes
not bundled are all agency or locked-bag codes (ministries, Hospital
Ripas, UBD, `BS8670`–`BS8675` Peti Surat ranges, Shell, post-office
private bags) — deliberately excluded; the bundle covers kampung
delivery codes only. Finder-only spellings (`Burong Pinggai Ayer`,
`Kampong Peramu`) are kept as alternative names. Residual: the finder
extract notes a few kampungs returned no data, so those kampungs have no
code here either.

Brunei addresses are formatted per the UPU layout: street lines, kampung
component, `{town or district} {postcode}` with the town preferred, and
country. Types are labelled `Daerah` and `Mukim`.

Revisit 2026-10-06 (verify-only, zero data changes; `gate_bn.py` ALL
PASS + new hold pins): the 20 finder-empty kampungs were retried
against Buku Poskod Edisi ke 2 (Dec-2018, both 2018 snapshots) with
the Mapanet family (56ok/postcodebase/postcode.info/postalcoder —
one shared 2013–2015 lineage with phantom rows, corroboration-only)
as second signal. 17/21 gap verdicts PROVEN with all codes already
bundled (incl. Lumapas B BJ3524 and Amo PD1151); residual holds are
Sungai Kedayan A/B (bundled BK1711/BK1511 vs family BN1711/BN1511,
kept as operator-sourced incumbents — flip needs live-finder proof)
and Amo B/C (family-only PD1351/PD1551, no fill). Adjacent find:
Belaban is bundled finder PD2451 but Buku + family unanimous
PD3151 (open-log #22, pinned pending adjudication).

## Bahrain

The bundled `BahrainGeographyProvider` supplies the four ISO 3166-2
governorates as `State` rows (codes `13`, `14`, `15`, `17` — there is no
`16` since the Central Governorate was abolished in 2014) and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BH')` after countries are seeded.

Bahraini addresses are formatted per the UPU layout: street lines,
`{municipality} {postcode}` with a 3–4 digit postcode, and country.

Revisit 2026-10-03 (verify-only, zero data changes; `gate_bh.py` ALL
PASS): block number = postcode per the UPU BHR profile (3–4 digits,
valid range 1XX–12XX; bundled 101–1218; first-two-digits region rule
applies to 4-digit codes). Anchors: UPU AL-MANAMAH 317 → Capital and
RIFFA 926 → Southern; addressed Sanabis 408 → Capital, Riffa 915 →
Southern, Nasfa 733 → Capital, Sanad 743 → Capital (Works Ministry
project pages). Per-governorate counts: Capital 121, Muharraq 74,
Northern 156, Southern 128. A fresh Mapanet pull agrees on 106/106
certain-town blocks with zero disagreements (Capital 14, Muharraq 59,
Northern 32, Southern 1). The A'ali area genuinely spans three
governorates (Northern 732/734/736/738/740/742/744, Capital
733/743/745, Southern 746/748). Gaps: 573 (single Mapanet Janabiyah
row) stays uncovered, not filled. Weak: 479 vs SLRB 478 off-by-one
unresolved — no SLRB block list obtainable (data.gov.bh stats only).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_qa.py` ALL
PASS): tree re-verified clean against ISO 3166-2:QA and the
2015-census zone-reservation table (90/90 zones exact, gaps exact);
codeless scope triple-confirmed (UPU qatEn 08/2024, Sep-2025
do-not-require list, no GeoNames QA dump).

## Kuwait

The bundled `KuwaitGeographyProvider` supplies the six ISO 3166-2
governorates as `State` rows with 135 postal areas as level-2 areas
(32 Capital, 29 Ahmadi, 24 Jahra, 20 Farwaniya, 17 Hawalli, 13 Mubarak
Al-Kabeer) in a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KW')` after countries are seeded.
Uninhabited islands (Miskan, Ouha, Umm an Namil, Bubiyan, Warbah) are excluded;
blocks and per-area postcodes stay out of scope, and governorate/area
name twins (Farwaniya, Ahmadi, Jahra, Hawalli, Mubarak Al-Kabeer) share
names by design; filter by type.

Kuwaiti addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country. The `governorate` type is labelled `Muhafaza`.

Revisit 2026-10-03 (verify-only, zero data changes; `gate_kw.py` ALL
PASS): all 6 governorates match ISO 3166-2:KW names and codes,
and the 135 areas reconcile against the Areas-of-Kuwait oracle
(32 Capital, 29 Ahmadi, 24 Jahra, 20 Farwaniya, 17 Hawalli, 13
Mubarak Al-Kabeer): the oracle's 140 rows are the 135 bundled
areas plus 5 uninhabited islands excluded here (Miskan, Ouha,
Umm an Namil, Bubiyan, Warbah) plus the Sabah Al-Salem
University campus and Sulaibiya Industrial rows not shipped as
areas, minus the Kuwait City capital area and Al-Shadadiya
which the oracle table omits. Remaining diffs are
transliteration variants kept as-shipped (Abdulla Al-Salem,
Bnaid Al-Qar, Hawalli, Mirqab, Qortuba, Al-Riggai,
Al-Fnaitees, Messila). Postcode scope is a live-system gap,
not codeless: the UPU require-list, the kwtEn profile
(07/2002), and the List of postal codes document a live 5-digit
sector/post-office system whose block codes nest under areas
(MOC Governorate/Area/Block table headers, Mapanet Al Dasma
block-range rows), but no allocation source is usable (MOC
tables render empty, PACI unreachable, no GeoNames KW dump,
Mapanet rows unattributable at L2), so no overlay ships.

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_jo.py` ALL
PASS): 12 governorates match ISO 3166-2:JO codes exactly, and the 51
liwa still match the DOS Yearbook counts per governorate. The bundled
351-code / 352-link overlay is unchanged: one dual (11121 Wadi Essier
primary + Jami'ah secondary), 48 liwa with primaries, and Kufranjah,
Bsaira, and Shoonah Janoobiyah codeless in every source. The ten
border-adjudication anchors (71910 Shobak, 61258 Sahab, 64710 Hasa,
25710 Mafraq Qasabah, 11190 Amman Qasabah, 11134 Marka, 11152/61256
Quaismeh, 71221 Ajloun, 71228 Jerash) all still resolve.

## Oman

The bundled `OmanGeographyProvider` supplies the eleven ISO 3166-2
governorates as `State` rows and a two-level administrative hierarchy
(governorate → 63 wilayats). It is selected with
`SeedCountryGeographiesAction::execute('OM')` after countries are seeded.

Omani addresses are formatted per the UPU layout: street lines, a
3-digit postcode on its own line above the locality, and country.
The `governorate` type is labelled `Muhafaza`.

Revisit 2026-10-03 (M3 fill: new 99-code / 99-link overlay;
`gate_om.py` ALL PASS): tree verified clean — 11 governorates match
ISO 3166-2:OM codes exactly and all 63 wilayat parents match the
Provinces-of-Oman oracle section by section, including the
post-2022 Jebel Akhdar (Dakhiliyah) and Sinaw (Sharqiyah North)
wilayats and the 2006 Buraimi split (Sunaynah/Mahdah under Buraimi,
Dhank/Yanqul staying in Dhahirah). New overlay at wilayat grain:
the Parcelforce Oct-19 posting guide, the live youbianku directory,
and an ntdtvjp mirror agree on the office table (the lone YBK
423-dupe on Dama Wattaeen loses to the PF+NTD 423/424 split);
offices resolve to wilayats via census locality tables, wiki
wilayat/village articles, OSM wilayat-boundary containment, office
street addresses, and ROP districting, with UPU OMN anchors (112
Ruwi, 133 Al-Khuwayr, 311 Sohar). All 99 codes are single-primary;
five wilayats are codeless in every directory (Duqm, Mahout,
Al-Mazyona, Shalim, Wadi Al Maawil). Weak keeps: 127 Wattayah
(adjudicated Muttrah over the OSM Bawshar polygon — needs a Muscat
Municipality district list), 129 Murtafaa (OSM-only, Seeb), 213
Taitam (road+adjacency, Salalah). Excluded as unverified singles:
the addressed-only general 100, 138 Al Mouj, and zng x00 rows; the
zng branch-ID table (Mutrah 169 class) is locator branch numbers,
not postcodes. Scope-table row flips expansion→complete.

## United Arab Emirates

The bundled `UnitedArabEmiratesGeographyProvider` supplies the seven
emirates as `State` rows and a single-level administrative hierarchy.
It is selected with `SeedCountryGeographiesAction::execute('AE')`
after countries are seeded.

The UAE has no postcode system — delivery is to P.O. Boxes only — so
the formatter stacks street lines, city, and country with no postcode
line.

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ae.py` ALL
PASS): 7 emirates match ISO 3166-2:AE codes and names exactly. The
UPU ARE profile (09/2014) states deliveries are P.O.-Boxes-only, and
the UPU Sep-2025 do-not-require list carries the UAE. Known trap:
the GeoNames AE postal dump holds 178,171 Makani geocode rows
(5+5 digits), zero real postcodes — never import it as postcodes.

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

Revisit 2026-10-03 (fix: 86365/86366/86369 Madinah→Jizan;
`gate_sa.py` ALL PASS): tree re-verified clean — 13 ISO 3166-2
regions (01–12 + 14, no 13) and all 139 governorate names +
parents match the Wikipedia Governorates list exactly. All 51
digit-off-grain codes re-checked via Mapanet zone filings +
OSM reverse-geocode: the Makkah adjudications hold (Mapanet
has no Ardiyat/Adum/Muwayh/Maysan zones, so it files those
Makkah governorates under Bahah/Asir zones — the moves were
corrections), as do the Bahah, Dawadmi/Riyadh, Qassim
(58276/Dhariyah overrules a Mapanet Riyadh filing), Asir and
Jazan samples, with the 58459 Qassim-row duplication directly
reproduced. Keeps on ties: 56988/56994/58259/58279/58617/58627
Riyadh (OSM Qassim-side), 65379/65394/65487 Makkah (rows
straddle the Bahah boundary), 65996 Makkah, 28997 Asir,
89936 Asir, 17276 Qassim. The 8636x Samtah block was
mislinked Madinah: 86366 is Al Hijfar village in Samtah,
Jizan (current Mapanet filing + OSM + Wikipedia + 56ok);
86365/86369 follow by unanimous-block extension. Gaps: 11xxx
absence is correct (P.O.-Box space — the UPU SAU profile's
own POB example is 11564 — not a Wasel gap); Arar city
(~40 codes) plus ~19 Tarif/Rafha-city codes on current
Mapanet are still unshipped, as is most of Riyadh
city-center 12–14xxx (~990 codes incl. Olaya 12211–12214) —
all Missing-not-invalid, awaiting a full re-pull pass
(source grew 218,705 → 257,689 rows).

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

Revisit 2026-10-05 (10 INEC-2026 formal renames incl. slugs +
176 leg moves; zero code changes; `gate_ec.py` ALL PASS): tree
246/246 — 24 provinces ISO EC-A..Z exact, 222 cantons exact vs
the INEC Clasificador Geográfico 2026 DPA (fresh) + es.wiki
canton annex (223 rows incl. Borbón) modulo the 10 renames:
Quito -> Distrito Metropolitano de Quito (116 legs), Santo
Domingo de los Colorados -> Santo Domingo (33), Pelileo ->
San Pedro de Pelileo, Píllaro -> Santiago de Píllaro, Baños
-> Baños de Agua Santa, Yaguachi -> San Jacinto de Yaguachi,
Santiago de Méndez -> Santiago, Joya de los Sachas -> La
Joya de los Sachas, Pueblo Viejo -> Puebloviejo, Río Verde
-> Rioverde. Keeps: Veinticuatro de Mayo spelled out (INEC
digit style only), Alfredo Baquerizo Moreno (INEC parenthetical),
General Antonio Elizalde (INEC double-space typo). Postal:
bundle set == GN set exactly (1225/1225, 0 multis); 080701-03
La Concordia keeps the legacy Esmeraldas 08 prefix (transfer
2013; GN admin2 2302 Santo Domingo); all 4 zone-90 legs
re-confirmed (El Piedrero -> El Triunfo via UTA thesis +
citypopulation + 2017 decree; Manga del Cura -> El Carmen via
en.wiki + UNAL/ULEAM; Las Golondrinas -> Cotacachi via
citypopulation + SRI doc + 900004 topo map). Hold: Borbón
canton (Nov-2025 referendum only; INEC 2026 still parish
080253; no cantonization law found).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_eg.py` ALL
PASS): tree re-verified exact against ISO 3166-2:EG (27 codes) and the
authoritative HDX COD-AB workbook (CAPMAS 20170421): 365/365 admin2
rows — p-codes, names verbatim (Kesm/Markz prefixes, Suhag/Qina/
Zaqaziq transliteration, Luxor twins, police units), counts, and
parents, incl. the 14 genuine EGNN00 Zemam residuals. L1 uses
common-English display names with ISO codes (COD's own L1 spellings
are Sharkia/Kalyoubia/Behera/Menia/Assiut/Suhag/Fayoum); the "(Suhag,
Sharkia, Qina)" examples are district-level (L2) rows. Postcode scope:
live-system gap, NOT codeless — UPU require-list Aug-2026 carries
Egypt with dual 5- and 7-digit entries; the 07/2023 profile documents
7-digit PP/L/NN/CC below locality while addressed usage is still
5-digit (transition). No 2-signal allocation source exists (Egypt Post
finder Cloudflare-walled, geoportal dead, no GeoNames dump, Mapanet
thin fragment now unreachable, aggregators walled/stale), so no
overlay ships; stays `expansion` with a gated 7-digit-first proposal.

## South Africa

The bundled `SouthAfricaGeographyProvider` supplies the nine ISO
3166-2 provinces as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ZA')` after countries are seeded.
The 44 district and 8 metropolitan municipalities ship as level-2 areas under their provinces.

South African addresses are formatted per the UPU layout: street
lines, locality, a 4-digit postcode below it, and country. The
province line is omitted when a postcode is present, per the UPU rule.

Revisit 2026-10-03 (fix-and-fill: 101 primary retargets,
+33 secondaries, +11 codes → 3277/3318; `gate_za.py` ALL
PASS):

Oracles (independent pulls, this pass): SAPO postalcodes.txt
(live postoffice.co.za download, 15,373 rows, 3,984-code
union) + SAPO postalcodes.html mirror (github postcodes-za.csv,
byte-identical code set) — primary code oracle; street (StrCode)
vs box (BoxCode) columns adjudicated per kind, never invented
splits. GeoNames ZA.zip postal (3,920 rows / 3,266 unique —
exactly the bundled set) + ZA-geo gazetteer (103,212 rows) for
place/town geocoding. Blaauwberg live postcode finder scrape
(a-z, 16,735 rows, 3,921 valid codes) as second transcription;
triple-agree 3,245 codes. National Treasury
municipal-demarcation API (52 L2 set-identical) + Wikipedia
List_of_municipalities + ISO 3166-2:ZA (9 provinces) for the
tree. UPU ZAF profile example postcodes as anchors
(0083/7975/1852/5170/0305/1715 in-set; 1982 excluded, box-dup
of 1983). MDB 2024 bulletin (no roster; demarcation vintage
cross-checked for the 2016 Inxuba Yethemba DC10->DC13 and
Mbizana DC15->DC44 moves).

What changed and why: systematic error found — ~3% of primaries
inherited the POSTAL TOWN's municipality (Temba/Hammanskraal->TSH,
Marble Hall->DC47, Louis Trichardt->DC34,
Vryburg/Upington/Bloemfontein hubs) instead of the street-delivery
place's. This pass applies street-first attribution: StrCode
geography decides the primary; box-office towns become secondaries
where 2+ signals support them. Five bundled duals swap legs under
this rule (0418/0419 TSH->DC37 Mathibestad/Swartbooistad/Thulwe,
0472 DC47->DC31 Siyabuswa, 1609 JHB->EKU Edenvale, 1689 JHB->EKU
Tembisa). Gross mis-assignments fixed (postal-town or
name-collision artefacts), e.g. 1861 Naledi-Soweto DC35->JHB,
7583 Kuils River DC47->CPT, 4480 Darnall DC45->DC29, 0950
Thohoyandou DC35->DC34, 2920 Wasbank DC16->DC24, 9670 Bultfontein
DC31->DC18, 9992 Bethulie DC06->DC16, Taung block 8537-8599
DC09->DC39, Kranskop block 3269-3277 DC16->DC24, Dordrecht block
5341-5445 DC31->DC13. Stability holds (no move on tie / single
weak signal): 5900 Middelburg EC stays DC13 (2016-boundary
staleness in gazetteer rejected), 1693 keeps JHB primary + EKU
secondary, 2778/2779 keep DC38, 0351/0405 keep, 9323 keeps MAN
(Ga-Sehunelo gazetteer trap resolved to Mangaung via Turflaagte
PPL + web). Secondaries: all 7 bundled legs kept with 2+ signals
(none single-source); 33 new legs added (box-office splits like
3236 Dalton/Greytown, 7283 Klipdale/Klipfontein, 0415
Rantebeng/Dikebu; street minorities like 1632 Rabie Ridge->JHB,
4399 Ballito->DC29, 0626 Diepsloot->DC35). Total 41 duals.
Fills: 11 street-distinct SAPO+BB codes added (0180 TSH,
0321/0323/0359 DC37, 0880 DC35, 1682 JHB+EKU, 2310 DC30, 2539
DC40, 6445 DC10, 6750 DC03, 9423 DC18). 178 SAPO-only street
codes and 496 box-only codes excluded (single signal /
box-dups); 21 SAPO-missing bundled codes all kept
(GeoNames-backed). Tree: verify-only, no change (61 areas:
9 provinces + 8 metros + 44 districts; Treasury/WP/ISO agree).

Per-municipality primary counts (pre -> post; secs = post
secondaries): BUF 48->46 | CPT 169->170 | DC01 50->53 (2) |
DC02 59->60 | DC03 26->27 | DC04 67->66 | DC05 6->6 |
DC06 31->30 | DC07 26->25 (2) | DC08 28->27 | DC09 46->31 |
DC10 60->62 | DC12 59->61 (1) | DC13 56->62 | DC14 33->31 |
DC15 61->61 | DC16 24->21 | DC18 47->48 | DC19 41->42 (1) |
DC20 22->22 | DC21 51->51 | DC22 68->66 (1) | DC23 28->28 |
DC24 26->32 (1) | DC25 17->16 | DC26 30->31 (1) |
DC27 29->29 (1) | DC28 36->35 (1) | DC29 17->21 (2) |
DC30 58->56 | DC31 84->84 (3) | DC32 103->101 | DC33 68->68 |
DC34 62->64 (2) | DC35 143->142 (1) | DC36 102->101 |
DC37 115->123 (4) | DC38 73->71 | DC39 42->56 | DC40 46->46 |
DC42 49->50 (1) | DC43 14->16 | DC44 32->32 (1) | DC45 8->11 |
DC47 44->40 (3) | DC48 53->55 (1) | EKU 172->173 (4) |
ETH 183->182 (1) | JHB 230->231 (3) | MAN 43->41 | NMA 63->64 |
TSH 218->210 (4). Totals: 3266->3277 codes, 3273->3318 links.
CRLF preserved on codes/links.

Holds/gaps: 161 SAPO directory rows unresolved (province-named
/ RPA / farm / depot towns); none affect bundled primaries.
GeoNames admin2 stale in spots (pre-2016 Inxuba Yethemba,
Naledi, Blood River, Middelburg-MP/EC collision) — always
outvoted, never sole signal. BB city field carries metro-label
artefacts (Vredendal-North->Cape Town); quarantined via scoped
overrides.

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_tr.py` ALL
PASS): the 81-province / 973-district tree re-verified clean against
ISO 3166-2:TR (all plates 01-81, names identical) and the
Districts-of-Turkey per-province tables — wiki omits the Merkez row in
all 51 non-metropolitan provinces and drops Gömeç from the Balıkesir
table (GeoNames admin2 + 10715 confirm Gömeç is ours-correct). The
2,896-code overlay is exactly the GeoNames TR dump minus 57 KKTC
99xxx rows (Northern Cyprus, correctly excluded); every primary agrees
with the GN modal admin2 (269 via Merkez equivalence, 6 via stale-GN
spellings where ours is current: Doğubayazıt, Tillo, Çağlayancerit).
All 7 shared codes adjudicated keeps with town-page place evidence
(09670 Buharkent/Koçarlı, 16270 + 16370 Osmangazi/Yıldırım, 19800
Bayat/Dodurga-Akkaya, 35730 Kemalpaşa/Bergama, 44000
Yeşilyurt/Battalgazi, 55530 Salıpazarı/Tekkeköy-Kutlukent); 16270 and
44000 are GN 1-1 ties with primaries kept. Coverage 970/973: Derecik,
Sultanhanı, and Kemalpaşa (Artvin) are post-2017 splits still served
by their parent codes (30800 Şemdinli, 68190 Aksaray Merkez, 08610
Hopa) — single-signal gaps, unblocked only by PTT-directory
corroboration (JS-walled at revisit time).

## Panama

The bundled `PanamaGeographyProvider` supplies the 10 provinces plus the
4 province-level comarcas (Guna Yala, Emberá-Wounaan, Ngäbe-Buglé, Naso
Tjër Di) as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('PA')` after
countries are seeded.
The 81 districts ship as level-2 areas under provinces and comarcas.

Panama has no postcode system: addresses are formatted per the UPU layout
with street lines, locality, and country. Rural PO-box style addresses
(`Zona 4, Apartado 0819-...)` keep the zone box in the street line.

Revisited (2026-10-04): verify-only — tree 14 L1 (10 provinces +
4 comarcas incl. Naso Tjër Di, Law 2020, childless like Guna Yala)
+ 81 districts exact vs WP Districts of Panama (name + parent +
spelling, zero diffs; WP's own source is the INEC 2023 census
Cuadro 10). Postal `none` re-confirmed: UPU PAN sheet 02/2015
shows no delivery postcode (home-delivery example codeless;
4-digit 0815/0832 numbers are PO Box agency prefixes, kept in the
street line) — no CSVs is correct. Gate
`docs/agents/audit/gate_pa.py` pins the full tree.

## Paraguay

The bundled `ParaguayGeographyProvider` supplies the 17 departments plus
Asunción as `State` rows and a two-level administrative hierarchy
(department → 263 districts). It is selected with
`SeedCountryGeographiesAction::execute('PY')` after countries are seeded.
Asunción is typed `capital_district` sharing the L1 `department`
level, so it sits in the state tier with department grouping rather
than a standalone label. The state tier is selected through `state_id`
and carries no assignment role; the only role is the level-2
`district` role.

Paraguayan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

Revisit 2026-10-05 (verify-only, zero changes; `gate_py.py` ALL
PASS): tree 281/281 — 18 L1 ISO PY-1..16+19+ASU exact, 263
districts exact vs the es.wiki municipios annex (names +
parents). GN ADM2 (245) agrees modulo abbreviations + 15
newest absent (stale); en.wiki Districts (161/262) +
Departments column (261) + Esri 268 are stale/different
universes. Postal: 259/259 p4 prefixes pure + 18/18 p2 pure
(DINACOPA dept 00-17), sectors gap-free, 0 multis;
youbianku transcription re-verified (5 detail breadcrumbs +
18/18 Guayaibí + 18/18 San Pedro + 4/4 Botrell); 9 codeless
districts confirmed code-empty at source; operator anchor
001013 (DINACOPA HQ) -> Asunción. No GN postal dump (404);
Mapanet 4-digit stays excluded.

## Uruguay

The bundled `UruguayGeographyProvider` supplies the 19 departments
as `State` rows and a two-level administrative hierarchy
(department → 136 municipalities). It is selected with
`SeedCountryGeographiesAction::execute('UY')` after countries are seeded.

Uruguayan addresses are formatted per the UPU layout: street lines,
`{postcode} – {locality}` with a 5-digit postcode and en dash, the
department on its own line, and country.

Revisit 2026-10-04 (fix: +11 areas, 9 renames, 8 primary flips,
11 drops, 14 adds; `gate_uy.py` ALL PASS): the 136 count is the
post-2025-election roster (OPP Mapa Municipios 2025, box-counted
3/32/16/13/2/1/3/6/8/8/9/3/3/4/6/4/5/4/6): the 2020 roster of 125
plus Ansina (Tacuarembó, 2015 batch the bundle missed) and the 10
department-promoted creations — Del Andaluz and Juanicó (Canelones);
Conchillas and Cufré (Colonia); Pirarajá and Zapicán (Lavalleja);
Cerro Chato (`paysandu:cerro-chato`, colliding with the Treinta y
Tres town) and El Eucalipto (Paysandú); Villa Soriano (Soriano);
Villa Caraguatá (Tacuarembó) — each confirmed by OPP + ES tables +
Medios Públicos and/or its Intendencia roster. Laguna Merín was
already bundled. Punta del Diablo, Barra del Chuy (Rocha) and 25
de Mayo (Florida) were approved for 2030 and are intentionally not
seeded. Montevideo labels use the official Spanish `Municipio A` …
`Municipio G` (OPP + ES tables; EN `Municipality` was
translation-only), Colonia ships `Colonia Miguelete` (OPP + ES +
GeoNames 70800 row), Treinta y Tres keeps `Enrique Martínez`
(official `Gral. Enrique Martínez (Charqueada)` over ES `La
Charqueada`). The 124-code set is exactly the Correo listadoCP set
(1943 rows, set-equal both directions; GeoNames covers 122/124 —
20100 Punta del Este town + 27500 India Muerta zone are
Correo-official, GN-stale). Legs 339 → 342, multis stay 88 (15800
+ 12800 collapse to singles, 12400 + 34100 go dual): 8 primaries
flipped — 37000 Tupambaé→Cerro Largo dept (zero Tupambaé rows in
74+74, builder bug; Tupambaé town is 36100), 50200→Belén,
15700→Toledo (seat + fully-municipalized Canelones),
30100→Solís de Mataojo (only town), 91200→San Bautista (only
town), 91500 Empalme Olmos→Sauce (zero EO rows; EO town is 15600),
12400 D→G (Peñarol/Colón G 2/3 over Manga D; Villa Colón +
Conciliación infoboxes corroborate), 12500 D→G single (zero D
support; Lezica A-split secondary would be single-signal, held).
11 zero-support legs dropped (55000→Barros Blancos cross-country
join bug, 15000→Toledo, 15300/15900→Empalme Olmos,
15800→Nicolich/Pando, 37100→Las Cañas, 12800→D, 91500→CdC/EO,
12500→D); 14 added (11 new-municipio seat secondaries, 37000→Las
Cañas per OSM 37000, 12400/12500→G). Montevideo, Canelones and
Maldonado are fully municipalized, so department-level legs there
are coarse fallbacks, never rural coverage. 30 single-signal
candidates held (MVD E/F + sliver splits, 20500/27300 border
parajes, split-town ties 35200/34200/40200, seat-vs-vote
45100/98000/15500).

Revisit 2026-10-06 (open-log #15 retry: 5 flips + 7 drops + 16 adds,
27 of 30 holds resolved; `gate_uy.py` ALL PASS): adjudicated against
IDElUy municipio polygons (pip), Corte Electoral plan-circuital,
INE loccen/loccat, Falling Rain gazetteer, and OSM/Nominatim —
same-lineage agreement never counted (Correo/GN = one base pair; FR
per-place pages copy GN coords; shared-fallback coords void).
Montevideo: 12100 E→F, 12000 F→D (+C, −E), 8 H3 sliver adds
(11900+G, 11300+B, 11600+E+C, 11400+D, 11800+CH, 11700+D+A);
Lezica A-split stays G-single (no delivery points in A-territory).
Interior: 35200 FL→Cerro Chato primary (+TT dept leg; tri-department
town, FL was 17% with no center function), 60200 dept→Quebracho
(+Chapicuy +Cerro Chato PA, −dept), 20500 −Lascano −Maldonado-dept
(3 legs left), 30100 +Soca (lean, 71 m margin), 70800 +Conchillas,
33000 −Vergara, 50000 −Belén, 70000 −Tarariras. Legs 342 → 351,
multis 88 → 93 (11300/11600/11700/11900/12100 go multi; no
collapses). Vindicated keeps: 40200/34200 ties→incumbent, 45100/
98000 vote-over-seat, 15500 Pando, 12500 G-single, Agraciada split
town, Belén / Juan L. Lacaze / Villa Soriano names. 3 holds stay:
37000→Tupambaé (Rincón de Tacuarí unplaceable), 91500→San Jacinto
(paraje "Blanco"), 70000→Carmelo (Santa Rosa 50 m straddle).

## Venezuela

The bundled `VenezuelaGeographyProvider` supplies the 23 states
plus the Capital District and the Federal Dependencies (ISO code
`W`) as `State` rows and a two-level administrative hierarchy
(state → 335 municipalities). It is selected with
`SeedCountryGeographiesAction::execute('VE')` after countries are seeded.
The capital district is a second `areaType` on the L1 `state` level,
and the childless federal dependency a third, so both are selected
through `state_id` and neither carries an assignment role. The only
role is the level-2 `municipality` role. Libertador ships
under the capital district.

Venezuelan addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 4-digit postcode (extended
`3028-A` style codes pass through), the state on its own line,
and country.

### Revisit (B18, 2026-10-05)

Tree verified (Guayana Esequiba correctly excluded: no ISO
code); two Bolívar renames applied (`gate_ve.py` ALL PASS):
`Heres` → `Angostura del Orinoco`, `Raúl Leoni` → `Angostura`
(name + slug). Postal files hold at 444 codes / 450 links with
NO changes: all 5 shared codes reconfirmed against a fresh
Mapanet pull — 2301 Guárico-p (capital San Juan de los Morros
+ 15 towns) / Aragua-s (Barbacoas), 2334 Aragua-p / Guárico-s
(Barbacoa), 2350 Guárico-p (Valle La Pascua) / Anzoátegui-s
(El Chaparro), 3101 Trujillo-p (Torondoy basin) / Mérida-s,
3158 Mérida-s (Las Virtudes). The 3101/3158 Zulia sides rest on
prior-batch evidence (zipcodehere/56ok/postcode.info),
unrefuted (fresh Mapanet Zulia 15/15 pages lacks both codes;
IPOSTEL finder is a JS shell with no static table).

### Revisit (B19 r2, 2026-10-06)

Postal top-up: +8 codes / +8 state-only links → 452 codes /
458 links (`gate_ve.py` ALL PASS). Each new code carries two
fine-source signals (youbianku page + postcode.info page,
town + state agreeing): 2303 El Calvario + 2304 El Rastro →
Guárico, 3060 El Empedrado/Pie de Cuesta → Lara, 3102 Carvajal
+ 3108 La Ceiba + 3113 Santa Isabel + 3115 Burbusay + 3149
La Cejita → Trujillo. Legs stay state-only: Mapanet town folds
conflict at fine level (Carvajal 3101, Santa Isabel 3103,
La Cejita 3101, La Ceiba 3154, El Empedrado 3031) — coarse
hub-folding vs fine sources, so admin2 legs are HOLD. Rejected
a proposed `3101 → Zulia` leg removal: postcode.info p3101
lists three Zulia towns (Arapuey, Boscán, El Batey), so the
non-primary leg stands (Mapanet Zulia is lossy here). Also
HOLD: a 3101 → Lara spillover leg (Quebrada Arriba is 1v1:
postcode.info 3101 vs Mapanet 3031).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_pk.py` ALL
PASS): the Pakistan tree re-verified clean against ISO 3166-2:PK
(BA/GB/IS/JK/KP/PB/SD) and current district lists — 185 areas (4
provinces + 3 territories + 178 districts; Balochistan 42, KP 40,
Punjab 41, Sindh 30, AJK 10, GB 14, Islamabad 1) including the May
2026 Balochistan batch and the 2022 Punjab/KP/GB splits. The
3114-code / 3121-link L2 overlay re-verified clean against the live
Pakistan Post postcodes table (identical code set), the PART-I
delivery + PART-II NPO directories, and DG PPO Circulars 4/2022 +
15/2021: all 7 dual-links kept as adjudicated (06011/07418 double in
PART-II; 02502/06536/07514/07529/24302 double on the live table), all
singleton samples and block-odd codes accounted for as GPO-catchment
artifacts, and 11 districts genuinely codeless (Allai, Darel, Haveli,
Kolai-Palas, Lower South Waziristan, Mohmand, Roundu, Sohbatpur,
Surab, Upper Dera Bugti, Wadh). Shigar is covered (16810, PION via
Skardu GPO), so the overlay table's "12 codeless incl Shigar" is
stale. Watch item: five Circular-11/2023 Islamabad codes (44032,
44122, 45260, 45620, 45722) remain unpublished in every directory;
re-check next revisit.

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

### Revisit (B20, 2026-10-06)

Tree ZERO changes, postal 19155/19372 → 19238/19488
(`gate_in.py` ALL PASS). L1 36/36 exact vs
ISO 3166-2:IN. L2 vintage ~Jan 2026, ahead of
Wikipedia in 7 confirmed spots (NL Meluri, DL 13,
AP 28, HR Hansi, GA Kushavati, KA Bengaluru
South, RJ post-rollback 41) — no action. The 5
notified Ladakh districts are HELD OUT per the
LGD-keying policy (gazette real, no LGD codes
yet; worker's add verdict overruled after
ladakh.gov.in + LGD-codes search). Postal L1
sweep (~1500 pins, India Post mirrors, tl_geoid
fractional, 2-vote rule): 195 pins re-legged
(AP/NTR + TN restructure primaries; Bengaluru,
Godavari, Nagaon, Sambhal batches; Delhi
412→111/113 local pans per circle directory)
and 83 missing pins ADDED (Karimnagar block,
Kanchipuram run, singles). Holds: Delhi re-leg
pre-delivery validation, stale-leg pattern (81
zero-leg districts), KA Rural→North watch,
pypinindia rejected (GN-identical, not an
independent signal).

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

Revisit 2026-10-04 (fix-and-fill: 2 renames, 87 added legs, 10
dropped legs, 358 primary flips, 2 fills → 2943 codes / 3750 links
/ 687 multi-L2; `gate_gb.py` ALL PASS): the tree matches the ONS LAD
list (Apr 2025) and the Lieutenancies Act 1997 Schedule 1 on all 48
English ceremonial counties, 22 Welsh areas and 11 NI districts
(the quoted-comma `Armagh City, Banbridge and Craigavon` / `Newry,
Mourne and Down` rows are valid CSV, not anomalies), except two
Scottish names fixed to the ONS LAD exact form: `Orkney` → `Orkney
Islands` (ISO GB-ORK agrees), `Na h-Eileanan Siar (Western Isles)`
→ `Na h-Eileanan Siar` (ISO GB-ELS reads `Eilean Siar`, its short
form; the GN dump's 232 `Western Isles` rows confirm the
parenthetical is the legacy name; source_ids kept stable). Every
link was re-derived from ONS NSPL August 2026 unit postcodes (1.81M
live) mapped LAD→L2 via the statute (unitary table + Tees-centreline
split, tested per-unit against OSM river geometry with 15/15
calibration anchors); primaries are geocoded-live-unit pluralities
(blank-LAD units excluded) and were corroborated by GeoNames place
votes plus 46 live postcodes.io outcode arrays — which reproduce
the new sets and falsify the old claim of a postcodes.io derivation
(the missing legs are pre-2020 units, i.e. builder bug, not drift).
The integrator reproduced all 358 flip targets and shares, all 87
added-leg unit counts, and all 10 dropped-side zeroes directly from
the NSPL aggregates. Stockton-on-Tees outwards keep only their
river-proven legs (TS17/TS2 stay dual; TS15→North Yorkshire;
TS16/TS18–TS23→Durham; TS8→North Yorkshire); PA34→Argyll single
(NSPL 366:0 + postcodes.io Argyll-only district array). Fills
E22→Greater London (Isle of Dogs, 2024+: NSPL 19 Tower Hamlets units
+ mathmos/OSM 19 Code-Point Open units with 13 OSM-mapped) and
MK20→Buckinghamshire (MK East, 2026-01+: NSPL 6 Milton Keynes units
+ mathmos/OSM 6 Code-Point Open units) each carry NSPL + OSM +
postcodes.io signals (addressed directory sightings were
snippet-grade only and are not cited). The 61-drop list is
vindicated exactly (37 dead incl. truncated EC1/W1/SW1/WC1 and
retired W1M/WD1/WD2, BN91 live-but-ungeocoded, 23 live Crown as own
GG/JE/IM countries); 10 more NSPL-live large-user-ungeocoded
outwards (GIR, IM99, CH90, EN77, LS78, PO24, S94, SN80, SR43, TW98)
and 33 micro-legs (<10 units, no GeoNames place) stay held out, and
7 razor primaries (NG20 369/368, WA3 762/754, CW3, LA6, MK19,
PH12, WV9) keep their bundled side — all pinned in the gate. BT75
flipped 2026-10-06 to Mid Ulster primary (dual Fermanagh and Omagh
leg kept): NISRA CPD_LIGHT Jul-2026 gives 73/136 Mid Ulster,
agreeing with NSPL 72/136, with the Fivemiletown town majority
explaining the margin; the 17 other BT flips are confirmed at
81–100% by the same third lineage (80/80 BT primaries agree, zero
mismatches). CSV line endings: areas LF, postal files CRLF —
preserve per file.

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

The 1373-code overlay (1000–9461) starts from the GeoNames dump at
district level — all 64 districts covered, old-spelling admin2 names
mapped to the post-2018 spellings, GPO anchors verified — plus 24
post-2018 fills. Office-level codes link their district; no new area
rows.

Revisit 2026-10-03 (fix-and-fill: 24 fills, 1349 → 1373 codes/links,
zero moves; `gate_bd.py` ALL PASS): the tree matches ISO 3166-2:BD on
all 8 division codes and 64 district codes/names/parents except BD-41,
where we deliberately ship `Netrokona` (official portal
`netrokona.gov.bd` and the Districts-of-Bangladesh oracle) against
lagging ISO `Netrakona`. All 1349 vintage codes and links were
corroborated against the archived Bangladesh Post finder (1330 codes,
zero link mismatches), the UPU addressing profile, a full
GPO-directory mirror, Mapanet (1326 rows), postcodebase (1359 codes),
and 56ok — including the four cross-block keeps (1333–1335
Lohajong/Munshiganj, 3893 Chhatak/Sunamganj, 5470 Pirganj/Thakurgaon,
8013 Chandradighalia/Gopalganj) and Demra 1360. The 24 fills carry 2+
independent signals each (Mapanet + postcodebase + GPO directory,
with the 2024 official Cumilla page for 3505/3512/3547/3573); 3515
was deliberately not filled (superseded by 3512 per the same page).
Rejected Mapanet-lineage typos (1661–1665, 2461, 4240, 8920/8921,
1218, 1231, 1921) stay absent. CSV line endings: areas CRLF, postal
files LF — preserve per file.

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

The 2,083-code overlay (10000–94152) joins the GeoNames 1,325-code
base with a Mapanet 3,370-row top-up that adds Casablanca and Rabat
coverage: 62 of 75 provinces/prefectures covered (13 small/new
codeless), 5 shared codes adjudicated against the Poste Maroc
annuaire; no new area rows.

Moroccan addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode left of the locality,
and country. Types are labelled `Région`, `Préfecture`, and
`Province`.

Revisit 2026-10-03 (3 flips + 6 drops, `gate_ma.py` ALL PASS; verified
against the Poste Maroc codepostal.ma annuaire, ISO 3166-2:MA, and
the Prefectures-and-provinces list): tree clean — all 12 regions and
75 divisions match with correct parents (deliberate keeps: Taroudant,
Mohammedia, Aït diacritics; the ISO wiki-table In-region cells for
Chtouka-Aït-Baha and Nouaceur are list errors). Postal fix: 3
primaries moved to the annuaire filing (35224 Oulad Ayyad Taza →
Taounate; 80100/80650 Agadir → Inezgane quartiers) and 6 xx119
Casablanca phantoms dropped (no official trace, bare no-quartier
directory rows, suffix 119 unattested) — 2,083 codes / 2,088 links,
still 62 of 75 covered. The 13 new/small provinces stay codeless
because Poste Maroc's own directory has no sections for them; their
codes live under the parent provinces.

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

### Revisit (B19, 2026-10-05)

Tree verify-only (taxonomy exact, 33/33 ISO, TW absence
deliberate). Postal surgery to 2353 codes / 2353 1:1 links
(`gate_cn.py` ALL PASS): the bundle blindly followed GeoNames
admin2, wrong on 16 codes — all retargeted (YBK directory +
NBS-divmap county membership / wiki / block coherence):
015400→Bayannur, 038300→Shuozhou, 044300→Yuncheng,
121000→Jinzhou, 236200→Fuyang, 244100+246700→Tongling (246700
by current-admin rule over stale YBK/GN), 276000→Linyi,
317300→Taizhou-ZJ, 342600→Ganzhou, 541300→Guilin,
657600→Zhaotong, 673400→Nujiang, 713100→Xianyang,
810600/810700→Haidong. Swaps 057800→054900 (YBK synonym page)
and 040000→041000 (YBK 404 vs full page), drop 671100
(typo-dupe of 651100), fills 158100 Jixi / 666100 Xishuangbanna
/ 838000 Turpan (zero-link prefectures 3→0) + 665000 Pu'er +
461700 Xuchang. Holds: 452600 Zhoukou (3-way conflict),
817300/162800/201300/676200/845100 overrides confirmed, ~45
pemekaran keeps. NBS static tables + MCA API still blocked
(worked around via salvaged NBS divmap + YBK).

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

Revisit 2026-10-04 (fix-and-fill: 1 fix, 136-link Tyumen retarget,
zero fills; `gate_ru.py` ALL PASS post-state, FAILs pre-fix on exactly
the 6 626-related checks): tree verified — 83/83 ISO 3166-2:RU codes
with the exact 46-oblast / 21-republic / 9-krai / 4-okrug /
2-federal-city / 1-autonomous-oblast split, all L1, no parents; the
ISO page carries no CR/SEV/new-territory codes and WP lists "83 (+6
unrecognized)", so the bundled pre-2014 roster is the stance (Crimea,
Sevastopol, 2022-claimed absent; no 26x/27x/28x/29x codes bundled).
Postal 43531/43531 vs the current GeoNames RU dump (43538 rows, all
distinct codes): bundled = GN minus exactly the 7 Baikonur 468xxx rows
(Kazakhstan, correctly dropped); all 6-digit, all primary, zero
dangling, every subject GN-admin1-pure. Fix: 626011–626399 (136 codes,
Tobolsk 6261 / Nizhnyaya Tavda–Yarkovo 6260 / Vagay 6262 / Isetskoye
6263) Khanty-Mansi → Tyumen — ru-WP postal-division table puts
625–627 in Tyumen and 628-only in KHM, OSM Nominatim resolves 626150
Tobolsk and 626020 N. Tavda to RU-TYU, and WP district articles place
Nizhnetavdinsky/Uvatsky/Vagaysky raions in Tyumen Oblast. Post-fix:
Tyumen 485 (625/626/627), KHM 238 (628), YAN 629×96, NEN 166×33, YEV
679×88, CHU 689×53 — each rescue second-signaled by Nominatim
(RU-KHM/YAN/NEN/YEV/CHU on 628418/629000/166000/679000/689000) and
place-name review (Surgut/Nefteyugansk/Nizhnevartovsk;
Salekhard/NovUrengoy/Noyabrsk; Naryan-Mar; Birobidzhan/Obluchye;
Anadyr/Pevek). Keeps: Chita Oblast → Zabaykalsky 436 (incl. 687
Agin-Buryat ×18), Kamchatka Oblast → Kamchatka Krai 119 (incl. 688
Koryak ×30); 144700 UFPS-Moscow-Oblast office stays Moscow city (GN
admin1 Moskva vs 144-block tie → stability, office sited in Moscow);
78 × 901xxx mail-route codes stay at GN origin region (ru-WP lists
901 as special-purpose). Oracles: GeoNames RU.zip (current, 43538
rows), ISO 3166-2:RU, WP Federal subjects of Russia, WP Postal codes
in Russia (6-digit, first-3 = subject), ru-WP postal-division table,
OSM Nominatim postcode index, UPU RUS profile family (live upu.int
PDF links now 404/JS; format corroborated via itelegram UPU-doc
mirror + WP). EOL: areas LF, codes/links CRLF (preserved).
Holds/gaps: tier-2 raions still parked per doc 17 (no consolidated
source); Pochta.ru finder is a JS shell (no server-rendered region
to scrape) so Nominatim + prefix table stand as the second signals;
901xxx attribution follows GN origin-city convention, not delivery
geography.

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

### Revisit (B19, 2026-10-06)

Tree membership/types/parents exact, 11 endonym
renames; postal set exact, 10812/10910 →
10812/10911 (`gate_de.py` ALL PASS). L1 16/16
vs ISO 3166-2:DE; 401/401 vs WP LK294/SK107
KrS lists + GN AGS (Hanau 27th HE district +
6 codes vindicated 2026-correct). Renames: 6
English exonyms → endonyms (Cleves→Kleve,
Cologne→Köln, Hanover→Hannover,
Munich→München ×2, Nuremberg→Nürnberg;
English kept as provider alternatives) +
Hohenlohekreis, Saarpfalz-Kreis, St. Wendel,
Frankfurt (Oder), Neustadt an der
Aisch-Bad Windsheim. Postal: exhaustive
per-code GN vote 10,763/49; 20 same-name-town
retargets (Munster/Halle/Essen/Fürth/Weimar/
Oberhausen/Amberg/Landau bugs) + 4 primary
swaps (07919 Vogtland, 12529 Dahme-Spreewald,
21465 Stormarn, 92637 Weiden) re-closed by
integrator OSM Nominatim (11/12; 50586 empty
but 50xxx=Köln space) + 1 Stormarn secondary
(22113, Hamburg primary OSM-confirmed) + 10/10
agreement spot codes. Holds: 98711 S5
(Nominatim empty, medium confidence),
91054/98739 keeps, 12529 Berlin secondary,
71/95 multi secondaries unreviewed,
GN-Hamburg-quarter rot lesson (corroborate,
never trust alone).

## France

The bundled `FranceGeographyProvider` supplies the 18 regions (13
metropolitan, 5 overseas) as `State` rows and the 101 departments
plus the Lyon Metropolis as level-2 areas under their regions. It
is selected with `SeedCountryGeographiesAction::execute('FR')`
after countries are seeded.

The 20,316-code overlay comes from the GeoNames 51,611-row dump at
department level, re-verified B13 against Hexasmal + geo.api.gouv.fr:
4,624 qualifier rows stripped to base delivery codes, 24
cross-department codes dual-linked, 7 GN-artefact dual legs
deleted, 13 Rhône→Métropole codes retargeted, 3 primaries flipped
on seat-majority, 93380 filled, Clipperton 98799 dropped
(uninhabited); Roissy 95701 / Orly 94391 held out (single-signal).

French addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country. CEDEX
suffixes are not generated. Types are labelled `Région` and
`Département`; the 973 region row is the endonym `Guyane` (matching
the department row and the corrected states.json entry) with the
English `French Guiana` kept as an alias. The Lyon Metropolis keeps
its English docs name; the area row is the COG `Métropole de Lyon`.

Revisit 2026-10-04 (fix-and-fill: 4,624 qualifier-code strips + 7
dual-leg deletes + 13 Rhône→Métropole retargets + 3 primary flips
+ 93380 fill + 3 tree cells; `gate_fr.py` ALL PASS): the bundled
20,315 "codes" hid 4,624 unmatchable qualifier rows (`01014 9`,
`75303 SP 07`, `13661 AIR`, `78078 CITYSSIMO`) — the build deleted
the literal ` CEDEX` from GeoNames strings but kept
distributor/qualifier tokens, so the overlay doc's "CEDEX suffixes
stripped" claim was false as built. All 4,624 stripped to clean
bases (distinct, zero collisions) with GeoNames row IDs preserved
as the audit join; La Poste addressed usage confirms the physical
base code in every sampled case (AIR = aviation internal routing,
SP = Service Postal internal). Tree: 18/18 regions + 101/101
departments exact vs ISO 3166-2:FR + geo.api.gouv.fr; fixes are
Grand-Est→Grand Est (ISO hyphenated), Lyon→Métropole de Lyon (COG
2025 spaced), curly→straight apostrophe in
Provence-Alpes-Côte-d'Azur. Lyon split re-verified commune by
commune against EPCI 200046977 + Hexasmal; 01000 Bourg-en-Bresse
and 01400 Châtillon-sur-Chalaronne confirmed single-department.
CEDEX 01460 / 01960 / special-distribution codes stay out: no
department attribution attainable. 2A/2B codes numeric-only in the
tree, formatted 2A/2B by the provider.

Revisit 2026-10-06 (verify-only, zero data changes; `gate_fr.py` ALL
PASS): the Hexasmal downward sweep ran to completion — the raw file
carries `#Code_commune_INSEE` on all 39,192 rows, so the commune
crosswalk (6,328 codes, 0 unmapped, geo.api tie-break on all 35,007
INSEE) built directly. All 6,051 metro Hexasmal codes are bundled
with 0 dept-attribution deltas; the 277 overseas codes route out by
design (136 DOM + 139 COM to their provider files, Monaco and
Clipperton excluded). Open-log #7 closed.

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

Revisit 2026-10-04 (fix-and-fill: +3 sigla, +48 codes, −2 codes,
3 leg reworks; `gate_it.py` ALL PASS): tree verified — 20 regions +
109 L2 with ISTAT Elenco-codici Feb-2026 sigla (Gallura OT, Medio
Campidano VS, Ogliastra OG filled from the Motorizzazione table +
it.wiki Targa infoboxes + plate-continuity lists; Sulcis Iglesiente
filled SU 2026-10-06 — CdM n.168 preliminare + n.181 definitivo
(14-07-2026, Consiglio di Stato opinion incorporated) + Lega press
explicit SU↔Sulcis mapping over stale ISTAT Feb-2026 CI). Codes
4735 → 4781, legs 4745 → 4791, multis 9 → 10.
Sardinian reworks: 08020 drops Sassari (ISTAT: zero Sassari-metro
comuni; Nuoro-primary + Gallura secondary for the Budoni/San Teodoro
coast), 08030 drops Oristano and re-primaries Cagliari over Nuoro
(10 Sarcidano comuni vs 7; Genoni confirmed Cagliari-metro),
09020 gains Cagliari secondary under Medio Campidano (Ussana /
Pimentel / Samatzai trio, ISTAT Cagliari-metro 318). 07051/07052
Budoni/San Teodoro fill to Gallura (comuni.json + NSC OT-locality
bank + addressed 2026 usage); 09050–09069 Cagliari-metro run + 09064
Seui→Ogliastra + 09065 Seulo→Nuoro + 09089 Bosa→Oristano fill from
comuni.json + all three courier CAP lists. Cesena 47023 dropped
(it.wiki Codice postale lists 47521/47522 only; absent from
comuni.json and all courier lists) with 47521/47522 filled;
Ravenna 48121–48125 filled (comuni.json + MagicLand + per-code
addressed usage; all 7 Cesena/Ravenna fills re-confirmed live
2026-10-06 by fresh NSC street tables + official Poste.it
Variazioni-CAP delta bulletins). Sappada pair: 32047 dropped,
33012 filled to Udine (ISTAT FVG/Udine + all courier lists +
addressed; GeoNames 32047 Sappada/BL row stale pre-2017).
New-comune fills 10079 Mappano, 29031 Alta Val Tidone, 33014
Treppo Ligosullo, 36044 Val Liona, 36048 Barbarano Mossano, 52019
Laterina Pergine Valdarno, 61036 Colli al Metauro, 62031 Valfornace
(comuni.json + en.wiki / db-city); Verbania 28921/28923–28925
(comuni.json + Poste-branch addresses; 28922 kept); 15122
Alessandria, 41123 Modena, 04031 Ventotene, 71051 Isole Tremiti
(dual-live with 71040 San Nicola), 82014 Ceppaloni. Holds stay
out, triple-sealed 2026-10-06 (fresh NSC NOT_FOUND + en/it.wiki
ranges skipping them exactly + WPC directory skips vs CJ-2019
contiguous padding alone): 09132/09133, La Spezia 19127–19130.

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

The 120,720-code overlay comes from the Japan Post ken_all official
gazette at municipality level (stored in dashed NNN-NNNN form):
designated-city wards roll up to their city codes, firm codes and
abolished rows excluded, 1,741 of 1,747 municipalities covered (the 6
Northern Territories villages codeless); no new area rows.

### Revisit (B21, 2026-10-06)

Tree ZERO changes, postal 120682/120764 →
120720/120801 (`gate_jp.py` ALL PASS). 47
prefs == ISO 3166-2:JP == MIC; 1747 muns
== MIC R6.1.1 set-exact, 0 kanji/type/
parent diffs; naive-Hepburn + GN-admin2
romaji screens rejected as artifacts.
FIX-A: 77 Tenryu-ku legs 22100→22130
(stale pre-2024 ku→city table; sibling
Chuo-ku already correct). FIX-B: 432-0000
phantom Shizuoka secondary deleted.
FIX-C: 38 KEN_ALL-missing codes added (32
Gosen-shi 959-10xx vintage hole + 6
scattered) as a terminal-authority
single-signal exception — JP Post defines
existence; live re-check carried as
hardening. Keeps: cross-prefecture
498-0000/871-0000 multis, Toyone 431-4121
cross-border delivery, 235/235 sample.

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

### Revisit (B21, 2026-10-06)

Verify-only: 0 tree fixes, 0 postal fixes
(`gate_us.py` ALL PASS). L1 56 (UM reasonably
absent); L2 3143/3143 GEOIDs identical to
Census 2024 gazetteer 50-state set, 0
name/type/parent defects (CT 9 planning
regions, VA 38 independent cities, AK 30 incl.
Chugach/Copper River split + Kusilvak,
Oglala Lakota, LaSalle/De Baca gazetteer
spellings, Bedford correctly absent).
Postal 40977/40977: 0 orphans (bundle ⊆ GN
exactly), 0/40977 state-join mismatches,
33226/33642 county-join (residual = CT
legacy-code vintage + DC L1-design,
vindicated; 408-row stratified sample 393
clean + explained rest). Holds: HUD
crosswalk 404 + USPS Akamai-wall (arbiter
unavailable), territory/military/MH scope
(149 ZCTAs + 509 APO/FPO + 96960/96970),
71 county-ambiguous single-signal ZIPs.

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

Revisit 2026-10-03 (B9 fix-and-fill: 11150/11172 to 11068/11094;
`gate_es.py` ALL PASS): tree verified clean — 17 communities + Ceuta
and Melilla + 50 provinces match ISO 3166-2:ES code-for-code with no
orphan parents. Primary oracle is the Correos nuclei API reverse
engineered from the finder bundle: exact-hit sweep of all 11150
bundled codes (11063 exact, 79 nucleus-404 re-verified stable, 8
fuzzy-neighbour matches exposed by response-code audit) plus a fresh
all-province municipalities sweep (7861 munis, 10865 main codes).
Corroboration: INE Callejero (11051 codes), CartoCiudad (182
leg-level queries), Nominatim, Wikidata P281, the UPU ESP profile via
Wayback 2018 (5-digit-before-locality + province roster), GeoNames
ES.zip (bundled set == GN set exactly), and web mirrors for renumber
adjudication. Removals (87, all with zero municipal holders):
renumber supersessions with live replacements pinned (34260
Revilla Vallejera villages → 09117 BU, 33692 Lena hamlets →
33693/33694, 33837/33838 Belmonte → 33830 series, 15591 Ferrol
parishes → 15590, 03115 Alicante diseminados → 03110, 42175 →
42174/42181, 28419 → 28412, 33599 → 33579, 37608 → 37609, 42147 →
42146, 19131 Entrepeñas → 19130, 16147 → 16143, 42350 Berzosa →
42351, 37479 → 37470, 33732 → 33734, 33736 → 33735), dead apartados
(30070/30071/30080 Murcia, 34260-class), withdrawn city sectors
(06012, 08805 Sabadell stale-web, 11200, 11574, 28870, 34006, 36281/
36282/36339 Vigo, 47018) and reservoir/station/finca phantoms (10396
Almaraz plant, 13434 Ciudad Real airport, 29395 Cañete station).
Fills (5): 01070 VI Vitoria apartados (Correos + postalcodesdb/cybo),
09117 BU (Correos ×2 views + ViaMichelin/codigo-postal.co; world
mid-migration from 34260), 21431 H Islantilla + 24359 LE San
Cristóbal (Correos + INE), 50221 Z Ariza villages (Correos +
citypopulation; absorbs the 42269 Z-leg). Dropped legs (3): 28189-GU
(Santuy unsupported: Correos M-only ×17 + INE M-only + CC empty),
28310-TO (Seseña is 45223/45224; Algodor is M per Correos + CC),
42269-Z (Ariza villages renumbered to 50221). New dual legs (7, each
Correos + independent second): 13249-AB Lagunas de Ruidera (5 web),
14113-SE Cañada del Rabadán (CC ×4), 16612-AB Ventas de Alcolea (10
web), 18312-CO Ventorros de Balerma (INE both), 26528-Z Torres de
Montecerzo (Nominatim 26528), 28600-TO Calypo Fado (CC), 45216-M El
Carrascal (CC ×2). Moved primaries (3): 28310 TO→M, 42269 Z→SO,
06691 BA→CC (Pantano de Cíjara, Alía side per CC). Kept duals (19,
all 2+ signals): Treviño 01118/01211/01427, 03657, 08281, 13110 CR
primary, 22583/22584 HU, 22808, 26212, 28190 GU primary, 33554
(Tresviso enclave, CC), 34492, 39232, 39250, 39419 (Lastrilla,
Correos + postalcodesdb over INE 34814), 43421, 44591 TE, 50686;
kept cross-prefix singles 14449 CR, 22806 Z, 26127 SO. Holds:
23296-AB, 18538-J, 45217-M single-Correos-nucleus legs; 28090
Zarzuela (Casa Real publishes 28071) + 52901-52905 Melilla
(USO RESERVADO) + 00000 excluded as Correos-internal. Gaps: INE
lacks 394 live codes (apartados/gran-usuario + delivery gaps) so
INE-absence never removes alone; 350/353 INE-extras are Correos-dead
typos (03818 fuzzy-false-alarm audited). Per-province primaries:
Araba 81, Albacete 131, Alicante 215, Almería 168, Ávila 148,
Badajoz 215, Illes Balears 163, Barcelona 397, Burgos 216, Cáceres
247, Cádiz 123, Castellón 134, Ciudad Real 131, Córdoba 140, A
Coruña 388, Cuenca 183, Girona 248, Granada 203, Guadalajara 182,
Gipuzkoa 110, Huelva 106, Huesca 274, Jaén 175, León 419, Lleida
301, La Rioja 131, Lugo 463, Madrid 315, Málaga 164, Murcia 207,
Navarra 265, Ourense 298, Asturias 404, Palencia 132, Las Palmas
147, Pontevedra 384, Salamanca 276, Tenerife 220, Cantabria 214,
Segovia 205, Sevilla 156, Soria 100, Tarragona 212, Teruel 211,
Toledo 232, Valencia 302, Valladolid 195, Bizkaia 143, Zamora 300,
Zaragoza 277, Ceuta 8, Melilla 9 — no codeless province.

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

### Revisit (B19, 2026-10-05) — round 1 of 2

Tree verify-only: 380/380 L2 vs eTERYT TERC 2026-01-01 +
PP operator enumeration + wiki, 16/16 L1 vs ISO. Postal
files to 20299 codes / 20583 links (`gate_pl.py` ALL PASS):
198 SIMC-vote conflicts all adjudicated vs the live PP
finder — 154 codes changed (95 primary flips, 10 primacy
swaps, 59 dropped legs), 15 keeps, 29 holds. Main finds:
10 same-name-twin clusters where the bundle joined the
wrong voivodeship twin (93 codes: średzki, świdnicki,
tomaszowski, opolski, krośnieński, brzeski, grodziski,
nowodworski, ostrowski, bielski), and a systemic GN
artifact (big-city street codes carrying one bogus
land-village row, typo-dupe class proven by twins in GN).
Round 2 outstanding (PL-r2 lane): 24 probe retries,
332 multi-link review, inverse-class sweep (bundle=land
primaries in city blocks — vote-blind), stratified
singles, cross-county prefixes, coverage statement.

### Revisit (B19, 2026-10-06) — round 2 of 2

Postal files to 20248 codes / 20395 links (`gate_pl.py`
ALL PASS): 525 codes PP-probed (all 332 multis + 198
conflicts + 94 inverse sweep + 10 novote sample), 227
more codes changed — 17 retarget flips, 81 primacy swaps,
142 leg drops, 10 leg adds, 51 stale-code drops. Classes:
conflict-hold retries (07-304/305/306/308 → Ostrowski
Mazovian 1416, 66-614 → Krośnieński Lubusz 0802, 96-314 →
Grodziski Mazovian 1405), over-500 city codes (43-300 →
Bielsko-Biała single, 33-100 → Tarnów city primary),
cross-voivodeship swaps (05-092/192, 05-807, 18-212,
24-120/160, 05-101 flip + NDM 2210 → 1408),
city-majority flips (PP street-majority → city primary,
land kept unless PP-0), ~110 typo-dupe/noise drops, box
code 32-312 → Klucze single. Stale drops are PP+KPI
absent with GN rows twin/home-explained (dropsig.json).
Keeps: thin applied-swap city legs, PP-confirmed
duals/ties, all 1102 GN-unanimous novotes (10/10 PP
sample). Holds: H-ADD (13 single-signal PP minority
rows, no add per Lezica rule), H-COV (87-220 Radzyń
Chełmiński absent from bundle + GN — needs directory
proof), H-MEDIUM (thin-PP flips/drops, second signal
each, pinned as-is). Code-set == GN exactly; full Spis
PNA is commercial (coverage HOLD on operator paywall).

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

### Revisit (B18, 2026-10-05)

Tree 342/342 PASS; postal files verified to 4071 codes / 4092
legs (`gate_nl.py` ALL PASS): BAG address census beats the
CBS-2024 vintage (1364 real per BAG, 5369 withdrawn). One stale
secondary dropped (1216 Wijdemeren; Hilversum primary kept) and
two BAG-attested secondaries added (6153 Beekdaelen,
6881 Rozendaal; code rows pre-existed).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ng.py` ALL
PASS): the 774-LGA tree re-verified — per-state counts match the
constitutional distribution and all 9 WP-LGA-page diffs adjudicated
ours-right (Ogun junk rows, Oyo Kajola/Surulere + canonical Ori Ire,
Sokoto Kebbe/Shagari/Yabo, Okrika / Bursari / Bakura vs WP typos,
constitutional Aiyekire). The 1926-code / 1926-link L1 overlay
re-verified clean: 215 dispatch prefixes unanimous, 1893 codes
corroborated by same-state 56ok ranges, and the 33 apparent
contradictions all resolved to 56ok-side errors with mirror cover
each (Yobe 620-632 / Taraba 660-672 unanimous blocks; Zamfara 8822xx
Kauran Namoda street mirrors; Isa 883101 Sokoto; Karim Lamido 888222
Taraba; Benue 982101-982104 Kwande cluster kept). HQ xxx001 codes
stay systematically absent (UPU Garki 900001 unshipped), as do
UPU-example singletons Aisegba 370104 / Oyo 211001 — documented gap
class; LGA-level join still needs the NIPOST facility file.

## Ethiopia

The bundled `EthiopiaGeographyProvider` supplies 14 regions and city
administrations as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('ET')` after countries are seeded.
The 115 zones and 9 Harari woredas ship as level-2 areas under their regions.

The Southern Nations, Nationalities, and Peoples' Region was dissolved
in August 2023 (split into Sidama, Southwest, South, and Central
Ethiopia). Seeding deletes any `SN` straggler rows, and the bundled
state data no longer ships the code. South Ethiopia (`SE`) and Central
Ethiopia (`CE`) have no ISO codes yet; those codes are provisional
and will be updated when ISO assigns them.

Ethiopian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
`region` type is labelled `Kilil`.

Revisit 2026-10-04 (B14 verify-and-fix, `gate_et.py` 0 FAILURES):
4 renames (Oromia `Borana`→`Borena`, `West Haraghe`→`West Hararghe`,
`East Welega GIMBIE`→`East Welega` — WP zone list plus the Oromia
article table; Tigray `Mekele`→`Mekelle` — GeoNames ADM2 zone-form
plus official standard) and 3 Amhara drops (unlinked `West Gojjam`
duplicate plus the lowercase `north gojjam` / `wolkait tegede stit
humera` rows, which are vandal rows in the WP zone list itself,
absent from every oracle; citypopulation double-claims Tsegede under
both Amhara and Tigray, confirming the Welkait dispute). Tree
127→124 L2 (Amhara 16→13, matching official count; Dire Dawa stays
terminal). Every other name is sealed: Afar 7 (article: six Rasu
zones including new `Mahi Rasu` plus the Argobba special woreda),
Gambela `Anywaa`, South Ethiopia `Gardula`/`Koore` (article display
names over the old Dirashe/Amaro links), Oromia `East Bale`
(MDPI/Haramaya plus townsvillages woreda list), `East Borana` /
`Buno Bedele` (article table plus citypopulation prefixes),
`Kelam`/`Illubabor` shorts, `Sheger City` (official English;
`Shaggar` is the Oromo form), Somali 11 base zones (citypopulation
prefix woreda sets) plus 6 `X Special` city/woreda units (zone-list
identity; `Tog Wajale` single-a per the Somali article), Harari 9
woredas at L2 (zone list carries the same grain). Postal: 1000
flipped to Addis Ababa primary (UPU `ethEn.pdf` plus the
EthioPost cheat-sheet, North Shewa stays secondary); the other 49
codes / 80 legs stand — the cheat-sheet corroborates 34 of 50 codes
and the woreda containments corroborate the Arsi/Bale/West Arsi,
Gondar, Tigray, Keffa/Bench Sheko and Afar splits. Retry
2026-10-06 (+1 code, +1 leg; `gate_et.py` ALL PASS): 1230 Akaki
Beseka added to Addis Ababa (cheat + vendor pool + addressed
Beseka School `PO BOX 28/1230`); 1150 gains a Sheger City
secondary (EHRCO press + Anbessa bank data place Alem Gena in
Sheger/Gelan Guda, Addis primary kept) — 51 codes / 83 legs / 23
multis. Tigray shorts kept (WP + EDRMC-2025 + Tigray Finance
Bureau short usage; longs equally attested incl. COD-AB 2025 —
no canonical form exists, hold closed as unresolvable-by-design).
Somali specials settled at 6 (COD-AB town units + DDSI 2016
session record; Harawo is a regular Fafan woreda, correctly
absent). Still held: Dessie/Woldiya/Sekota town codes (EthioPost
publishes a 629-branch office table but no postcodes; vendor pool
has no Dessie/Woldiya/Sekota rows; flag the vendor Abergele-6200
vs bundle-7220 contradiction, both single-signal, no flip).

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

Revisit 2026-10-04 (verify-only, zero changes; `gate_cd.py` ALL
PASS): 26/26 provinces match ISO 3166-2:CD on code + name
(Kinshasa deliberately typed `province`, the post-2015
city-province, though ISO kinds it a city); 145/145 territories
match COD-AB (re-downloaded from HDX, valid 2019-09-11) on name +
parent with per-province counts exact. The 19 extra COD-AB admin2
rows are villes (provincial capitals/major cities), deliberately
excluded — the bundle ships territories only, and Kinshasa is
terminal. Two COD-AB spellings overruled: `Kanyama` (not Kaniama)
and `Gandajika` (not Ngandajika) — EN redirects/article and FR
titles agree with the bundle. No postal files: DR Congo has no
postcode system.

## Tanzania

The bundled `TanzaniaGeographyProvider` supplies the 31 regions
(including Songwe, split from Mbeya in 2016) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TZ')` after countries are seeded.
The 194 districts ship as level-2 areas under their regions,
reflecting post-2021 splits (Busokelo, Madaba, Bumbuli, Chalinze,
Mpimbwe, Itigi) verified against government council registers.
"Nanyumbu Urban" ships under its official town name Nanyamba Town.

Tanzanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line, and country. Wards are intentionally not bundled.

Revisit 2026-10-05 (tree +Mlimba/+Mtama/+Kibiti,
Mpanda->Tanganyika, -Kilombero/-Lindi; postal 4034/4034 ->
4096/4096: 124 fills + 73733 swap-add - 63 removes + 59
moves; `gate_tz.py` ALL PASS): NBS 2022 census structure —
Kilombero DC dissolved (16->Mlimba + 2 Mang'ula->Ifakara
TC), Lindi DC dissolved (20->Mtama + 11->Lindi MC, now
31), Mpanda DC renamed Tanganyika DC, Kibiti DC added.
Katavi 502 block filled (TCRA+NAPA+live+NBS); Zanzibar
rebuilt (91 TCRA-2012 fills, 10-district live sample by
integrator; 60 sequential phantoms removed, absent
TCRA+NAPA+live); 53733->73733 Njisi swap; 57731 Lituta;
9 Mlele->Mpimbwe; town-council attributions. Integrator
verified every op class: scratch TCRA/NAPA sets, NBS ward
lists (Mtama-20/Mlimba-16/Tanganyika-16/Kibiti-16 full,
Mpimbwe-9 full), 32 live lookups. Holds: Magharibi A/B
split (NBS-confirmed, no shehia->code map, legs lumped),
65121-31 numbers bundle-single-signal, ~20 retired codes
kept, Kigoma extensions kept.

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

### Revisit (B18, 2026-10-05)

Postal files verified to 977 codes / 977 legs (`gate_ke.py` ALL
PASS): 47/47 counties + 290/290 constituencies exact (IEBC
numbered roster); PCK 4-list agreement drove 28 fills
(Nairobi 00514/00617/00624, Murang'a 01026, 10110/10134/10135/
10137/10138, 10225, 20155, 30127, 30220, 40130, 40226,
40324/40327, 40636/40637/40638/40642/40643, Kakamega
50129/50130/50131, 50301, 50427, 90201, 90409) and 26 moves off
wrong-county/Nairobi fallbacks (21 Nairobi-kept corrected, incl.
the 403xx/404xx/406xx Kisumu/Siaya blocks and 504xx Kakamega
block; plus 01102 Migori, 10311 Kisumu, 20420/90148 Narok,
30711 Uasin-gishu). Holds: 60210 Tigiji ghost, 80204 Watalii
unattributable, 01029/30216/90149 kept Nairobi for lack of a
second signal.

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

Revisit 2026-10-03 (fix: 13315 River Nile secondary dropped,
98 → 97 links; `gate_sd.py` ALL PASS): tree re-verified clean —
18/18 ISO 3166-2 states and 188/188 OCHA districts (names +
parents + per-state counts exact; Abyei PCA still excluded,
West Kordofan-parented Abyei district still ships). All 90
codes diffed against the independent SCC Oct-19 Sudan postcode
directory: code set exact, and 6 of 8 dual-links match including
primary order (21115 Jazirah, 25514 Blue Nile, 31116 Gedaref,
51111/51113/52221 North Kordofan). 13315 loses its River Nile
leg: SCC lists it Khartoum-only, the OSM state boundary sits at
~16.42N (the whole 16.0–16.3N cluster is Khartoum-side), and
GeoNames admin1 agrees for the found villages while the alleged
north-side villages are unfound — the build mislocated the
border. 63314 keeps both legs (Fongfong geocodes Central
Darfur, primary West Darfur per SCC). UPU anchor 11111
Khartoum holds; East Darfur stays codeless in every source.
GeoNames has no SD postal dump (404), worldpostalcode and
youbianku have no Sudan pages, and mapanet.eu now 403s, so the
SCC directory + Nominatim/OSM carry the postcode evidence.

## Suriname

The bundled `SurinameGeographyProvider` supplies the 10 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SR')` after
countries are seeded.
The 63 ressorten ship as level-2 areas under their districts.

Suriname has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line.

Revisit 2026-10-03 (verify-only, zero data changes; `gate_sr.py` ALL
PASS): 10 districts match ISO 3166-2:SR codes exactly, and all 63
resorts match the Resorts-of-Suriname oracle per district (two
Centrum resorts — Brokopondo and Paramaribo — plus the
comma-carrying Para, Zuid). Verdict stays none: the UPU surEn
profile shows a codeless street address, the UPU Sep-2025 list
carries Suriname on do-not-require, and GeoNames has no SR postal
dump (404).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ug.py` ALL
PASS): all 4 regions match ISO 3166-2:UG codes (C/E/N/W); all
135 districts match the Districts-of-Uganda oracle per region
(Central 25, Eastern 37, Northern 38, Western 35) plus
Madi-Okollo under Northern (real 2019-created district, ISO
UG-336; the oracle tables total 134 + Kampala against their
own 135 claim). Deliberate deviations from ISO spellings where
the oracle and UBOS agree with the bundle: Bukomansimbi (ISO
Bukomansibi), Luweero (ISO Luwero); Terego is post-ISO-vintage.
The 11 cities are the 10 regional cities operational 1 Jul
2020 plus Kampala (IGC/NPC list exact); the 5
approved-but-unfunded cities stay excluded. Postcode gap
stands: the UPU ugaEn profile (1.2026) documents an adopted
5-digit district/locality/zone system, but no allocation rows
are published (ugapost app shell, UCC chart postcode-free,
E-Posta gated, no GeoNames UG dump, zero Mapanet UG rows).

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

Revisit 2026-10-03 (fix-only: 19 link retargets, 3908/3908 codes kept,
coverage 547→548/548 dairas; `gate_dz.py` ALL PASS): tree re-verified
exact — ISO 3166-2:DZ 58/58 (Tamanghasset, Tipasa, El M'ghair, El Menia
display deviations kept) + Law 26-06 JO-n°25 numbering for 59–69
(mother-wilaya order; the alphabetical third-party variant rejected) +
geoalgeria daira sets 548/548. Code set == baridimap offices 3908/3908
(zero rot); GN 2918/3162 with the 119 renumbered-olds + 125
unverifiable GN-only deliberately absent. Retargets (geoalgeria ONS
join + frwiki/JO-91-306/OSM + GN place, 3 signals each): Aflou-wilaya
offices to Aflou/Oued Morra/Gueltat Sidi Saad (7), Djelfa-batch offices
to Birine/Sidi Ladjel/Had Sahary/Faïdh El Botma (6), Bougara→Hamadia,
Djezzar→Djezzar, Ferkane→Negrine, El Ogla El Malha→Bir El Ater (2),
Deux Bassins→Tablat. BOD/Debdeb/Aïn Smara stays mapped as communes to
In Amenas/El Khroub (frwiki-confirmed). Era note: 58- and 69-wilaya
maps both legally real through 2026; the 11 new wilayas fully
operational 2027-01-01.

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

### Revisit (B21, 2026-10-06)

Tree ZERO changes, postal 5526/5526 →
5547/5547 (`gate_br.py` ALL PASS). 27/27
ISO; 5570/5570 byte-exact vs IBGE, FnN
correctly district under PE; one-major-
CEP-per-municipality granularity
confirmed. 38 RO renumber replaces
789xx→768/769xx (ViaCEP old-erro 52/52
+ new-OK IBGE-match + OSM postcode);
78937-000 stale Itapuã dup deleted
(76861 kept); 68948-000 relinked Pedra
Branca→Serra do Navio; 22 general-CEP
adds (ViaCEP-OK + OSM). Follow-up
(integrator OSM 44-probe sweep, 6
matches on municipality boundaries +
ViaCEP): 3 more RO (Jaru, Presidente
Médici, Nova Mamoré), 3 small-city
generals (Santa Bárbara de Goiás,
Aroeiras, São Sebastião do Oeste),
Bujari 69923→69926 (69923 is a Rio
Branco street). Holds: 10 RO stale
(big-city policy + L1-only), 42
unlinked (L1-only/conflicts/street-
level), DF-19 + small-city-10 +
Noronha (ViaCEP erro on general;
street-coding undetermined).
Keeps: 144/144 ViaCEP-OK, 10 rename
resolutions, Buritirana non-000,
capital generics by convention.

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

### Revisit (B21, 2026-10-06)

Tree 10 renames, postal 2 reassigns
(`gate_mx.py` ALL PASS). 32/32 ISO;
2479 keys = INEGI 2478 + Villa Juárez
01012 (2026-08-28 vindicated); 21/21
new-key deltas vindicated (Playa del
Carmen 2025, Batopilas 2017, Yalálag
2023). Renames: Cadereyta Jiménez
(dup-bug), San Juan Colorado
(corruption), Dolores Hidalgo full,
San Martín Hidalgo, Acambay full,
Blas Atempa de-prefix, Tututepec
full, Atltzayanca, Ziltlaltépec,
Medellín de Bravo. T11 Las Casas
HELD (INEGI capital-L). PF1
77580/77586 → Puerto Morelos (2015
split); 74801 Tehuitzingo kept (S1
isolated master error). PF2 317 adds
HELD (single-lineage; current-Correos
spot check blocked — correos +
datos.gob.mx timeout from sandbox);
PH1 473 bundle-only held; 1285 admin
codes correctly absent; 384/384
sample.

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

### Revisit (B21, 2026-10-06)

Tree 1-char repair, postal 1651/1660 →
1663/1673 (`gate_ca.py` ALL PASS). L2 ==
reconstructed SGC 2024 exactly (5028/5028;
NB 2023 reform + IRI incorporations + QC
code changes already adopted); T1 Sambaa
K'e U+0092→U+2019 (sole control byte in
file + StatCan 98100002). FSA granularity
confirmed: G0B (retired, now G4T) + H4Z
(unassigned) removed (wiki cells +
StatCan-2021 absent; GN staleness shown);
14 FSA adds (StatCan pop + wiki-active;
R5J Taché via MB news release, R5L/R5M
via opened Wikipedia, R5N/R5P + V7Z
secondary via StatCan DPL parents).
Integrator holds: S7B Corman Park
secondary (MLS snippets only), S7C
secondaries (over 2-leg convention);
14 wiki-only FSAs + SGC-2025 delta held.

## Australia

The bundled `AustraliaGeographyProvider` supplies the 6 states plus
the 2 mainland territories as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AU')` after countries are seeded.

The 539 local government areas ship as level-2 areas (128 NSW,
79 VIC, 78 QLD, 137 WA, 70 SA, 29 TAS, 18 NT; the ACT has no
local government and stays childless). All eight LGA types
(city, shire, town, region, borough, municipality, rural
city, council) share the `lga` assignment role. Excluded:
Lord Howe Island and the Unincorporated Far West (NSW),
Christmas Island and Cocos Islands shires (external
territories, not WA LGAs), and the Gerard Aboriginal council
(SA community, not an LGA — unlike APY and Maralinga, it is in
neither the ASGS LGA set nor the SA government LGA list).

External territories (Norfolk Island, Christmas Island, Cocos
Islands) carry their own postcodes and are intentionally not areas.

Australian addresses are formatted per the UPU layout: street lines,
`{locality}  {ST}  {postcode}` with two spaces between each part, and
country. All 8 states and territories carry their postal abbreviation
as a searchable alias; the eight LGA types render correctly and need
no type labels.

### Revisit (B19, 2026-10-05)

Tree: Grant → Southern Limestone Coast Council (1-Jul-2026
rename, ESCOSA letter + wiki + govt list + geojson; slug kept,
transition in progress) and Lower Eyre Peninsula → Lower Eyre
Council (ABR entity + govt list; operating-name policy per
Roxby keep). APY + Maralinga Tjarutja added as SA L2 rows (4
signals each: wiki + ASGS2024 LGA set + govt list + geojson) —
the old "SA communities, not LGAs" exclusion was wrong for
these two (Gerard correctly stays out). Postal files to 3165
codes / 4186 links (`gate_au.py` ALL PASS): 7 flips (5150
Mitcham, 5273 Naracoorte, 7469 West Coast single, 0862 Barkly,
0885 Groote, 2335 Singleton, 7215 Break O'Day), 13 secondary
adds (incl. Cherbourg unlinked, APY/Maralinga/Groote/
Palmerston/Katherine), 9 drops. Oracles offline-only (network
down): ASGS gazetted coding index + GN place-postcode (GN
admin2 never an LGA oracle) + SA geojson PiP. Holds: NT
Coomalie/Litchfield types, ~89 n<10 primaries, 565 novote
office codes, metro slivers, single-signal adds; ASGS2024/GN
vintage predates SLCC + Groote.

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

### Revisit (B19, 2026-10-05)

L1 24/24 ISO, L2 membership 529/529 vs live georef
(`gate_ar.py` ALL PASS). Seventeen areas fixes: Pueyrredón
rename (2010 law), Cafayate/Hucal typos, GSM full name,
Feliciano short, San Miguel (Corrientes) qualifier,
Realicó/Rinconada suffix-strips, 9 accent fixes. Postal
files to 2501 codes / 3115 links: 2 San Miguel retargets
(operator "SAN MIGUEL" name-collided onto the BA partido —
the only cross-province mis-map), tie flips 3151→Victoria +
5400→Capital-SJ, and 6 CABA remaps from addressed usage
(C1405/C1424→C6, C1406→C7P+C10s+C6s, C1416 C10↔C11 swap,
C1426→C13P+C14s, C1439→C8) — the build's Mapanet-cluster
method proved ~40%-wrong in the 14xx fringe. Holds: SDE
Capital/JFB 3v3, BA Madariaga/Rosales, 3162, 8522, 5157,
4635-Y, CABA residual ~295, thin ties, singletons, 8133,
5272, and 13 officially-bare duplicate names (kept official
by integrator decision). Overlay L2 count corrected 527→529
(stale doc count).

## Colombia

The bundled `ColombiaGeographyProvider` supplies the 32 departments
plus Bogotá D.C. as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CO')` after countries are seeded.

The 1102 municipalities, 20 Bogota localities and 19 non-municipalized areas ship as level-2 areas under their departments.

Colombian addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 6-digit postcode, the department on
its own line, and country. Types are labelled `Departamento`,
`Distrito Capital`, `Municipio`, `Localidad`, and
`Área No Municipalizada`.

### Revisit (B20, 2026-10-06)

Tree +1 area, 42 renames; postal 3676/3676 → 3681/3681
(`gate_co.py` ALL PASS). L1 33/33 vs ISO 3166-2:CO.
Three-lineage verification (GN + 4-72 operator / wiki
annex + apicolombia / HDX-OCHA MGN), fix iff 2+ agree:
F1 San Jacinto del Cauca (Bolívar, DANE 13655) ADDED —
the only missing L2; F2/F3 +5 codes (134060/67/68,
474001/474007 → Pinto, was leg-less); F4 81 Bogotá legs
L1 → 20 localities (zones 1101–1120 = GN place;
Bogotá L1 now leg-less by design); N01–N42 renames
(Talaigua, Arroyohondo, Ciudad Bolívar, Cartagena de
Indias, Santa Bárbara de Pinto, …; source_ids
unchanged). Officially identical display names coexist
by design (La Paz ×2, Providencia ×2, San Andrés ×3 —
scoped by parent, same as the pre-existing San Pedro
×3). Holds: Albán 2v2 tie, 88001 ANM type (both need
true DIVIPOLA), Mirití hyphen format.

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

Revisit 2026-10-05 (4-code Loreto rotation fix + 42 leg moves
+ 2 renames; `gate_pe.py` ALL PASS): tree 222/222 (25
regions ISO-exact, 196/196 provinces vs the es.wiki
INEI-sourced annex modulo 4 adjudicated pairs, 196/196
parents). Bundle held Putumayo=1605/Requena=1606/Ucayali=
1607/Datem=1608; INEI truth (GN admin2 codes + annex) is
Requena 1605/Ucayali 1606/Datem 1607/Putumayo 1608 — codes
fixed, 42 legs remapped to GN-unanimous admin2 (16 Putumayo
→Requena, 11 Requena→Ucayali, 12 Ucayali→Datem, 3 Datem→
Putumayo). Renames: Antonio Raymondi→Raimondi (es.wiki +
en.wiki + gob.pe title; slug + 7 legs), Daniel Alcídes→
Alcides Carrión (GN + en.wiki + es.wiki). Keeps: Cusco
(official + en.wiki over GN/es.wiki Cuzco), Huanca Sancos
spaced, Vilcas Huamán accented. Postal: bundle set == GN
set exactly (2669/2669); GN spans >1 admin2 on exactly the
2 multis with matching plurality primaries (14000 Chiclayo
72>62, 14013 Lambayeque 7>1).

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

Revisit 2026-10-06 (B21 worker r3, verify-only, zero changes;
`gate_vn.py` ALL PASS): L1 34/34 (25 province + 9 municipality)
vs MOST TOC + Resolution 202 + openapi + ISO legacy codes (Hue 26
inherits VN-26; ISO entry explicitly obsolete); L2 counts 34/34
vs a direct MOST-text recount of 3321 true rows (the prior
worker's 3319 JSON was a lossy parse — dropped Tam Dương Bắc
15221 on a "152213" PDF artifact and the Hai Phong #VALUE!
05127 row); 3320/3320 legs MOST-correct with a 68/68 stratified
hand sample. HELD: H1 22 wards as 2026 xã→phường conversions
(bundle+vi.wiki 2026 vs MOST+openapi 2025 — live openapi p/24
re-confirms 33/66 vs bundle 45/54 with identical name sets);
H2 05127 (single-sourced from the corrupted MOST row, Nghi
Dương stays unlegged); H3 Hiệp Hoà 2-2 spelling; H4 corrupt
postcode.vn PDF; H5 GeoNames publishes no VN postal dump
(VN.zip 404s); H6 24 provinces single-signal on names. Keeps:
15221→Tam Dương Bắc (3 signals), lam-ong slug as internal key.

## Vanuatu

The bundled `VanuatuGeographyProvider` supplies the 6 provinces as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('VU')` after
countries are seeded.
The 64 area councils and 3 municipalities ship as level-2 areas
under their geographic provinces with HASC codes (Lenakel added
manually as the 2008 third municipality; municipalities are
parented geographically though administratively independent).

Vanuatu has no postcode system. Addresses are formatted per the UPU
layout: street lines, locality, and country.

Revisit 2026-10-03 (fix-and-fill: 63->67 L2; `gate_vu.py` ALL
PASS): the bundled roster followed Statoids' pre-2008 council
list (Statoids' own caveat: "valid as of just before" Lenakel's
2008 municipalization). Four cells updated to the current
VNSO-census roster (citypopulation 2009/2016/2020 tables + UN
OCHA COD-AB v01, 2 fresh signals each): Penama Bangan-Vanua ->
East Ambae and Lungei-Tagaro -> North Maewo (Statoids' own
variant table lists North Maewo/East Ambae as the variants, a
3rd signal; en-wp Penama section agrees, 4th), Shefa Yarsu ->
South Epi (Statoids variant table: "Yarsu: South Epi (variant)",
3rd signal), Tafea Whitesands Tanna -> Whitesands (bare form
per census + COD + the cited WP-Provinces list; Statoids main
still prints the qualified form, outvoted 3-2). Torba's 3
directional rows (Central/Northern/Southern) replaced with the
7 census island councils Gaua, Merelava, Mota, Motalava,
Torres, Ureparapara, Vanua Lava (census + COD agree exactly;
new rows codeless per the Lenakel precedent). HASC codes stay
with their divisions on renames (VU.PM.BV/LT, VU.SE.YA,
VU.TF.WS). Canal-Fanafo hyphen kept: Statoids + bundled (2)
vs spaced COD/CP (2) is a tie, stability holds. Malampa 10/10
and Sanma 10/10 verified unchanged (Sanma incl. Luganville +
North Santo + single South Santo per census + COD). WP's six
uncited boilerplate admin sections are the outlier for Sanma
(Big Bay Coast/Inland, South Santo I/II — no census
existence), Shefa (19-list incl. Emae/Makira-Mataso/Tongoa/
Varsu/Tanvasoko/East Efate/North West Efate — census subsumes
Emae+Makira+Mataso under Makimae, Pele under Nguna), Tafea
(Central/East/South East Tanna — contradicted by WP's own
cited Provinces list + census), and Torba (9-way split —
census merges to 7); do not "fix" toward those sections.

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

### Revisit (B20, 2026-10-06)

Tree HOLD (zero changes); postal 771/901 → 789/930
(`gate_th.py` ALL PASS). L1 78/78 vs ISO 3166-2:TH, L2
928/928 names + counts vs Statoids, 50/50 khet vs
en.wiki; same-L1 Bang Sai pair is a real TIS homonym.
Thailand Post finder DNS-dead, so fixes rest on the
GN + Statoids + Wiki trio, each 2+ signals: 67000 →
Mueang Phetchabun (GN admin1 typo 76 + builder match),
43170 → So Phisai (fuzzy Phon-Phisai match; GN admin1
stale Nong Khai), Surin cluster +13 codes/+17 legs (GN
had zero Surin rows), Bangkok +1 code/+6 legs (6
codeless khet), +5 upcountry legs, 42190 Nong Hin, and
three wrong-code moves (23170 Ko Chang, 42220 Erawan —
killing the only cross-province multi 41220, a GN
artifact — 41280 Wang Sam Mo, Na Yung flipped primary
on 41380). Codeless L2: 31 → 0. Report's "21 legs/934"
corrected to 17/930 (enumeration governs). Holds:
BKK/old-district primacy judgments, H10 rejected
single-signal items, 11 Statoids-side errors,
transliteration policy (Mueang etc.).

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

Revisit 2026-10-06 (B22 worker + integrator; `gate_ph.py` ALL PASS):
bundle is exactly PSGC 1Q2025-vintage — L1 83/83 (82 provinces ISO
exact + NCR), L2 1642/1642 (all 39 WP diffs resolve PSGC-literal in
the bundle's favor), L3 41798/41798 code-join with zero parent
mismatches. Tree: 3 PSGC-literal renames (Araceli + Abra de Ilog
Santo Niño ×2, PPC `Barangay ng mga Mangingisda` — the sole
`Barangay`-prefixed row in 42011, kept because the bundle had
dropped meaning, not just the prefix). Postal 1933→1919: 15 drops
(0905 Manila big-user, 4 old-Cebu artifacts, 4532 stale, 9 retired
DavOro 81xx), 2 relegs (9610/9612 Maguindanao del Norte→del Sur),
1 add (2222 Subic Bay Freeport Zone→Zambales). HELD: H1
forward-delta (1Q2026 San Rafael→Dacanlao merger evidence-ready +
14 renames single-lineage — bundle stays 1Q2025-vintage pending a
vintage-policy decision); H2/H3 sub-office keeps (1045/1820/2333/
2334/5456/4133/6267, GeoNames-single-lineage); H4 L2-remapping
project (all legs point at L1 while codes serve municipalities —
needs a leg-granularity product decision); 8016 Ateneo scope quirk
pinned. NCR legs all within 1000–1899.

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

Revisit 2026-10-05 (B17 worker: VERIFIED-STALE, zero changes;
`gate_kr.py` ALL PASS): bundle is a correct pre-2026-07-01
snapshot — 17 L1 ISO exact (ahead of ISO on Jeonbuk State),
228 L2 with Gunwi->Daegu + Michuhol + special statuses all
correct; postal set == GN Oct-2026 set exactly (34249/34249,
0 multis), 452/452 p3 blocks agree with the en.wiki postal
table, 29/29 Nominatim hits agree. Two effective 2026-07-01
reorgs verified but HELD pending a structural decision: (1)
Gwangju + South Jeolla -> Jeonnam-Gwangju Integrated Special
City (en+ko.wiki + Aju Press 2026-07-01 + Nominatim; new L1
row needs maintainer type vocabulary + code policy — ISO cell
blank); (2) Incheon Jung+Dong abolished -> Jemulpo + Yeongjong,
Seo split into Seohae (renamed) + Geomdan (ko.wiki + Incheon
city official + district articles + Nominatim; 441-code
re-attribution needs Korea Post per-code dong mapping —
tree-only application would orphan 441 legs). Mixed-vintage
application is incoherent (shared effective date); the gate
pins old names PRESENT and new names ABSENT so the held state
stays explicit.

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

### Revisit (B19, 2026-10-05)

Thirty-one mis-parents fixed: 18 Chiayi townships (MOI 10010xxx)
and 13 Hsinchu townships (MOI 10004xxx) moved from city to
county (CYI 20→2, CYQ 0→18, HSZ 16→3, HSQ 0→13;
`gate_tw.py` ALL PASS) — 4 signals per cell (Chunghwa operator
menu js, twzipcode-data npm, en.wiki infoboxes, Wikidata P131).
Postal links clean, zero moves (0/368 twz-diff, 368/368 WD zip
match; 300/600 shared-code primaries kept). Prefix-flag 0/368
is not a defect: L1 ISO alpha-3 vs L2 MOI 8-digit can never
prefix-match; MOI-5-prefix per parent is clean. Holds:
disputed-island codes 290/817/819 correctly unbundled, WD
extras excluded, Alishan type kept.

## Ukraine

The bundled `UkraineGeographyProvider` supplies the 24 oblasts plus
Kyiv, Sevastopol, and the Autonomous Republic of Crimea as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UA')` after countries are seeded.

The 136 post-2020 raions ship as level-2 areas under their oblasts. Oblast names use
the ISO adjectival forms (`Kyivska`, `Lvivska`).

Ukrainian addresses are formatted per the UPU layout: street lines,
locality, oblast, a 5-digit postcode on its own line, and country.

Revisit 2026-10-04 (B15 re-adjudication, 1313 ops: 701 leg drops,
608 leg adds, 4 primary flips; 26674 -> 26581 legs, 92 -> 2 multis;
`gate_ua.py` ALL PASS): tree verified exact — 27 ISO 3166-2:UA L1
(24 oblasts + Kyiv 30 + Sevastopol 40 + Crimea republic) with all
136 post-2020 raions, and the 26579-code set verified clean; every
bundle multi traced to twin-name misattribution in the GN/KATOTTG
join. Worker multi-drop loop re-adjudicated per code by the
integrator against 2020-reform old→new mapping (EN/UK Wikipedia +
VRU 807-IX hromada compositions), 20+ ukwiki village infoboxes
(postcode + raion), and Nominatim reverse on acc=4 rows (acc=1 rows
share junk centroid coords and were discarded): 85 drops applied
(reform-wholly old raion + unique strict anchor; S-uniques proved
twins, e.g. Uspenka-26220, Chervone-30214, Kalytyntsi-30334,
Berezna-30426, Borysiv-31073, Sloboda-31146), 4 specials verified
(Kurylivka 41671 Konotop, Bubnivka 32011 Khmelnytskyi, Holoskiv
32340 Kamianets-Podilskyi, Hrushiv 81016 Yavoriv — all infobox-pc
exact). Two genuine cross-raion codes HELD with both legs: 82563
(Matkiv 82563 Stryi/Koziova + Ivashkivtsi Sambir/Borynia)
and 47431 (Pahinya 47431 Kremenets/Lanivtsi + Karnachivka 47431
Ternopil/Zbarazh). 90124 flipped to Berehove-sole 2026-10-06
(Ukrposhta operator street DB: 1253/1253 Siltse + PCIU + Siltse
infobox + COD-AB; old Irshavskyi SPLIT Kamianska→Berehove, so the
B15 reform-wholly premise was false). All 607 singles moves applied
after a 16/16 stratified sample (8 infobox-pc exact incl. 08411
Boryspil, 78119 Kolomyia, 16262 Novhorod-Siverskyi, 67633
Mizhlymanske; 8 reform+block incl. 27019, 26135, 84423 Lyman
hromada, 08146 near-miss accepted; zero contradictions).
Retry 2026-10-06 found the live Ukrposhta operator API
(index.ukrposhta.ua street-grain + serving-office records) and
closed all 7 village gaps (27019/26135/26230/52029/08146/84423/
64372, operator + GN; infoboxes in 5/7 carry the neighbour's
code); 47431/82563 stay held multis (directory favours sole —
Pakhynia 47428, Ivashkivtsi 82525 — but 1v1 pending a municipal
second signal). Row-error proofs: Kalynove-Borshchuvate true-pc
93279 Alchevsk (voids 93302-Alchevsk; operator-blind, 0 rows),
Mayak-Myrivska true-pc 53524 (53542 is Zoria's; voids
52414-Nikopol — 52414 is Mayak-Solonianska sole per operator),
Luchka-Okhtyrka 42600 vs Luchka-Romny 42547 (voids 42600-Romny).
Held: Berestove-63744 ukwiki infobox pc 32911 still corrupt
(re-verified 2026-10-06 back to Feb-2023; operator confirms 63744
= Berestove/Kupiansk and 32911 has 0 rows operator-wide);
occupied-territory OSM hromada edges treated as weak.

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

Revisit 2026-10-03 (fix-and-fill, 11 link retargets, zero tree/code
changes; `gate_iq.py` ALL PASS): tree verified exact — 18 ISO 3166-2:IQ
codes + HL-provisional Halabja, and all 119 districts match the
Districts-of-Iraq oracle per parent (Makhmur once under Nineveh per the
page's own contest note). Fresh 348-row Mapanet re-pull agrees the code
set exactly. Link adjudication via Nominatim fwd/revgeo with postcode
echoes, Mapanet pins + Arabic labels, and governorate articles: Latifiya
10080→Baghdad (Mahmudiya subdistrict); Qal'at Diza 46016→Sulaymaniyah
(Pshdar) and Koya 46017→Erbil (were swapped); Debca 44015 + Quwair/Gwer
44021→Nineveh (Makhmur district); Sharazor/Penjaween/Said Sadiq
46005/46007/46008→Sulaymaniyah (declined Halabja); Helabcha
46006→Halabja (Halabja town); Suwaira 58012→Wasit and Shafiiyya
58014→Qadisiyyah (were swapped). Held: disputed-territory ties Aqre,
Sheekhan, Kalak at Nineveh; unlocatable Mishtiqa 44016 at Nineveh as an
explicit weak keep. UPU 61102 is PO-box-only (out of scope); 44023 a
single-echo note. Halabja now holds 46006+46018 (both Halabja town).

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

Revisit 2026-10-05 (verify-only, zero changes; `gate_gh.py` ALL
PASS): tree 277/277 — 16 regions ISO GH-AA..WP exact (GH-BA
former, deleted 2019), 261 MMDAs exact vs the en.wiki Districts
table (names + parents + 6/113/142 categories). Citypopulation
agrees modulo mechanical conventions; its 6 category lags
adjudicated for the bundle (Kwabre East / West Gonja / Nandom
canonical-municipal; Jasikan + Krachi West elevated May 2021
L.I. 2437/2418; Obuasi East municipal pre-batch). The Nov-2024
15-MDA executive-approval batch never completed into law: 8/8
probed members (South Tongu, Bole, Anloga, Ningo-Prampram,
Techiman North, Techiman, Gomoa East, Kpone-Katamanso)
confirmed at old status by 2025/2026 MoFEP budgets + assembly
sites; remaining 6 batch members held by pattern. GN ADM2 stale
for GH (pre-split names) — not used. No postal files by design
(GhanaPost GPS is not a postcode system).

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

Revisit 2026-10-05 (verify-plus-one: `Alto Chipaca` → `Alto Chicapa`
name + slug; `gate_ao.py` ALL PASS): 21/21 provinces exact vs
citypopulation (2024 reform + census), geo-ref census-2024, PCGN
factfile (Law 14/24: 21/326/378), and GeoNames ADM1; 325/326
municipalities exact vs citypopulation + geo-ref with zero parent
mismatches (36 citypop dual-name displays pair 1:1 with bundle
singles, e.g. `Tômbwa (Porto Alexandre)`, `Waku Kungo (Cela)`).
The Chicapa spelling carries four signals (citypop INE-1106, geo-ref
LSU-01, GeoNames PPLA2, ANGOP current usage); GeoNames `Chipaca` /
`Tchipaca` are different places in Cuanza-Sul / Malanje, and only a
2001 UN typo agrees with `Chipaca`. ISO 3166-2:AO still lists the
former 18 (PCGN: new-province codes N/A), so `CUA`/`CUB`/`IEB`/`MLE`
stay provisional. Verdict stays none: GeoNames has no AO postal dump
(404) and UPU addressing is codeless.

## Cameroon

The bundled `CameroonGeographyProvider` supplies the 10 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CM')` after
countries are seeded.

The 58 departments ship as level-2 areas under their regions.

Cameroon has no postcode system, so the formatter stacks street
lines, city, and country with no postcode line. Types are labelled
`Région` and `Département`.

Revisit 2026-10-03 (verify-only, zero data changes; `gate_cm.py` ALL
PASS): 10 regions match ISO 3166-2:CM codes exactly (Adamaoua and
North-West/South-West hyphen variants pinned in provider English),
and all 58 departments match the Departments-of-Cameroon oracle per
region. Verdict stays none: the UPU cmrEn profile (07/2002) shows a
codeless B.P. address, the UPU Sep-2025 list carries Cameroon on
do-not-require, and GeoNames has no CM postal dump (404).

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

Revisit 2026-10-04 (B14 verify-only, `gate_mg.py` 0 FAILURES, zero
content changes): the tree matches the WP regions/districts tables
with zero diffs (24 regions including `Ambatosoa` and the Malagasy
`Matsiatra Ambony` form the regions article uses; 114 districts with
identical per-region counts; ISO province codes A/D/F/M/T/U exact),
and GeoNames reconciles exactly (119 ADM2 = bundle 114 minus
Renivohitra plus the 6 Tana arrondissements; remaining GN deltas
are `X District` suffixes, French alternates like `Brickaville` /
`Fenerive Est` / `Port-Berge`, and the stale `Fenoarivobe`).
Postal: 110 codes / 114 legs / 4 multis sealed — all legs district
grain, zero province-block violations, every code exactly one
primary. 74 singles corroborated by their WP district articles; the
4 shared codes sealed by both partners' articles (113 Betafo +
Mandoto, 303 Ambalavao + Lalangina, 305 Ambohimahasoa + Vohibato,
314 Ikalamavony + Isandra); the 102/103 Tana split sealed by 10
Avaradrano commune articles plus directory pages against one stale
district page claiming 102 for Avaradrano; UPU anchors 101/501;
302 stays absent (no Fianarantsoa II district). Areas file
normalized LF/CRLF-mixed → pure CRLF to match codes/links. Held:
~20 Mapanet-only singles (e.g. 207 Nosy Be, 401 Mahajanga I, 602
Toliara II) whose WP articles carry no postcode — kept as
uncontested block-sane singles pending a Paositra list.

Retry 2026-10-06 (zero data changes; `gate_mg.py` 0 FAILURES):
the unsealed set re-derives to 32 codes, of which 29 are now
sealed by WP town/commune articles + OSM calculated postcodes +
addressed usage (WFP "Nosy Be 207", hotel "SAINTE MARIE,515" and
"614 Taolagnaro"); only 111 Antsirabe II, 323 Manandriana, and
606 Ankazoabo-Atsimo stay held (French-list-only, WP fully
exhausted). Lineage correction: Mapanet + youbianku +
postcodebase + 56ok are ONE French-list lineage (shared quirks:
Nord/Sud, Rural/Urban, Morafanobe, Antsihihy typo) — agreement
between them is not a second signal, and B14's youbianku
urban-row corroboration was weak (replaced by the new seals).
Paositra's live site (paositramalagasy.mg) has an official
193-agency map but no postcode table; postcodesdb is dead and
postcode.info MG leaves all 404 (B14's 502/504/508/604 seals no
longer live-verifiable). Single-signal anomalies held for the
record: Bezaha-610 typo claim, Soalara membership conflict,
110-bleed around Antsirabe, Ambohimahazo-223 OSM noise.

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

Revisit 2026-10-03 (fix-and-fill, 12 moves + 2 fills, zero tree
changes; `gate_af.py` ALL PASS): tree verified — 34/34 ISO
3166-2:AF codes, 401/401 COD-AB v03 pcodes byte-exact, Badakhshan 28
+ Kabul 15 exact vs the list page (its 14 deltas are staleness:
Ghor Murghab, Marja/Aqtash/Gul Tepa/Kalbad/Baad Pakh/Mirzaka/Rohani
Baba/Gerda Serai/Abshar folds, Khak-e-Afghan→Kakar, Dand fold,
Islam Qala/Turghandi, Haska Meyna, Khulm/Chinarto gaps).
Live finder re-pull (1,408 zones) × COD-AB polygons: the 6 build
"resolved by location" links were OSM-boundary errors (Qala-e-Naw
+100km, Kiti +25km, Anar Dara +40km, Muqur +25km offsets proven by
transect), corrected to Shindand, Feroz Koh, Baghran, Qaysar (+
Chihil Gazi shrine in-polygon), Waygal (Want Waigal), Jawand
(Allah Yar in-polygon); plus Pul-e-Khumri (Dand Ghuri false
friend), Darwaz-e-Payin, Sar-e-Pul, Sharak-e-Hayratan, Ab Kamari,
Behsud (airport); 186701 Khyber kept Qaysar on a strip tie. Both
"drops" filled (396701 Bahramcha→Deh-e-Shu, 425201 Gizab→Gizab —
both ARE COD-AB districts; the row typo'd 396601 for 396701).
1,408 codes / 1,408 links; 401/401 districts covered; UPU 07/2025
anchors all match.

## Mozambique

The bundled `MozambiqueGeographyProvider` supplies the 10 provinces
plus Maputo City as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MZ')` after countries are seeded.

The 129 districts ship as level-2 areas under their provinces, plus
the 7 municipal districts under Maputo City. `Maputo Province` and
`Maputo City` are disambiguated at seed. Maxixe is excluded (city,
not a district).

The 145-code overlay files Mapanet rows to districts via the
posto→district table (132 of 136 districts; city municipalities and
anomalous codes excluded as admin-separate or anomalous);
Maputo-city codes district-mapped; no new area rows.

Mozambican addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the province on its
own line, and country. Types are labelled `Província`, `Cidade`,
and `Distrito`.

Revisit 2026-10-03 (fix-and-fill, 32 fills + 1 secondary, zero tree
changes; `gate_mz.py` ALL PASS): tree verified — 11/11 ISO 3166-2:MZ
codes, 9 provinces exact vs the Districts-of-Mozambique oracle,
Maputo City's 7 municipal districts exact, Maxixe/Beira/Matola
confirmed city-only. Fresh 436-row/138-cell Mapanet re-pull (build saw
329/100): all 113 kept codes still present; the 32 fills map new cells
to their districts with OSM containment per locality (1304, 1310,
1312, 2106, 2112, 2114, 2309, 2312, 2401, 2402, 2405, 2409, 2413,
2415, 3104, 3107, 3110, 3113, 3115, 3119, 3202, 3205, 3209, 3213,
3215, 3218, 3219, 3302, 3307, 3308, 3310, 3311) plus a Doa secondary
on 2307; the 3 existing multis re-verified (Murrebue ∈ Mecufi,
Quirimba ∈ Ibo). 145 codes / 151 links; 132/136 districts hold
primaries (codeless: Xai-Xai, Mogincual, Nampula rural, KaMaxaquene;
3100 serves city+Rapale, 3111 splits three ways, 1200 mixes city and
Chonguene). 1213 is a Beira-cell anomaly, not Xai-Xai-misfiled.
Backlog: 11 post-2013 districts + Ilha + Inhambane/Maxixe-D need a
primary-source expansion pass (Boletim/INE list).

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

Revisit 2026-10-03 (1 fix: 5-link Oqoltin retarget, zero fills;
`gate_uz.py` ALL PASS): tree verified — 14/14 ISO 3166-2:UZ codes, 163
region tumans exact vs the Districts-of-Uzbekistan oracle (+ 12 Tashkent
districts), 31/31 regional cities exact vs the ru.wiki SOATO-sourced
admin-division page (de.wiki's 26-star count is stale). Postal 2140/2140
vs Postcodebase full crawl (183 zones, 2963 rows) + MITC decree draft
IHL-1909/22-2 (1886 codes) + Mapanet re-crawl (2795 codes): 2137/2140
corroborated, 29/30 sampled attributions agree (1 Mapanet-only Xovos
trio kept as block-coherent). Fix: 120501–120505 Sardoba-t → Oqoltin-t
(decree Sardoba PAB scope + OSM: Sardoba town sits in Oqoltin-t).
Known-absent 140101 (UPU + decree + PCB) stays out pending the fill pass.
71 L2 codeless post-fix (12 Tashkent tumans by design). Backlog for a
dedicated fill pass: 466 PCB+decree codes (`uz-fill-inventory.json`,
zone→tuman attribution incl. city splits) + 171 decree hub mains
(`uz-hub-inventory.json`); PCB-only 385 (single directory family, the
draft decree is branch-incomplete) and Shirin/G'ozg'on/Zarafshon-city,
Ko'kdala, Bo'zatov gaps need official adjudication. Overlay-row errata:
max code is 231620 (not 230912); "Mehnatobod" is a Xovos/Mirzaobod
village, the Sirdaryo city gap is Shirin.

## Myanmar

The bundled `MyanmarGeographyProvider` supplies the 7 regions, 7
states, and Naypyidaw as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MM')` after countries are seeded.
The 126 districts ship as level-2 areas (121 GAD districts from
the April 2022 expansion plus the 5 self-administered-zone rows
kept as district-typed L2 under their states). Each row carries
a p-code (`MMR016001`-style, MIMU convention minus `D`) and an
explicit parent. MIMU splits Bago into East/West and Shan into
East/North/South (18 admin-1 units); those split parents are
rolled up into the ISO `Bago` and `Shan` states.
Townships are not bundled.

Revisit 2026-10-04 (B12 tree rebuild: 80 -> 126 L2, 5 deletes +
2 renames + 51 adds; `gate_mm.py` GATE PASS). This reverses the
standing "never operationalized" decision: the Sep/Oct 2024
national census enumerated under the new districts
(citypopulation/geo-ref.net carry Census 2024-09-30 populations
per new district, e.g. Ahlon 229,600, Botahtaung 469,489), which
is operational reality, plus the MOI Notifications 319-333/2022
legal act, the MOI 2 May 2022 announcement table (75 originals +
46 expansion, per-state name lists, MITV-corroborated), and
matching en.wiki/my.wiki rosters. MIMU PCode v9.7 (Jan 2026)
still ships the pre-2022 75-district roster (verified first-hand:
75 GAD-Active + inactives; its May-2023 change log never mentions
the expansion), so MIMU is now the outlier — retained only as the
house standard for English spellings and SAZ parentage. Deleted:
Mandalay District (suppressed per WP; MOI continuation framing
overruled) and the 4 old Yangon quadrant districts (split with no
oracle statement of continuation); retired codes MMR010001 and
MMR013001-004 are never reused. Renamed in place (name cell only;
GAD lists them as originals under current spellings): Oke Ta Ra
-> Ottara, Det Khi Na -> Dekkhina (deliberate slug/name
divergence, pinned by gate + test). Added 51 rows in en.wiki-table
order per parent (Mandalay 11 incl. Amarapura; Yangon 14;
Shan 25 incl. 4 SAZ + Hopang/Matman kept under Shan, WP's
separate Wa-SAD section outvoted 2-1; Sagaing 14 incl. Naga SAZ).
New-row codes are worker-assigned continuations of each MIMU
state-group sequence, NOT MIMU-issued (MIMU has not adopted the
expansion); Bago-East/West and Shan-South/North/East groups
assigned via WP split-from notes. Spelling rule: bundled/MIMU
form stands unless two oracles concur against it (11 WP variants
rejected incl. Ma-ubin, Putao, Bawlakhe, Pa'O; adopted: Chipwi,
Demoso, Mese, Kyain Seikgyi, Tedim, Homalin, Ye-U, Bokpyin,
Aunglan, Chauk, Kyaikto, Ye, Ann, Taungup, Hlegu, Hmawbi,
Kyauktada, Ahlon, Kamayut, Mayangon, Botahtaung, Dagon Myothit,
Twantay, Kalaw, Kutkai, Monghsu, Mongla, Mongton, Mong Yang,
Mongyawng, Nansang, Tangyan, Kyonpyaw, Myanaung, Zeyathiri,
Pyinmana, Amarapura, Aungmyethazan, Maha Aungmye, Tada-U,
Thabeikkyin, Insein, Thingangyun, Thanlyin, Mingaladon, Taikkyi).
Postal stance holds (no overlay built): UPU mmrEn 11/2022 still
current (7-digit, quarter/village-tract level, no allocation
table); GeoNames MM re-fetched (141 junk rows, zero postcodes);
the Zenonia-9 odoo 943-code 5-digit set evaluated and REJECTED
(single unprovenanced signal, 291/330+ townships, no
township->new-district crosswalk — shipping office-level codes
rolled up to districts would misrepresent precision). Needs: a
public township-level allocation list with provenance plus a
township->district crosswalk.

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

Revisit 2026-10-05 (5-leg Samraong collision fix; `gate_kh.py`
ALL PASS): tree 235/235 — L1 codes 1-25 ISO-exact, all 210
L2 exact (codes + names + types + parents) vs the en.wiki
NIS-sourced district list. 240401-05 moved Takeo Samraong
district (2107) -> OM Samraong municipality (2204): the
only 5 legs failing the postcode=NIS+22/23/24-remap
district-part audit (1628/1633 fit), COD-AB 2018 confirms
NIS 220401-05 in district 2204. Postal: 1537/1633 communes
overlap COD-AB 2018; the 96 post-2018 creations sit in the
14 documented new districts (Bokor 070901-03 + Kamboul
121401-07 CPC-transcription exact). Ou Krasar gap is
230102/220102 (COD-AB: Ou Krasar=230203 in Kaeb) —
overlay note corrected. Holds: Chrouy-vs-Chroy Changvar
(en.wiki split), Ratanakiri single-k (en.wiki; COD-AB
double-k).

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

Revisit 2026-10-05 (B15 tree + postal fixes; `gate_la.py` 0
FAILURES): tree 147/148 admin codes join COD-AB exactly. Meun is
10-13 (COD-AB LA1013 + 2015 census usid 1013; 10-11/10-12 are
retired Xaisomboun-split codes, and the wiki table's 10-11 is
positional renumbering, so it is not evidence). Savannakhet 13-10
is Xonbuly (the bundled Xonaboury was a piped-link display
artifact nobody prints; LSB + citypopulation + EPL Lao
ຊົນນະບູລີ agree), 13-14 is Xayphoothong (LSB 3 channels +
citypopulation + the province article + EPL Lao ໄຊພູທອງ; the
Districts-table Xonboury is stale), 13-15 is Phalanxay (LSB +
citypopulation + EPL Lao ພະລານໄຊ over the wiki Latin prefix).
Xaisomboun rotates to Thathom 18-02 / Longchaeng 18-03 / Longxan
18-05 (census usid + uuid geocodes + B. Longcheng / B. Samthong
village ground truth + LSB code-ordered tables + the Special Zone
18-02 history; COD-AB's 1803/1805 NAMES are swapped, its
geometries are right). Vientiane-prefecture 1-08/1-09 drop the
`district` qualifier (146:2 bundle convention + COD-AB/Mapanet
clean forms). All other spelling diffs hold as common-English
transliteration (Et, Mok May, Sainyabuli-8-01 matching its L1,
landmark Luang Prabang, Hinhurp, Hom, Yot Ou, and spacing-only
pairs — each matches at least one external source). Postal moves
to 26 codes / 148 legs on EPL-official evidence (7,781 village
rows via the operator API): Champasak zone 16010 -> 16000
(EPL: Pakse district 16000, 16010 is the Champasack-district
office, so 16010 misroutes 9 districts; Mapanet's all-16010 is
structurally contradicted), Xaisomboun leaves 10000 for its own
18000 block (Anouvong primary). VTE sub-zones verify 9/9
against Mapanet and hold (EPL Vientiane blocks overlap
districts, so they cannot re-anchor district legs). Luang
Namtha holds 03000 (Mapanet-affirmative; EPL's 03001 is a thin
2-row sub-code). District office blocks (EPL PP0D0 series) stay
below bundle grain: noted but unmapped, like the 11060/11070
Bolikhamxai zones and the Km-52/special entries.

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

Revisit 2026-10-04 (areas verify-only, zero changes;
`gate_tl.py` ALL PASS): 13/13 ISO 3166-2:TL codes + provisional
AT for Atauro (ISO still unassigned as of this pass, so the
provisional code stands); 67/67 admin posts with exact
per-municipality counts (4/4/8/6/7/5/5/5/4/6/4/5/4) and names vs
the en-wp posts table — incl. the 3 posts created 2024-01-01
(Loes, Quelicai Antiga, Matebian per Tatoli + Diploma
Ministerial 40/2023) and Atauro correctly childless (WP row
empty). WP's "70 posts" lede is stale: its own table lists 67.
GeoNames TL dump (13 ADM1 + 65 ADM2) confirms the 62 unchanged
cells and dates the bundled deltas (Atauro 2022 split + 3×
2024 posts). Postal stays admin-ready: UPU Aug-2026 lists the
TL+5 system live (require-list, ISO-prefix Yes, length 7,
format TL99999) but no public allocation table exists (prior
2026-09-25 recheck stands); GeoNames TL.zip 404.

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

Revisited (2026-10-04): fix-and-fill — 2 fills + 1 retarget
(779→781 codes/links; `gate_am.py` ALL PASS, pre-fix FAILs on
exactly the counts + 3 pins). Tree verify-only: 70
municipalities exact vs citypopulation (q/k + j/ch romanization
variants only) 8/5/8/5/11/11/6/7/4/5 + 12 Yerevan districts; WP
Armavir "7" lede is stale (its table lists 8 incl. Khoy, mtad.am
confirms 8). Fills from live Haypost postalIndex re-pull (780
records): 0109→Kentron (branch opened 2026-09-30, Arshakunyats
18/4; Photon + Nominatim both Kentron) and 0236→Ashtarak (branch
opened 2026-09-28, Postmobil unit, city Artashavan; WP +
Nominatim both Ashtarak community; junk 1,6 coords are a Haypost
data error in the 3014/4112 class — both precedents bundled).
Retarget: 3518 Vaghatin Goris→Sisian (mtad.am Sisian settlement
list + Nominatim; Goris page lacks it). Keep 2102 Tashir (Haypost
API dropped it but Spyur branch directory + OSM HayPost-2102 POI
show Tashir post office No.2 live → stability). New Haypost city
typo recorded: 0919 "v. Artashat" = Artashar (branch coords
reverse-geocode Artashar/Metsamor on both engines; no Artashat
village in any Armavir settlement list) so Metsamor holds — same
class as Doxs→Doghs/Xursal→Ghursal/Mexri→Meghri. 30-link sample
29/30 correct; mtad.am settlement lists used as adjudicator
throughout (OSM community boundaries are stale for post-2021
splits: Khoy villages still show Vagharshapat, Aygehovit shows
Berd — both bundled attributions vindicated by mtad).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_az.py` ALL
PASS): the 78-code L1 tree re-verified byte-equal to ISO 3166-2:AZ
(NX + 66 rayons + 11 cities, incl. CAB/CAL/CUL/QOB/GYG/UCA/XAC/XIZ/
XCI/XVD); display names kept as conventional spellings (Agdam/Aghdam,
Agstafa/Aghstafa, Yardymli/Yardimli, Q-forms per WP titles). 685 SSC
local municipalities re-checked (Baku 45 across 12 rayon prefixes,
eponymous city splits, 9 suffixed rows, 8 childless
liberated-territory L1). The 1186-code / 1186-link L1 overlay
re-verified clean: code set identical to the live GeoNames AZ dump
both ways, clean 100-block grain (Baku 10+11; 42/55/66 city splits
hold NN00+eponym+N Sayli), 12 Nominatim reverses + gomap.az sightings
+ UPU anchors all agree. Coverage stays 68/78: the Nakhchivan exclave
(UPU AZ6715 Babek, AZ7000 city, AZ7303 Sadarak attested) and Jabrayil
AZ1400 (gomap/gun.az/b2bhint) are real-world codes missing from
GeoNames too — documented gap, Azerpost index needed for any fill.

## Bhutan

The bundled `BhutanGeographyProvider` supplies the 20 dzongkhags as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BT')` after
countries are seeded. The 205 gewogs ship as level-2 areas under their districts.

Bhutanese addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the dzongkhag on its
own line when it differs, and country.

Revisit 2026-10-05 (fix: 2 district renames, zero count
changes — 225 areas / 38 codes / 38 legs; `gate_bt.py` ALL
PASS): tree verified — 20/20 ISO codes, 205/205 gewog names
+ parents exact vs the WP Gewogs list, per-district counts
exact. Renames Chukha→Chhukha + Lhuntse→Lhuentse (ISO +
WP + chhukha.gov.bt / lhuentse.gov.bt; slugs renamed too,
25 rows). Keeps: Mongar + Pemagatshel (district govs +
WP beat ISO Monggar / Pema Gatshel), Trashi Yangtse (ISO
+ bundle beat WP Trashiyangtse). Postal: 38 office base
codes at district level by design; 37/37 youbianku-listed
codes exist with matching attribution incl. cross-block
21104 Lhamoizingkha→Dagana; 11/38 OSM hits all match.
36001 Tsirang kept on operator provenance + HQ pattern
(youbianku has no Tsirang section; Bhutan Post legacy
finder 404, UPU BTN.pdf redirects home).

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

### Revisit (B20, 2026-10-06)

Key finding: the bundle was a 1:1 GeoNames derivation, so GN
agreement proves nothing — verification rests on the official
Cyprus Post directory xlsx (34k street rows + 757 communities)
+ live finder AJAX + wiki district lists. Postal 1125/1127 →
1132/1135, L2 752 → 755 (`gate_cy.py` ALL PASS): +8 codes
(1000/3014/5000/6029/8203/8204/8652/8653, +3 L2 anchors
incl. Ammochostos city), spurious 5720 dropped + L2 dedup
(GN double-rowed one Agios Georgios) + 5520 repoint,
Fylousa pair unswapped (8629 Kelokedaron / 8811
Chrysochous), 4528 primary → Pentakomo (Kyverniti is not
a community), 1025 second leg Omorfita (+L2), and 5
renames incl. the U+03BF homoglyph Kato Zοdia → Kato
Zodeia (+slug), Pano Zodeia, Tremetousia, Komi Kebir,
Tziaos. Policy recorded: RoC 4-digit system island-wide
incl. the north (like S1); TRNC 5-digit out of scope;
392 POB-only codes correctly excluded. Holds: 9
community-only codes valid, name variants kept, 5
further multi-community codes single-signal (not added).

## Georgia

The bundled `GeorgiaGeographyProvider` supplies the 9 regions plus
the Abkhazia and Adjara autonomous republics and Tbilisi as `State`
rows and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GE')` after countries are seeded.
The 64 municipalities, 17 districts and 4 self-governing cities ship as level-2 areas under their regions, republics and Tbilisi. The level-2 cities share the `municipality` assignment role with municipalities and districts; Tbilisi stays on the level-1 `city` role.

Georgian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, the region on its
own line when both are set, and country. The `region` type is
labelled `Mkhare`.

Revisit 2026-10-04 (fix: Gali retyped municipality→district;
`gate_ge.py` ALL PASS): tree re-verified against the Organic Law on
Local Self-Government (matsne.gov.ge), the municipality register
table, and ISO 3166-2:GE (12/12 codes, names, categories). The law
registers only Akhalgori, Eredvi, Kurta, Tighva and Azhara in the
occupied territories, so Gali matches its five pre-2006 Abkhaz
district siblings (post-state: 64 municipalities + 17 districts + 4
L2 cities + Tbilisi). All 74 codes / 83 links re-verified against
~370 live gpost.ge finder queries (~1,400 cards): every rural
district filing carries exactly its bundled code, Tbilisi's 8 codes
attach to villages/settlements (0167 = Mukhiani-2 settlement
block), and both multi-leg codes stand (6600 Sokhumi-primary ×6,
7300 Akhalgori-primary ×5). Zero link moves, zero fills. Held
deliberately: Azhara linkless (its Kodori villages carry 6600 but
file under Gulripshi with no second signal), pure-urban street
codes out of scope (incl. observed Kutaisi 4600/4602 and the
Tbilisi 01xx street space), Tkvarcheli/Tskhinvali de-facto
districts absent per the formal scheme. Directory mirrors rejected:
no GeoNames GE postal dump (404), worldpostalcode Georgia page
broken, zipcode.com.ng village sub-codes contradicted by live gpost
on every sampled village.

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

Revisit 2026-10-05 (2 changes: San Juan de Flores -> Cantarranas
incl. slug, Taulabe -> Taulabé; zero leg moves; `gate_hn.py` ALL
PASS): tree 316/316 — 18 departments ISO HN-AT..YO exact, 298
municipalities dept-aware exact vs the es.wiki annex as
299/299 with the San Juan de Flores/Cantarranas pair plus GN
ADM2's full 298-set vote (Wampusirpi kept per en.wiki + GN
over annex Wampusirpe; Taulabé accented per GN + en.wiki).
Rename signals: es.wiki Cantarranas (municipio) + OSM
municipality boundary + en.wiki. Keeps: Ocotepeque muni
(annex + OSM; Nueva Ocotepeque is the city name), San Pedro
(annex + OSM boundary; GN long form is an outlier), Saba /
San Francisco de la Paz / Texiguat bare (annex over GN
'Municipio de' prefixes). Postal: 37/37 GN codes bundled
with 37/37 department agreement (12101 dual Comayagua-p +
FM-s; GN twin-city confirms); 47 Mapanet-only kept (18
prefix blocks zero splits); 11000/12000/31000/33000/41000
correctly absent (no operator evidence). Holds: Ocotepeque
vs Nueva Ocotepeque long forms, San Pedro canonical long
form, GN San Miguelito pair.

## Hong Kong

The bundled `HongKongGeographyProvider` supplies the 18 districts as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('HK')` after
countries are seeded.

Hong Kong has no postcode system. Addresses are formatted per the UPU
layout: street lines, the district, and country; any supplied code
prints on its own line for form-compatibility.

Revisit 2026-10-03 (verify-only, zero changes; `gate_hk.py` ALL
PASS): 18/18 districts exact vs Districts of Hong Kong +
Statoids (no ISO 3166-2:HK subdivisions; H/K/N codes synthetic
with HKI 4 / Kowloon 5 / NT 9 grouping). No postcode system
(UPU hkg examples carry no postcode section); GeoNames 999077
is mainland-CN-assigned and stays excluded.

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

Revisit 2026-10-03 (fix: 96914 South Khorasan → Razavi Khorasan
+ 97716 Razavi secondary, zero tree changes; `gate_ir.py` ALL
PASS): tree verified — 31/31 ISO 3166-2:IR codes and formal
names (IR-09 is Khorasan-e Razavi; the wiki "Central Khorasan"
gloss is unofficial), 429/429 county names + parents +
per-province counts exact vs COD v01. Fresh 364-row/109-code
Mapanet re-pull is code-set-identical to the build. 96914 moves
on 4 signals (5 unanimous Gonabad-area rows, Photon 5/5
Razavi/Gonabad, wiki county membership, addressed "Gonabad
96914" usage); 97716 keeps its South primary 3v2 (Ferdows) with
a Razavi secondary for the Gazi/Jazin rows (Jazin RD,
Bajestan). The 45617 dual keeps Qazvin primary + Zanjan
secondary (Magan/Mahin re-filed to Tarom-e Sofla, Qazvin).
109 codes / 111 links; 30/31 provinces covered (Alborz
codeless: no Mapanet r1). Scope: bundled codes are 5-digit
prefixes of the live 10-digit system (UPU IRN profile); full
allocation needs Iran Post (post.ir unreachable).

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

Revisit 2026-10-03 (fix: 9 Almaty districts reparented city→region
+ 070209 Tarbagatai E.Kazakhstan→Abai; `gate_kz.py` ALL PASS): L1
re-verified against ISO 3166-2:KZ (20/20, 2022 numeric scheme)
and L2 against Districts of Kazakhstan (170/170 names + parents,
per-region counts exact) — but by source_id the 9 Almaty-region
audandar (Balkhash, Enbekshikazakh, Ile, Karasay, Kegen,
Raiymbek, Talgar, Uygur, Zhambyl) were parented to Almaty city;
a name-keyed diff cannot see this because region and city share
a name. GeoNames ADM2 rows (admin1 01 Almaty Oblysy, not 02
Almaty city) confirm the region. All 2,525 codes re-checked
against a fresh full worldpostalcode scrape (4,172 codes, 185
town pages): every shipped code but the 14 build corrections is
confirmed, and WPC carries the stale 1218xx/131309 forms behind
those corrections. Seam attribution checked town-by-town
(554 agree): the Samar 0710 split holds (Samarskoe/Palattsy/
Novotimofeevka are Samar-district villages; the WPC kokpekti
page overreaches), Martobe 160818 stays Shymkent (OSM
reverse-geocode: Karatau district, KZ-79), and all five
Nominatim adjudications hold (Marinogorka 071005 is Abai —
overlay wording fixed). 070209 was the lone E.Kazakhstan
singleton inside the Ayagoz 0702 run (GN village/district name
collision); it joins Abai. The 589 WPC artefact-series codes
(department names: Dekretniki, Bukhgalteriya, …) corroborate
the 70/71/72/79 drop. Gaps (single-signal, not filled):
~1,070 WPC-only codes with real-looking places, incl. Shymkent
city 1600xx and Zhezkazgan 10060x / Satpaev 10130x / Zhezdy
101508 — Missing-not-invalid, awaiting a re-pull pass.

## Kyrgyzstan

The bundled `KyrgyzstanGeographyProvider` supplies the 7 regions plus
Bishkek and Osh as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('KG')` after countries are seeded.
The 44 districts ship as level-2 areas under their regions.

The Osh region and Osh city share a name by design; filter by type.

Revisit 2026-10-03 (fix-and-fill: Aitmatov rename + 11 retargets +
26 fills, 893 → 919 codes/links; `gate_kg.py` ALL PASS): tree
re-verified against Districts of Kyrgyzstan (44 districts, parents
and diacritic spellings all match) with one law-enacted rename —
Kara-Buura → Aitmatov (Law KR 2023-04-10 No. 82; name and slug both
move, 13 links follow). 720900–720910 retargeted Suzak → region:
the block is Jalal-Abad city 720900–720909 plus city-admin
Kachkynchy 720910 (Mapanet City leaf, city archive, Kyrgyz Post hub
filing). +7 Toguz-Toro 721500–721506 and +19 Toktogul
721601–721614/721617–721621 from two new Mapanet leaves (Kazarman
721500 triple-corroborated: archive + OSM + branches).
721000–721003 reinterpreted as Kara-Köl at region, not Jalal-Abad
city. Seat anchors held (Tokmok 724200/724915, Naryn 722900, Gulcha
723000, Kara-Suu 723300, Daroot-Korgon 723700). Jalal-Abad city was
renamed Manas in September 2025 (region unchanged, codes unchanged);
no CSV row carries the city name. Open gaps: Talas city code unknown
(724200 stays Tokmok); 722620 At-Bashy two-signal only, left out;
721619 Ustasai Mapanet-only, pinned weak.

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

The 688-code overlay files Mapanet rows to caza via per-point
reverse-geocode (Beirut links at L1, no caza; 16 zero rows
adjudicated, 10 ties + 3 row-majority multis, Hazmiyeh keeps its
Mapanet-published `1107-2090` row under Baabda); spaced/compact
8-digit sector suffixes strip to the base 4-digit code at lookup;
no new area rows.

Revisit 2026-10-03 (gate_lb.py ALL PASS, 688 codes / 701 links):
tree re-verified clean against Districts of Lebanon (9 governorates
+ 25 cazas, KJ still provisional per ISO 3166-2:LB); +5 Hermel fill
codes (8123, 8128, 8151, 8173, 8242 Hermel city — Mapanet + Photon
+ 56ok + GeoNames adm1=11 unanimous); 18 links retargeted on
56ok-directory + Photon + wiki-oracle + GeoNames-governorate
agreement (3018/3514 Tripoli→Miniyeh-Danniyeh, 4215 Byblos→Batroun,
4362/4384 Batroun→Byblos, 5649 Baabda→Chouf, 6642/6710/7150→Jezzine,
6851/6875/6893→Tyre, 7121/7192→Nabatieh, 1835→Zahlé, 1855→Rashaya,
3769/3911→Bsharri); +2 secondaries (5428 Chouf, 8119 Hermel).
Tripoli caza is codeless in every source (Mapanet Trablous/Mina
rows all empty, no 56ok Tripoli cards) — needs a LibanPost fill.
4143/5203 primaries stay per stability despite fresh Baabda/Koura
leans. 3868 keeps its original-build Bsharri leg (no fresh support).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_mv.py` ALL PASS):
tree verified — 18 atolls + 5 cities match ISO 3166-2:MV (minus retired
Gnaviyani/29, plus FVM/KUH/THD city codes) and the Decentralization Act
city list (Thinadhoo 5th city 2023); 192/192 island names match the
Wikipedia inhabited-island lists per atoll. Full Postcodebase re-crawl
(20 atoll pages, 295 rows): 156/156 inhabited matches agree, zero code
diffs; 56ok + worldpostalcode corroborate. The UPU mdvEn (2004) prefix
table is stale (ADh 10..S 20); the shipped live scheme (ADh 00..S 19)
is confirmed. 199 codes / 202 links; multis 05020 (genuinely shared
Inguraidhoo/Vaadhoo — tie, Inguraidhoo primary kept), 02110 + 17100
city secondaries. Codeless 4 stand: Malé + Villimalé street ranges and
the Ookolhufinolhu resort by design; Dhuvaafaru absent in every source
(needs Maldives Post confirmation — their Kadhonlhudhoo 05070 is the old
uninhabited island, not the resettlement island).

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

### Revisit (B18, 2026-10-05)

Tree verified; 4 display-label renames applied (GeoNames +
Mongol Shuudan + worldpostalcode, 2+ signals each;
`gate_mn.py` ALL PASS): `Eg` → `Batshireet` (Khentii),
`Bayanuur` → `Bayannuur` (Bulgan, namespaced slug),
`Jargalant (Khovd city)` → `Jargalant` + `Khovd (sum)` →
`Khovd` (Khovd, namespaced slugs). Postal 39/39 clean, no
changes.

## Nepal

The bundled `NepalGeographyProvider` supplies the 7 federal provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NP')` after
countries are seeded. The 77 districts ship as level-2 areas under their provinces.

Nepali addresses are formatted per the UPU layout: street lines,
`{locality} {postcode}` with a 5-digit postcode, the province on its
own line when both are set, and country. Types are labelled
`Pradesh` and `Jilla`.

Revisited (2026-10-04): verify-only — tree 7 provinces (ISO P1–P7)
+ 77 districts exact vs WP per-province roster with the federal
splits (Parasi-as-Nawalparasi-West + Nawalpur, Eastern + Western
Rukum) 14/8/13/11/12/10/9. Postal 753/753 exact vs the OFFICIAL
GPO table (gpo.gov.np postal-code page: 753 rows, per-prefix code
sets identical, per-prefix counts identical, transliterations
match incl. Nawalpur=east / Nawalparasi=west). 2025 federal
palika system confirmed live (WP: 1991 system superseded);
postcodenepal.com district pages agree (6/6 spot blocks) but the
site's bare slugs now serve the index — district pages live under
`/postal-code-of-<district>/`. Gate
`docs/agents/audit/gate_np.py` pins all 77 prefix blocks.

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

Revisit 2026-10-04 (verify-only, zero changes; `gate_kp.py` ALL
PASS): 13/13 L1 units match ISO 3166-2:KP on code + name + kind
(01–10 plus 13 Rason / 14 Nampo / 15 Kaesong special cities, 11/12
unassigned); 179/179 districts match COD-AB v01 (valid 2019-06-24,
re-downloaded from HDX) on pcode + name + parent with per-parent
counts exact (Nampo 6, Pyongyang 3, Kaesong/Rason childless). The
Dec-2019 Samjiyon county→city upgrade is grain-invisible (bare
name `Samjiyon` KP0107 kept). Postal files stay absent — North
Korea has no postcode system.

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

Verified 2026-10-03 (verify-only, no data changes; `gate_ps.py` ALL PASS):
the 16 governorate names and ISO 3166-2:PS area codes match the OCHA
COD-AB `cod-ab-pse` gazetteer and the ISO subdivision list, modulo the
`Jerusalem (Quds)` display name and the `Ramallah` shortening of
`Ramallah and al-Bireh`. The live Ministry table still holds 755
locality/code rows and 603 distinct P3 codes with per-governorate sets
identical to the bundled links and no cross-governorate code; every
code falls within its Instruction No. 1/2022 Article 5 range. A full
Mapanet pull the same day (866 rows, 603 distinct codes) is
set-identical to the bundled overlay; its only cross-governorate code
is P149, where two Jerusalem rows (Beit Safafa, Sharafat) outvote the
lone Al Walaja/Bethlehem row, agreeing with the ministry and legal
verdict. Gaza governorates hold 4–7 codes each (North Gaza 5, Gaza 7,
Deir El Balah 7, Khan Yunis 5, Rafah 4); no Gaza-side gaps were filled
because the Ministry table is the allocation authority and it lists no
further codes.

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_sy.py` ALL
PASS): all 14 provinces match ISO 3166-2:SY names and codes, and all
66 districts match the Districts-of-Syria oracle per governorate
(Aleppo 10, Rif Dimashq 10, Homs 7 including Taldou, Hama 5, Tartus
5, Al-Hasakah 5, Idlib 5, Latakia 4, Deir ez-Zor 3, Al-Raqqah 3,
Daraa 3, As-Suwayda 3, Quneitra 2, Damascus 1). Note: the oracle
page intro still says "65 districts" but its own per-governorate
lists total 66 (Taldou, created 2010 under Homs, never added to the
intro count). Verdict stays none: the UPU syrEn profile (09/2004)
states the postcode system is still developing, the UPU Sep-2025
list carries Syria on do-not-require, GeoNames has no SY postal dump
(404), and the List of postal codes reads "no codes ... Status
unknown".

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

Revisit 2026-10-03 (verify-only-tree, zero data changes;
`gate_tj.py` ALL PASS): the tree matches ISO 3166-2:TJ (DU/GB/KT/RA/SU)
and the Districts-of-Tajikistan oracle (rev 2026-09-30) 69/69 on
names, types, and parents, with all post-Soviet renames applied
(Sughd, Spitamen, Devashtich, Istiqlol, Bokhtar, Levakant,
Jaloliddin Balkhi, Shamsiddin Shohin, Lakhsh, Rasht, Rudaki,
Vahdat, Dusti, Kushoniyon, Nosiri Khusrav, Hamadoni). Deliberate
deviation: `Roshtqal'a` follows the district article title and
native spelling over the oracle table's `Roshtqala`. No postal
overlay ships: the live 6-digit system (Tajik Post index, 390 rows /
336 codes, modified 2025-09-16) is truncated mid-Khatlon — 22/69 L2
codeless including all 4 Dushanbe districts — with 14 shared codes,
7 conflicted seats, and all locality codes single-signal; no second
directory exists (Mapanet paywalled, GeoNames 404, youbianku stub,
worldpostalcode 404). Scope stays `expansion`. Unblock: Tajik Post
completes/restores the index, a live office finder appears,
addressed sightings resolve the 7 conflicted seats, or a second
directory emerges.

## Turkmenistan

The bundled `TurkmenistanGeographyProvider` supplies the 5 regions
plus Ashgabat as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TM')` after countries are seeded.
The 58 districts ship as level-2 areas under their regions.

Turkmen addresses are formatted per the UPU layout: street lines,
the locality, the region when it differs, the 6-digit postcode on
its own line, and country.

Revisit 2026-10-03 (2 cells only, no count changes; `gate_tm.py` ALL
PASS): two city rows carried literal wiki-bold markup
(`'''Mary'''`, `'''Türkmenbaşy'''`, dropped comparison-breaking
boldface to match the Baýramaly pair). All 6 level-1 rows match ISO
3166-2:TM names and codes, and all 58 districts match the
Districts-of-Turkmenistan oracle per region, with the post-2022
Arkadag district under Ahal and Hazar absorbed into Balkanabat city.
The 49-code set was re-pulled from Mapanet (267 locality rows, exact
match) and every code re-attributed with full Nominatim
re-geocoding: 20 shared codes stay as adjudicated (744000
Ashgabat-general quad-linked to 4 boroughs with Berkararlyk
primary; 745420 Sakarçäge/Oguzhan/Mary triple;
746632 Halaç/Hojambaz/Saýat triple). Awaza stays as a real 2013
borough of Türkmenbaşy city at district level. Scope stays
`complete`.

## Yemen

The bundled `YemenGeographyProvider` supplies the 21 governorates
plus Amanat Al Asimah (the Sanaa municipality) as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('YE')` after countries are seeded.
The 333 districts ship as level-2 areas under their governorates.

Yemen has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, the governorate when it differs,
and country; any supplied code prints on its own line.

Revisit 2026-10-03 (2 cells: `Al  Hawtah`/`Al  Makha` double-space
typos fixed to single spaces per the canonical district articles;
`gate_ye.py` ALL PASS): tree verified exact — all 22 ISO 3166-2:YE
codes (SA municipality, SU Socotra) and all 333 districts match the
List-of-districts-of-Yemen oracle per parent (wiki " district" link
suffix stripped). Names are ASCII transliterations of the ISO
BGN/PCGN forms. Codeless confirmed four ways: UPU yemEn (03/2005)
has no postcode section (B.P.-box addressing), the Sep-2025 UPU list
carries Yemen on do-not-require, GeoNames ships no YE postal dump
(404), and the List of postal codes says "no codes" (the 2014 Sanaa
geocoding pilot is not a postcode system).

## Benin

The bundled `BeninGeographyProvider` supplies the 12 departments
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BJ')` after
countries are seeded.
The 77 communes ship as level-2 areas under their departments (9
Atakora, 9 Ouémé, 9 Zou, 8 Atlantique, 8 Borgou, 6 Alibori, 6
Collines, 6 Kouffo, 6 Mono, 5 Plateau, 4 Donga, 1 Littoral).

Benin has no postcode system: UPU benEn (11/2025) shows P.O.-box-only
addressing, the Sep-2025 UPU list carries Benin on do-not-require,
and GeoNames ships no BJ postal dump. The 2-digit Cotonou /
Porto-Novo / Abomey / Parakou delivery-office prefixes are office
codes, not postcodes, and stay out of scope. Addresses are formatted
per the UPU layout: P.O. box lines, the locality, and country; any
supplied code prints on its own line. Types are labelled `Département`
and `Commune`.

Revisit 2026-10-03 (5 spelling fixes, `gate_bj.py` ALL PASS):
`Pehonko` → `Péhunco` (CONAFIL audits 2013–2023 + fr.wiki
canonical), `Cové` → `Covè` and `Zangnanado` → `Zagnanado`
(2015-596 Zou decree + INSAE RGPH4 + fr.wiki canonicals),
`Segbana` → `Ségbana` (INSAE RGPH4 + CONAFIL + en.wiki canonical;
fr.wiki keeps unaccented), `Porto Novo` → `Porto-Novo` (UPU benEn).
Department names keep the common spellings `Atakora` / `Kouffo`
over ISO `Atacora` / `Couffo`. Open: `Comé` vs `Comè` contested
(Mairie letterhead acute vs INSAE-prose/fr.wiki grave); CSV keeps
`Comé` pending the Mono decree text or Loi 97-028 schedule.

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

Spelling evidence (Oct 2026, M3 re-verified): `Koosin` follows the
Presidency decree table; the `Kossin` form appears in the Presidency
communiqué prose and most operational sources (COD-AB Apr-2026,
FEWS NET, the `Kossin Province` article, AIB-derived news) — kept
`Koosin` per the decree table until the Journal Officiel text rules.
`Gobnangou` follows the decree table and GeoNames
(`Province du Gobnangou`); English Wikipedia still lists `Tapoa`
because its province page predates the reform. `Kuilsé` follows the
Presidency table, the SIG decree-meaning text, and the English
Wikipedia primary article (`Kuilsé Region`, "sometimes rendered as
Koulsé"); the UK PCGN factfile (May 2026) prefers `Koulsé` ("also
seen Kuilsé", claiming decree spellings), COD-AB uses `Koulsé`, and
one SIG headline uses `Koulsé` — kept `Kuilsé`, both forms pinned in
the gate. COD-AB's `Kourittenga` (double-t) is a typo against ISO
`Kouritenga` (also the finder form); `Komandjari` is the recognized
variant of ISO `Komondjari`; `Tuy` is the ISO form (`Tui` variant).

Burkinabe addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, the region on its
own line when both are set, and country.

Revisit 2026-10-03 (1 accent fix: province `Boulkiemde` → `Boulkiemdé`
per ISO 3166-2:BF + COD-AB + province article; `gate_bf.py` ALL
PASS): tree verified against the COD-AB Apr-2026 admin1/admin2 table
(all 17 compositions exact, incl. Tapoa → Dyamongou + Gobnangou and
Soum → Djelgodji + Karo-Peli), ISO 3166-2:BF (45 province codes
exact; region codes follow the succession scheme the UK PCGN
factfile carries as BF-01–BF-13, new regions 14–17 provisional),
and the Presidency 02/07/2025 decree tables (new/renamed divisions
+ chef-lieux Kantchari/Arbinda). Postcodes FILLED from the live La
Poste BF finder (`laposte.bf/codespostaux` commune/quartier/agence
endpoints, Oct 2026): 350 commune rows + 122 quartiers (Ouaga/Bobo
only) + 109 agences = 467 distinct 5-digit codes, 47/47 provinces
covered, zero cross-province codes, every province inside one
2-digit block (split pairs share 62/79). UPU BFA examples 10000 /
10010 / 70000 confirmed; UPU `91001 BAMA` is illustrative (finder:
91001 = Orodara agence; Bama = 90200). Codeless: Komsilga and Silly
communes (no finder entry under any spelling). Finder-side spellings
recorded, not followed: NIAMBOURI (= Niabouri), CINKANSE (postal
locality with agence 70551, absent from COD-AB), NOUBIEL, ZANDOMA.
Real-world usage is thin (sightings use `BP …` + office number, no
5-digit code; Mapanet 2,148 BF rows carry zero code values; 56ok /
GeoNames / geopostcodes silent) — which is what the UPU Sep-2025
do-not-require listing reflects — but the system is official, live,
and enumerable, so it ships as `complete`.

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

Revisit 2026-10-03 (areas verify-only, zero changes;
`gate_cv.py` ALL PASS): 24/24 ISO 3166-2:CV codes (B/S
island groups + 22 concelhos incl. the SO and SM
reassignments); 32/32 parishes with exact per-concelho
parents (RG 4, SD/RGS/Brava/SF/BV/RB/PN 2, rest 1)
matched independently by the en-wp "Administrative
divisions of Cape Verde" table AND GeoNames ADM2
(22 ADM1 + 32 ADM2, same counts/parents, names modulo
diacritics). Same-name parishes keep prefixed ids
(Nossa Senhora da Luz x3, São João Baptista x4, Nossa
Senhora do Rosário x2). One single-signal variant held:
GeoNames calls the Tarrafal de São Nicolau parish "Sao
Francisco de Assis" but WP + bundled say "São Francisco"
(no dedicated parish article to tiebreak), so per the
stability rule no primary moves.

Postal stays admin-ready (no CSVs — decision re-confirmed,
not new): UPU POST*CODE Aug-2026 lists CV as postcode
country (format 9999, length 4) and the official ARME July
2019 revision (Wayback-archived
revisaoCPostalCV2019.pdf) defines the 7-char CPN
`CCZZ-QQQ` (CC = concelho per the INE Código Geográfico
Nacional 11..91, ZZ = zona/bairro, QQQ = quarteirão),
launched July 2019 per furtherafrica (~27,000 records at
codigopostal.cv). But the portal is dead and unarchived
for data, the 2019 annex carries 32 illustrative examples
only (all 22 concelhos represented, e.g. 7401 Cidade da
Praia, 2101 Cidade do Mindelo), and no allocation table
has published — so the 32 examples are NOT bundled (an
examples-only list would mislead: e.g. Tarrafal's only
bundled code would be village 7112 Tras os Montes).
OSM addr:postcode values in CV follow the superseded
pre-2019 system (x110/x600 pattern: 1110, 2110, 7600…)
per the 04/2014 UPU compendium example "7600 PRAIA" —
stale, do not "fix" toward OSM. Revisit when Correios de
Cabo Verde publishes an allocation list.

## Central African Republic

The bundled `CentralAfricanRepublicGeographyProvider` supplies the
20 prefectures — 18 administrative plus the Nana-Grébizi and
Sangha-Mbaéré economic prefectures — as `State` rows and a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CF')` after countries are seeded.
The 85 subprefectures ship as level-2 areas under their prefectures.
The December 2020 law added `Lim-Pendé` (Paoua), `Mambéré` (Carnot),
and `Ouham-Fafa` (Batangafo), and retyped Bangui from commune to
prefecture; Sangha-Mbaéré is modelled as an economic prefecture.
ISO 3166-2:CF still lists only the former 17, so the bundled codes
`LP`/`ME`/`OF` are provisional pending ISO.

The country has no postcode system. Addresses are formatted per the
UPU layout: P.O. box lines, the locality, and country; any supplied
code prints on its own line. Types are labelled `Préfecture`,
`Préfecture Économique`, and `Sous-préfecture`.

Revisit 2026-10-04 (fix: 4 drops + 9 adds; L2 80 → 85; `gate_cf.py`
ALL PASS): the bundled tree had been built from the stale EN
Wikipedia sub-prefecture list, so it kept pre-split duplicates
(Batangafo under Ouham, Paoua + Ngaoundaye under Ouham-Pendé) and a
`Prefectures of the Central African Republic` scrape row (the page's
See-also link, filed under Vakaga) — all dropped. Added the five
missing sous-préfectures (Moboma → Lobaye, Nana-Outa →
Nana-Grébizi, Ouandja-Kotto → Haute-Kotto, Ouandja + Amdafock →
Vakaga) and Bangui's four (Rapides, Fleuve, Centre, Kagas: 8
arrondissements + Bimbo + Bégoua per ICASEES RGPH-4). Oracles: Loi
n°21.001 (adopted 10 Dec 2020) per-prefecture lists via Oubangui
Médias, and the June-2024 sous-préfet decree enumerating posts 1–85
contiguously with prefecture-block order. The decree's press
transcription has single-letter corruptions (Ouada/Ouanda,
Mokouba/Makouba), so `Amdafock` follows the 3-source -ck majority
over the decree-literal `Amdafoc`, and `Nana-Outa` follows the
article + citypopulation over `Nana-Ouata`. The press "84" total is
its own arithmetic slip (its lists sum to 81 + Bangui); 85 is the
verified target — confirmed 2026-10-06 by RNL post 69002 quoting
the Loi du 21 janvier 2021 itself (20 préfectures + 85
sous-préfectures) and OCHA COD-AB v02 listing exactly 20 + 85.
L1 untouched: ISO 3166-2:CF still lists the former
17 (last change NL II-2 2010), so LP/ME/OF stay provisional. Verdict
stays none: UPU cafEn/cafFr profiles (03/2022) codeless, UPU
Sep-2025 do-not-require list carries the country, GeoNames has no CF
postal data (CF.zip + CF.txt both 404).
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

Revisit 2026-10-03 (1 cell only, no count changes; `gate_td.py` ALL
PASS): `Bahr El Gazel Sud` lowercased to `Bahr el Gazel Sud` (live
oracle lowercase + sibling Nord lowercase = 2 signals). All 23
provinces match ISO 3166-2:TD (2018 23-province set; OBP 2020-11-24
recategorized regions to provinces), and all 63 departments match
the pre-2024 Departments-of-Chad oracle (rev 2024-05-03, 2018-era
set) per province, with N'Djamena terminal (oracle confirms no
departments, 10 arrondissements). Display variants vs ISO fr are
deliberate (Bahr el Gazel, Hadjer-Lamis, Logone
Occidental/Oriental, Mayo-Kebbi Est/Ouest, N'Djamena). Scope note:
Ordonnance N°001/PR/2024 restructured departments to 120; the
bundled tree keeps the 2018-era 63-department set pending an
official department list under the 2024 ordonnance or a second
directory. Verdict stays none: the UPU tcdEn profile (09/2004) shows
a codeless B.P. address, the UPU Sep-2025 list carries Chad on
do-not-require, GeoNames has no TD postal dump (404), and the List
of postal codes reads "no codes".

## Chile

The bundled `ChileGeographyProvider` supplies the 16 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('CL')` after
countries are seeded.
The 56 provinces ship as level-2 areas under their regions.

Chilean addresses are formatted per the UPU layout: street lines,
`{postcode} {commune}` with a 7-digit postcode, the region, and
country. Types are labelled `Región` and `Provincia`.

Revisit 2026-10-04 (fix, 1 cell: province display name Cautin ->
Cautín; counts unchanged 72/346/346; `gate_cl.py` ALL PASS):
signals 3-0 — GeoNames "Provincia de Cautín", en-WP canonical
"Cautín Province" ("Cautin Province" is a redirect), es-WP
"Provincia de Cautín" + official usage; the file's own
convention accents every other stressed final vowel (Copiapó,
Limarí, Curicó, Diguillín, Chiloé, Aysén). ASCII slug unchanged,
no link/provider churn. Codes re-verified 1:1: fresh GeoNames
CL.zip (346 rows, set-identical, zero cross-province; pre-2018
admin1/2 with old "Provincia de Ñuble" under Biobío, 325/325
non-Ñuble rows map 1:1 with aliases Aisén->Aysén, (del)
Ranco->El Ranco); es-WP postcode annex 344/345 commune rows
(+1 non-commune Labranza 4810000 correctly absent; annex omits
Torres del Paine 6170000 + San Pedro-Melipilla 9660000, both
real and bundled); UPU 7-digit commune-base model confirmed;
first-digit region ranges hold (1xx..9xx 44/50/68/63/49/20/9/
19/24). Ñuble split (Law 21.033): all 21 communes re-homed
21/21 vs en+es WP rosters (Diguillín 9, Punilla 5, Itata 7).
Per-region provinces/codes: AP 2/4, TA 2/7, AN 3/9, AT 3/9, CO
3/15, VS 8/37, RM 6/52, LI 3/33, ML 4/30, NB 3/21, BI 3/33, AR
2/32, LR 2/12, LL 4/30, AI 4/10, MA 4/12. Holds: CL-MA keeps
official long name vs ISO short "Magallanes" (3 signals); WP
Petorca commune-count "6" is a typo (column sums 347 vs lede
346; reality 5); La Ligua/Petorca 2030000/2040000 swap is
province-neutral (both -> Petorca either way); CorreosChile
finder 403-blocked (operator sweep deferred); non-commune
locality codes out of scope by design (gate pins 4810000
absent).

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_km.py` ALL
PASS): the 16 prefectures match Loi N°11-006/AU Article 7 exactly
(Mwali 3: Fomboni, Nioumachioi, Djando; Ngazidja 8; Ndzuwani 5),
and the island codes match ISO 3166-2:KM (A/G/M). No postcode
system: UPU KM profile (07/2002) shows B.P.-only addressing, GeoNames
has no KM postal dump (404), and directories list no codes (00000
placeholders only). Note: UPU's require/do-not-require lists
contradict on Comoros (Aug-2026 vs Sep-2025) — resolved by the
profile, dumps, and live B.P. usage.

## Congo

The bundled `CongoGeographyProvider` supplies the 15 departments
of the Republic of Congo as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CG')` after countries are seeded.
The 92 districts ship as level-2 areas under their departments.
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

Revisit 2026-10-04 (fix: +3 districts Odziba/Bouemba/Ile Mbamou,
Ollombo reparented Plateaux→Nkéni-Alima, 5 law-spelling renames
Vinza/Ongoni/Makotipoko/Bouaniéla/Mbandza-Ndounga; `gate_cg.py` ALL
PASS): all 15 departments and 92 districts verified against JO N°
42-2024 Laws 24–34 of 8 Oct 2024 (Djoué-Léfini 6 incl. newly
created Odziba, Nkéni-Alima 6 incl. Ollombo, Plateaux 6 incl.
Bouemba, Congo-Oubangui 4, donor remainders Cuvette 7 / Likouala 6
/ Pool 8 exact; Kintélé→Brazzaville is commune-level, out of tree
scope). ISO 3166-2:CG still lists only the former 12, so codes
17/18/19 stay provisional. Verdict stays none: the UPU cogEn
profile (09/2004) is codeless (BP 652), the UPU Sep-2025 list
carries Congo (Rep.) on do-not-require (DR Congo is on the require
side), and GeoNames has no CG postal dump (404 + zip-index
absence).

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

Revisit 2026-10-03 (Bélier row moved into the Lacs group, ordering
only; `gate_ci.py` ALL PASS): all 14 first-level areas match ISO
3166-2:CI, and all 31 regions match the Districts-of-Ivory-Coast
oracle per district (Bélier confirmed under Lacs; Abidjan and
Yamoussoukro childless). Verdict stays none: the UPU civEn profile
(09/2004) states the 2-digit code is a post-office code on P.O.-Box
lines, the UPU Sep-2025 list carries Côte d'Ivoire on
do-not-require, and GeoNames has no CI postal dump (404).
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

Revisited (2026-10-04): verify-only — tree 7 provinces + 84
cantons (es.wiki per-province 20/16/8/10/11/13/6 exact; Río
Cuarto/Monteverde/Puerto Jiménez present; Cóbano/Paquera/Jicaral
canton bills + Comte Burica district bill unenacted — Golfito
exp. 23189 archived, refiled 25750). Postal 492/492 = WP 491-row
district table + Lagunillas 61103 (Garabito 3rd district, law Nov
2020; en.wiki table stale), attribution 491/491 exact, 84
prefixes 1:1 with cantons. GeoNames 473 reconciled: bundled =
GN + 22 documented extras (17 post-GN districts + 5 new-canton
codes) − 3 superseded GN rows correctly excluded (20306 Río
Cuarto-as-Grecia, 60109 Monteverde-as-Puntarenas, 60702 Puerto
Jiménez-as-Golfito — none in the WP table). Correos finder
unreachable (SSL + conn failed); WP + GN + es.wiki canton
articles stand as oracles. Gate `docs/agents/audit/gate_cr.py`
pins all 84 prefix blocks.

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

Revisit 2026-10-05 (B15 inline, 2 renames, 18 drops, 2 moves,
2 dual-links, 5 fills; 785 -> 772 codes, 788 -> 777 legs, 3 ->
5 multis; `gate_cu.py` ALL PASS): tree verified 168/168
names+parents against COD-AB (3 COD-AB typos: Ciefuegos, Ciro
Redodo, Frak Pais) + eswiki. Renames: Old Havana -> La Habana
Vieja (COD-AB + eswiki + Mapanet; 5 legs retargeted); Songo en
dash -> Songo-La Maya (COD-AB + eswiki + Mapanet). Drops: 5
shape-broken (-, -8104, '000 3', 2, 7), 12 zero-garbled 000XX
(all duplicating live bases), 1 ghost 20600 (Pinar block on
Santa Clara, zero signals anywhere). Moves: 10200 Vieja ->
Centro Habana (Mapanet + parish usage 5:2); 99420 San Antonio
del Sur -> Yateras (Mapanet + 6 directories). Duals (boundary
zones, majority primary): 10600 Cerro(P)+Plaza(S) (usage 8:4 +
serviciosglobal + OSM vs operator + UPU Habana-6); 11400
Playa(P)+Marianao(S) (usage 3:1 + OSM vs operator). Fills:
22600 La Palma, 53310 Sagua (52310 kept too — both OSM-real),
73200 Santa Cruz del Sur, 97310 Baracoa (all Mapanet + OSM),
19120 Habana del Este (usage + OSM). Keeps: Artemisa 35/37/38
scheme (operator office-API lineage + UPU/youbianku agreement
over stale Mapanet/OSM 32xxx and stale parish entries —
renumber, dual-validity unproven, held); 3 office duals
24280/74370/74440 (OSM inconclusive/noise); Habana del Este
article (tie -> hold); 10500/10900/52310/10100/10400/11500
(operator + usage over Mapanet-dup/OSM-noise challengers).
Oracles: Mapanet full CU crawl (144 muni / 186 codes),
Nominatim ~70 probes (rural reliable, Habana-city noisy:
10100/10500/10700/10900/10800 misattributions), Havana
Archdiocese parish directory (100+ usage postcodes, also
confirms all Mayabeque bases + 34xxx sectors), UPU CUB.pdf,
eswiki, 6 Guantanamo directories. No GN CU postal dump.
Retry 2026-10-06 (zero data changes): the office search was
never dead — its backend is `POST /wp-admin/admin-ajax.php`
(`action=oficinas`, named by `hueman/js/oficinas.js`); 638
live office rows pulled across 11 provinces, 100% new-scheme
in Artemisa (35/37/38 keeps vindicated, all old codes stay
unfilled — even 2-signal stale rows 32300/32400/32500 prove
existence, not dual validity). Rebutted, do not fill: 74680
La Jagua (operator: La Jagua office carries 74540) and 74360
San Miguel de Bagá (operator: 74370 + Nominatim places it in
Nuevitas, not Guáimaro). Still held: 10 Camaguey sectors
(operator- + OSM-silent; unblock = one second lineage each);
62410 (operator+OSM+usage all 62400 — likely off-by-10);
77200 (de-facto base is 77210); 34390/34140 old-scheme;
35110/37330/37550/34360 operator-only finds (need a second
signal); 12900 Castilla usage vs Cerro leg.

## Djibouti

The bundled `DjiboutiGeographyProvider` supplies the 5 regions
plus Djibouti City as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DJ')` after countries are seeded.

The 20 sub-prefectures ship as level-2 areas (Ali Sabieh 3,
Arta 1, Dikhil 4, Djibouti City 1, Obock 4, Tadjourah 7),
each parented per its town/place article since the reference
lists are flat. `Adailou` follows the town-article spelling
(the flat lists print `Adaylou`/`Adayllou`); `Lac Assal` follows the
French local name (district map + UPU profile). `Lac Assal` is
parented to Tadjourah (2024 census placement, UPU 77601 routing,
GeoNames admin1 DJ-05, Sagallo settlement) — moved from Arta
during M1 verification; pinned in `docs/agents/audit/gate_dj.py`.

Djiboutian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
Types are labelled `Région`, `Ville`, and `Sous-préfecture`.

The 10-code overlay is the UPU DJI addressing profile (05/2020):
77101 Djibouti Ville, 77102–77105 Marabout/Einguela/Nasser/Balbala,
and 77201/77301/77401/77501/77601 for the Arta/Ali Sabieh/Dikhil/
Obock/Tadjourah villes. Rural localities route via their region
capital's code (UPU examples: Randa and Lac Assal via 77601), so
6/20 linked sub-prefectures is complete coverage by design, not a
gap; 77102–77104 rest on the UPU table alone (single evidence).
The 2024 census reports an 18-unit ville/périphérique scheme
(Damerjog, Karta, Mouloud; no Galafi/Balho/Moulhoule/Mousa Ali);
no decree was found, so the classic 20-sub-prefecture tree is
retained pending official text.

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

Revisit 2026-10-05 (1 rename: Ingenio Quisqueya -> Quisqueya;
`gate_do.py` ALL PASS): tree 200/200 — 10 regions ISO
DO-33..42 exact, 32 L2 ISO DO-01..32 exact (Baoruco kept per
ISO-current + Statoids GEC-2013 over es.wiki Bahoruco), 158
municipalities 158/158 normalized + 158/158 parents vs the
es.wiki municipality table (bundle uses formal official
names). Rename signals: es.wiki Quisqueya (municipio) +
INPOSDOM 21400 place Quisqueya. Postal: bundle set ==
live INPOSDOM data.json set exactly (528/528); both multis
dual-confirmed by the operator with population-majority
primaries; 6 GN-only DN codes (10110/10131/10203/10206/11111/
11708) correctly excluded (operator-silent, 4 acc=1);
odd-prefix keeps coordinate-verified (58081 Santiago,
56000 Moca town). Hold: Jamao al Norte lowercase al
(Spanish orthography; es.wiki Al).

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

Revisit 2026-10-03 (verify-only, zero changes; worker gate
`gate_sv.py` ALL PASS, 13 installed checks + 3 crosswalk checks):
areas tree context — the B9 inline fix corrected 12 mis-parented
municipalities (La Libertad x6 + La Paz x3 had sat under
Cuscatlan, San Miguel x3 under Morazan); tree re-pinned at 14
departments + 44 municipalities (2024 reform 262->44).

Codes: full 262/262 crosswalk verified. Code set matches
mapanet.eu's per-municipality scrape exactly (262 coded, one code
each, no dupes); every code's old (pre-2024) municipality falls
inside its linked NEW municipality per the official reform
composition (es-wiki Anexo:Municipios y distritos, citing DL
reform D.O. 14/06/2023 + official distribution map). Mirror #2
worldpostalcodes agrees (dept counts SS 19 / Sonsonate 16;
spot profiles 1115 San Marcos, 1118 Ciudad Delgado, 1131 Santo
Tomas, 2302 Acajutla). 4 name aliases adjudicated, all same-place:
Ciudad Delgado=Delgado, San Jose de la Fuente=San Jose (La Union,
origen "San Jose La Fuente"), San Antonio del Mosco=San Antonio
(San Miguel, redirect), San Jose Cancasque=Cancasque.

One oracle conflict investigated and resolved WITHOUT moving any
primary: en-wp "List of municipalities and districts" gives San
Salvador as Este 6 / Centro 6 / Sur 2 (Cuscatancingo+Delgado east,
San Marcos/Santo Tomas/Texacuangos central), but the bundled
Este 4 / Centro 5 / Sur 5 matches the decree-citing annex plus
mapanet/WPC code anchors — en-wp is stale (pre-approval
proposal); bundled side holds 3 signals, so per the stability
rule no primary moves.

Per-department code counts (each = old-municipality count):
San Salvador 19 (1101 + 1115-1132), Cabanas 9, Chalatenango 33,
Cuscatlan 16, La Libertad 22, La Paz 22, San Vicente 13,
Ahuachapan 12, Santa Ana 13, Sonsonate 16, La Union 18, Morazan
26, San Miguel 20, Usulutan 23. All 262 links primary, one per
code; every new municipality holds >=1 code (singletons Santa
Ana 2201, Acajutla 2302). Codes/posts CSVs stay CRLF.

Liveness (honest): SV 4-digit codes are thinly used domestically
("no national postcode system, codes as placeholders" per 56ok /
zipcodehere-style directories; Correos site exposes offices only,
no public postcode finder found; GeoNames ships no SV dump) —
but the published per-municipality directories (mapanet 262,
WPC, prior UPU-compendium cross-check in overlay row) agree
exactly behind the bundled set, so fill stays.

Holds/gaps: none. No uncovered codes, no dual links, no codeless
municipalities. Ugly-but-stable ids kept (BM precedent):
sv:municipality:santa-ana-el-salvador-central-santa-ana,
sv:municipality:acajutla-acajutla-western-sonsonate.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_gq.py` ALL
PASS): 10/10 ISO 3166-2:GQ codes incl. DJ Djibloho (3 Insular
+ 5 Río Muni parents verified); UPU gnqEn shows province +
locality addressing with no postcode.

## Eritrea

The bundled `EritreaGeographyProvider` supplies the 6 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ER')` after
countries are seeded.
The 58 subregions ship as level-2 areas under their regions.

Eritrea has no postcode system. Addresses are formatted per the UPU
layout: street lines, the locality, and country; any supplied code
prints on its own line. The `region` type is labelled `Zoba`.

Revisit 2026-10-03 (fix, 1 rename; `gate_er.py` ALL PASS):
Debub's 12th subregion renamed `Kudo Be'ur` ->
`Emni Haili` (id `er:subregion:emni-haili`). Four naming
signals say Emni Haili: UN OCHA COD-AB v01 pcode ER610,
GeoNames ADM2 (admin2 code 610, containing PPL "Kudo Baur"
i.e. the capital village under another transliteration),
and the en-wp "Regions of Eritrea" + "Subdivisions of
Eritrea" 12-member Debub lists. Only the en-wp "Subregions
of Eritrea" list (plus its auto-stub "Kudo Be'ur subregion")
says Kudo Be'ur, so per the 2-signal rule the primary moves.

Everything else verified unchanged: 6/6 ISO 3166-2:ER codes
(AN DU GB MA SK DK, incl. Statoids + de-wiki confirmation),
58/58 subregions with per-region counts 11/7/14/10/12/4
matched independently by GeoNames ADM2 AND the full COD
admin2 table. WP-English naming convention kept for the
translated Maekel/SRS names (North Eastern etc. =
COD Semienawi Mierab etc.; Central/Southern Denkalya =
COD Maekel/Debub Deb.Keih Bahri) and for transliteration
variants (Debarwa=Dbarwa, Mai ani=May Aini,
Mai-Mne=Maimine, Segeneiti=Segeneity, Ghela'elo=Ghelaelo,
She'eb=Shieb, Are'eta=Araeta, Assab=Asseb, Dghe=Dige,
Teseney=Tesseney, Molki=Molqi, Geleb=Gheleb,
Adi Tekelezan=Adi Tekeliezan, Berikh=Berik,
Ghala Nefhi=Galanefhi, Serejaka=Serejeka, Upper Gash=Lalay
Gash) — same places, bundled side holds the en-wp
article-title convention (e.g. "South Eastern subregion",
"Upper Gash subregion"), so no primary moves. Faithful WP
quirks kept verbatim: "North western" (lowercase w) and
"Mai ani" (lowercase a) appear exactly so in the oracle
lists. OSM has region boundaries only (admin_level 4 x6;
one stray Al Fushqa District relation is Sudanese data rot,
ignored). No postal files: Eritrea has no postcode system.

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ga.py` ALL
PASS): 9 provinces match ISO 3166-2:GA digits 1–9, and all 49
departments match the Departments-of-Gabon oracle per province (Cap
Estérias, deleted 2013, correctly absent; Leboumbi-Leyou uses the
corrected display spelling over the article-title typo). Verdict stays
none: the UPU gabEn profile (07/2002) shows the 2-digit code is a
delivery-office code (same shape as Ivory Coast), the UPU Sep-2025
list carries Gabon on do-not-require, and GeoNames has no GA postal
dump (404). The UPU "99" format-table entry is that office code, not
a delivery postcode — the same contradiction shape as Comoros.
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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_gm.py` ALL
PASS): first level matches ISO 3166-2:GM (Banjul B + 5 divisions M L
N U W) plus Kanifing municipality (K), kept terminal by design — its
sole oracle district is the degenerate self-named Kanifing. All 42
bundled districts match the Subdivisions-of-the-Gambia oracle per
LGA (Basse Santa Su → Upper River, Brikama → West Coast, Janjanbureh
+ Kuntaur → Central River, Kerewan → North Bank, Mansa Konko → Lower
River, Banjul 3). Verdict stays none: the UPU gmbEn profile (07/2002)
shows a codeless address, the UPU Sep-2025 list carries Gambia on
do-not-require, and GeoNames has no GM postal dump (404).

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

Revisit 2026-10-03 (one fix: sector `Bafata` → `Bafatá`;
`gate_gw.py` ALL PASS): the 38-sector tree matches the en.wiki Sectors
list plus region articles and ISO 3166-2:GW (BA/BM/BS/BL/CA/GA/OI/QU/TO)
exactly; the accent fix aligns the eponymous sector with the
Bafatá-region article infobox, town article, pt.wiki, Mapanet, 56ok,
and OSM. Leste/Norte/Sul stay excluded (statistical provinces). All
52 codes / 62 links reproduce exactly under a fresh re-derivation
(150 Mapanet locality rows with coords + per-row OSM sector
reverse-geocode). Recorded judgments: 3600 seat-primary Mansoa
despite a 3-1 OSM Nhacra plurality (the Mansoa row is Mansoa town
itself); 5300 stays Galomaro-only (lone Xitole vote is a mislabeled
centroid row). Bigene, Catió, Komo, and Bolama sectors are codeless
in source. 1000/1011/1021/1029 are Cedex/PO-box-layer codes and
correctly stay out.

## Guyana

The bundled `GuyanaGeographyProvider` supplies the 10 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GY')` after
countries are seeded.
The 10 towns and 65 neighbourhood democratic councils ship as
level-2 areas under their regions (19 East Berbice-Corentyne, 17
Demerara-Mahaica, 14 Essequibo Islands-West Demerara, 10
Mahaica-Berbice, 6 Pomeroon-Supenaam, 3 Barima-Waini, 2
Cuyuni-Mazaruni, 2 Upper Demerara-Berbice, 1 Potaro-Siparuni, 1
Upper Takutu-Upper Essequibo). Region 8 (Potaro-Siparuni) has no
NDC tier and Region 9's Ireng/Sawariwau NDC was dissolved in 2012,
so Mahdia and Lethem are lone towns by design.

Guyana has a UPU-documented 7-digit postcode system (UPU guyEn
08/2025: region/sub-region/locality/office/delivery district, e.g.
Georgetown 4130106), but no postal overlay ships yet — code links
are a step-4 follow-up, not a codeless verdict. Guyanese addresses
are formatted per the UPU layout: street lines, the locality, the
postcode on its own line below the locality, and country.

Revisit 2026-10-03 (tree verified clean, zero data changes;
`gate_gy.py` ALL PASS): 65/65 NDC names exact against the
MLGRD-cited list, 10/10 towns placed (8 regional capitals +
Corriverton and Rose Hall under East Berbice-Corentyne), prior "66
NDCs" wording corrected.

## Lesotho

The bundled `LesothoGeographyProvider` supplies the 10 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LS')` after
countries are seeded.
The 80 constituencies ship as level-2 areas under their districts.

Basotho addresses are formatted per the UPU layout: P.O. box lines,
`{locality} {postcode}` with a 3-digit postcode, and country.

Revisited (2026-10-03): 1-cell fix — Mokhotlong #78 is Senqu, not a
second Malingoaneng (IEC 2025 constituency PDF + IEC 2022/2017/2015
results pages + 2012 election table + 2006 census all list Senqu
No.78; WP Constituencies page duplicates Malingoaneng in error).
Tree otherwise verify-only: 10 districts with ISO letters A–K and
the 80-constituency 2022 delimitation (Legal Notice 37/2022)
11/5/13/22/7/6/4/3/4/5. Postal stays admin-ready: 3-digit system
is live (Maseru 100) but no public allocation table exists
(GeoNames LS.zip 404, no UPU PDF snapshot, Mapanet shell-only).
Gate `docs/agents/audit/gate_ls.py` pins the full 80-name map.

## Liberia

The bundled `LiberiaGeographyProvider` supplies the 15 counties
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LR')` after
countries are seeded.
The 157 districts ship as level-2 areas under their countys.

The 31-code overlay (1000–7520) comes from the post-office list at
county level (codes do not resolve to districts): UPU-anchored
Monrovia 1000 and Buchanan 4000, Monrovia 1000-xx delivery units strip
to base at lookup, Gbarpolu codeless; no new area rows.

Liberian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country. The
system is officially defined but flagged not-in-use by UPU, so codes
stay optional; Monrovia zone suffixes pass through as supplied.

Revisit 2026-10-04 (B14 tree rescheme 127→157 districts;
`gate_lr.py` 0 FAILURES): the bundle now follows the LISGIS 2022
census final report Appendix B (primary), whose per-table arithmetic
seals membership — 11 of 15 tables sum exactly; the B2 gap is a
Gounwolaila row misprinted under later tables (Gbarpolu 6); the B4/B8
gaps are rows omitted in print (Owensgrove 14,422 and Dugbe River
17,478; both county totals triple-sealed via main text +
citypopulation). Per-county L2: 5/12/6/8/5/8/19/11/5/7/15/17/10/8/21
(Bomi through Sinoe). 21 renames (Bong Panta + Sanoyeah; Bassa
Neekreen + St. John River City; Cape Mount Golakonneh; Lofa Quardu
Boundi; Margibi Farmington; Maryland Pleebo/Sodoken; Nimba Garr-Bain,
Gbehlay-Geh, Sanniquellie Mahn, Wee-Gbehyi-Mahn; Rivercess Beawor,
Central Rivercess, Zarflahn; Sinoe Jeadepo, Kulu, Plahn, Sanquin
Number 1/2/3), 5 drops (Gbarzon, Barrobo, Webbo, Mambah-Kaba,
Montserrado Commonwealth — none in any census table), 35 adds
(Montserrado 11 townships/borough, Maryland 6, Grand Gedeh 6, Sinoe 4,
Lofa 4, Margibi 2, Gbarpolu/Bassa 1 each). Adjudication notes: Lofa's
+4 use report spellings (Lukameh, Wahasa, Waum — the CDA added
letters); Sinoe keeps Dugbe River (CDA prose affirms the district)
beside Jlah/Krah/Sarboh/Bar-Nakay; Montserrado's 15 use Barnersville
(MIA/judiciary/presidency usage) and Louisiana (both CDAs) over the
report's "Lousana" typo, with "New georgia" case-fixed; Cape Mount
keeps short Commonwealth (the report's wrapped "Robertsport" is a
capital qualifier, per the Bassa "District Number N (clan)"
parenthetical precedent); Penicess, Karforh, Porkpa, Jaedae,
Meinpea-Mahn, Twan River keep over CDA typos. Postal verify-only:
31/31 county-pure links intact, Gbarpolu still codeless (held).

Revisit 2026-10-04 (B14-PDF reconciliation, zero data changes;
`gate_lr.py` 0 FAILURES): the user-supplied LISGIS final report was
extracted and re-checked table-by-table in the 2022 frame (every row
male+female=total). 10 of 15 App.B tables sum exactly; the 5 errata
confirm the bundle as-is: B2 gap +17,986/+9,513/+8,473 seals
Gounwolaila under Gbarpolu (row omitted from the B2 print, county
total includes it; COD-PS p-code LR0305 + Statoids GP agree), B3/B5
gaps −17,986 each prove its two printed rows spurious dups
(identical figures twice — the final PDF carries the dup in both
tables where the draft carried one), B4 gap +14,422 seals omitted
Owensgrove (COD-PS LR0407), B8 gap +17,478 seals omitted Dugbe River
(COD-PS LR1504), and B11 sums exactly so Maryland has no 8th
district. The B14 "Barobo 18,758" figure was void mixed-frame
arithmetic (a 2008 county total set against 2022 district rows),
is printed nowhere in the report, and is struck; Barrobo stays
dropped (absent from the whole PDF and from current COD-PS).
`pdf-*` gate pins + provider-test errata pins guard the dups.
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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ly.py`
ALL PASS): 22/22 ISO 3166-2:LY codes exact, and the 100
baladiyas match the OCHA COD 2017 ADMIN-3 sheet 100/100 on
p-code, name, and mantika parent (the only 2 raw diffs are COD
spreadsheet artefacts where the CSV holds the clean form);
COD-AB v01 corroborates 78/78 place rows with zero diffs, and
DTM's own reporting (R8 2017 through Migrant Report 60,
Nov–Dec 2025) asserts the 100-municipality set throughout.
Era: L1 is the 2007 22-sha'biyat system still current in ISO
(Newsletter II-2); L2 is the post-2013 operational system in
the BSC/IOM p-code lineage — distinct from the 1983/1988
25-baladiya system, the 99 gazetted 2013, and 106–114 claimed
maxima. Display follows the ISO en reference (Derna, Murqub,
Sirte, Tripoli, Zawiya, Nuqat al Khams, Wadi al Hayaa) against
the districts oracle's Arabic-name forms; `Wadi al Shatii`
matches the oracle article title. Verdict stays none: the UPU
lbyEn profile (02/2012) shows a codeless address with no
postcode section, the UPU Sep-2025 list carries Libya (State
of) on do-not-require (absent from the Aug-2026 require
list), and GeoNames has no LY postal dump (404).
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

Revisit 2026-10-03 (1 fix: `ml:cercle:niema` Niéma → Niéna, name
only, slug stable — oracle Nièna + fr.wiki Niéna +
citypopulation 0307__niéna; `gate_ml.py` ALL PASS post-fix):
2023-restructure era confirmed (Laws 2023-006/007; 19 regions +
Bamako + 159 cercles with gap-free 4-digit codes, Bamako
terminal). Per-region counts exact (Gao 16, Tombouctou 13,
Ségou 11, Kayes 10, Bougouni 10, Kidal 9, Bandiagara 9,
Koulikoro/Sikasso/Mopti/Koutiala 8, San 7, the rest 6).
National 09 = Taoudénit / 10 = Ménaka confirmed by
citypopulation, diverging from ISO 3166-2:ML (still 10-region
era, 9/10 swapped). Keeps: Anéfif (CSV + citypop; oracle
Anétif probable typo), Dialassagou (2v2 stalemate vs
Diallassagou — recheck against the Loi annex), Taoudenni,
Tombouctou (French vs Timbuktu exonym), Inlamawane (Fanfi),
Kadiana (oracle Kadiala wrong). Codeless quadruple-confirmed
(UPU do-not-require, mliEn profile postcode-free, no GeoNames
ML dump, List "no codes").

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_mr.py` ALL
PASS): 15 regions match ISO 3166-2:MR codes exactly (Nouakchott
Nord/Ouest/Sud as MR-14/13/15 from the 2014 3-way split; CSV
`Dakhlet Nouadhibou` drops the ISO circumflex per the oracle
display), and all 63 departments match the
Departments-of-Mauritania oracle per region (oracle link-text
`Guidimakha` vs ISO/CSV `Guidimaka`). Verdict stays none: the UPU
mrtEn profile (03/2005) shows a codeless B.P. address with no
postcode section, the UPU Sep-2025 list carries Mauritania on
do-not-require, and GeoNames has no MR postal dump (404).

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

Revisit 2026-10-04 (B14 verify-only, `gate_mu.py` 0 FAILURES, zero
content changes): the tree matches the WP places table with zero
diffs (139 mainland places; `Black River` kept over the table's
French `Rivière Noire` per ISO; `Crève Coeur` accented in both)
and the Agaléga article seals the island trio (Vingt-Cinq, La
Fourche, St. Rita). ISO L1 codes exact (9 districts + AG/RO/CC);
urban L2 deliberately carries no ISO town codes, matching every
other country. Postal 1990/1990/0 sealed structurally: UPU anchors
11213 Port Louis + 42602 Lalmatie, district blocks clean except 58
whole-village cross-block codes in 10 border villages (Belle Vue
Haurel 30101-08, L'Escalier 61401-17, Midlands/Seizième Mille,
Quatre Soeurs, Rivière du Poste, La Flora, Plaine des Roches,
St Julien d'Hotman, Ripailles — postal district follows the
serving office across the admin line, consistent with the 16
dual-district villages), 182 R-codes at Rodrigues by design (no
sub-grain bundled, same terminal pattern as Brčko), 4 A-codes
split 3 villages + 1 dependency leg, Saint Brandon codeless
(uninhabited), Chagos correctly excluded (BIOT-administered).
Held: per-code transcription of all 1,990 codes (MP finder is
JS-walled with grouped postal localities, no GeoNames MU postal
export, directories paywalled) — the bundle stays
MP-scrape-authoritative pending a finder API or directory PDF.

Retry 2026-10-06 (zero data changes): the finder yielded a plain
same-page POST (`town_village` + `submit=` to
`/find-your-post-code/`, plus an autocomplete AJAX action) and a
full 146-town harvest (4,283 rows) replicates the bundle code set
EXACTLY (1,990/1,990, zero diffs either way). The MDPA open-data
street snapshot (2024-09-30) replicates 1,987/1,990 with 6
MDPA-only stale/typo rows adjudicated against live MP (bundle
vindicated); MDPA is publication-independent but MP-derived, so
per-code transcription is proven to MP-operator level while a
non-MP ground-truth oracle stays unfound (GeoNames/WPC/postcodebase
absent, OSM 406, Nominatim junk, UPU POST*CODE walled). Link-level
(code→area) audit remains future work (naive town join only
1,349/1,990 — grouped postal localities need the integrator's
`mp-table.json` replay). Rodrigues 5-sub-office split (La Ferme,
Port Mathurin, Grande Montagne, Riviere Coco, Mont Lubin + 182
R-code rows) and the Agalega office record are banked in OPEN/MU
scratch for the future enhancement.

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

Revisit 2026-10-04 (fix: 2 name-only renames, slugs stable;
`gate_na.py` 0 FAILURES): `Karas` → `ǁKaras` per the GG5261
delimitation proclamation (REGION NO. 13 renamed !KARAS) + ISO
3166-2:NA + WP (NamPost `//KARAS` and ECN `||Karas` are ASCII
fallbacks); `Okorukambe` → `Okarukambe` per GG5261 (2 hits, zero
for the old spelling) + the ECN 2024 advert (WP and citypopulation
carry the error). All 121 constituencies re-verified member-exact
against the ECN 2024 full enumeration + GG5261, incl. the WP
list-table omissions Tondoro (Kavango West) and Oshikunde
(Ohangwena). Kept against ECN-advert variants on gazette evidence:
Ncamagoro (GG×7, ECN `Ncamangoro` is a typo), Okatyali (GG×1),
Sibbinda (GG explicitly substitutes `Sibinda` → `Sibbinda`),
Omuthiyagwiipundi (GG unhyphenated), Nehale lyaMpingana (ECN 2020
post-election report + WP camel; the gazette's spaced `Nehale lya
Mpingana` is the legal outlier). Diacritic/click names Dâures,
ǃNamiǂNûs, Moses ǁGaroëb kept per WP + citypopulation (gazette and
ECN are ASCII-only and fold them). Postal overlay re-verified
149/149 codes + attributions against the NamPost poster PDF with a
clean first-two-digits prefix→region rule. Watch: the 2025
Demarcation Commission proposed ten new constituencies, but the
Nov 2025 elections still ran on 121 — no tree change.

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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_ne.py` ALL
PASS): 7 regions plus the Niamey urban community match ISO
3166-2:NE codes exactly (NE-1..8), all 66 departments match the
Departments-of-Niger oracle region lists per region (the page lead
prose still says 63; the lists total 66 including the Maradi,
Tahoua, and Zinder city departments), and the 5 Niamey communes
match the Niamey article (I--V). Verdict stays none (nothing to
import): the UPU nerEn profile (03/2005) gives 4 digits left of
the locality but states deliveries are made to P.O. Boxes only
(example 8001 NIAMEY), GeoNames has no NE postal dump (404), and
directory evidence shows 800x codes routing to Niamey post
offices (plateau/aeroport/rive droite/RP) -- sub-city box
routing, not department geography.

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

Revisit 2026-10-05 (B15 inline, 2 renames, zero leg moves;
`gate_ni.py` ALL PASS): tree verified 153/153 names+parents
against COD-AB (INIDE lineage) + citypopulation admin + eswiki.
Renames: San Juan de Río Coco → San Juan del Río Coco, 35800
(INIDE gazetteer ×2 + COD-AB + enwiki article/lists + eswiki
body vs eswiki-title/cp-display "de"); Waspán → Waspam, 72100
(2015 operator RAAN map + INIDE gazetteer + COD-AB +
citypopulation wikilink vs eswiki/cp-display Spanish form; slug
was already m-form). San Juan del Norte → San Juan de Nicaragua,
92500, renamed 2026-10-06 (Ley 434 Art. 1–2 of 2002-07-15 +
consolidated Ley 59 of 2021 + INIDE anuario2023 31× + operator
print-all 4 captures + enwiki; eswiki "extraoficial" note cites no
law and is stale; the 2015 map's del Norte is stale vs the
operator's own print-all; area id keeps the pre-2002 slug as
stable key). SJRC del now law-backed too (Decreto 981 of 1964 +
consolidation; note INIDE anuario2023 prints de ×10 — an
INIDE-internal inconsistency, law wins). Keeps:
El Jícaro, 38800 (operator NS map keeps the article);
Kukra Hill, 82200 (operator RAAS map spaced); Mulukukú accent
(gazetteer drops accents inconsistently); Los Remates casing
(eswiki canonical capital-L). Postal: all 144 non-Managua codes
verified — 16/17 archived operator dept maps read code-by-code
(Madriz map re-captured 2026-10-06 as file-verified 600×429 JPEG
from Wayback 20151225082009, content pixels not yet eyeballed;
its 9 covered by Nominatim), full 144/144
Nominatim sweep, 7 flags resolved for the bundle by
codigo-postal.org + operator maps (42600 Masatepe, 46400 San
Marcos, 46600 Santa Teresa, 48500 Tola over OSM
misattributions; 38300/52300/62400 over OSM gaps), JINOTEGA
map-center ambiguity resolved for the bundle by OSM+cpo
(66600 San José de Bocay, 66700 Wiwilí). Managua 738: Mapanet
609/609 subset + OSM 4 + codigo-postal.org 18 + grid scheme;
X0 district labels + 13003 triple-absent (no fill); UPU NIC.pdf
5-digit format. Live Correos finder Cloudflare-403, GN has no
NI postal dump, Overpass 406/504 this session (worked around
via Nominatim + maps + directories).

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

Revisit 2026-10-03 (fix: Lemba → Lembá 1-cell; `gate_st.py` ALL
PASS): 7/7 ISO 3166-2:ST codes; the accent matches ISO ST-04,
the district article, and the file's own convention (Água
Grande, Caué, Mé-Zóchi, Príncipe). UPU stpEn carries no postcode
section.
## Senegal

The bundled `SenegalGeographyProvider` supplies the 14 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SN')` after
countries are seeded.
The 46 departments ship as level-2 areas under their regions.

Senegalese addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode (often written `CP
NNNNN`), and country.

Revisit 2026-10-03 (14 link fixes: 12 stale secondaries dropped,
24027 Foundiougne → Fatick, 20600 gains Tivaouane; `gate_sn.py` ALL
PASS): tree verified against the Departments-of-Senegal table (14
regions + 46 departments, ISO 3166-2:SN) with the Keur Massar split —
zero tree changes. Postcodes verified against the live La Poste
table (`postal-data.js`, 4,143 rows): its 163 five-digit codes
exactly equal the shipped set, including bureau rows 10200 Dakar RP
and 16500 Thiaroye. Every link re-adjudicated commune by commune:
182 links, 19 shared codes. Same Kanta attested (Sama Kanta Peulh
CR, Sédhiou); Nghoye unattested in all sources. 32800 is a 9-9
quartier tie; Dagana holds the primary as the bureau name. Note:
postcodebase is transcription-only now — its department column is
proven wrong in 10+ placements and must never adjudicate links.
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

Revisit (geo-verify M1): tree verified against the Districts/Provinces
tables, ISO 3166-2:SL (SL-E/NW/N/S/W), and the Stats SL 2021 MTPHC
pilot report — 4 provinces + Western Area + 16 districts, zero CSV
changes. `North Western` keeps the ISO official name (`North West`
is the Stats SL/display shorthand, kept as an alternative name).
`Western Rural`/`Western Urban` match the Districts-table display
labels; the Stats SL long forms `Western Area Rural`/`Western Area
Urban` ship as alternative names. No postcode system: UPU Sep-2025
no-postcode list + no GeoNames SL postal dump (404) + `no codes`
directory entry. Gate `docs/agents/audit/gate_sl.py` ALL PASS.
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

Revisit 2026-10-03 (2 renames, `gate_so.py` ALL PASS):
`Lower/Middle Shebelle` → `Lower/Middle Shabelle` (ISO en-ref +
oracle table + Statoids + OCHA COD-AB v03; `Shebelle` is the
river's English name, `Lower Shebelle` redirects to `Lower
Shabelle`). 18/18 ISO 3166-2:SO codes exact, and 89/89 districts
match the Regions-and-districts oracle per region (automated diff,
zero diffs; the article intro's "72 districts" is stale prose, the
explicit table sums to 89). Kept deliberately: `Woqooyi Galbeed`
(ISO vs the table's Somaliland rename `Maroodi Jeex`),
`Hiran`/`Nugal` (oracle display vs ISO so Hiiraan/Nugaal).
Verdict stays none: the UPU somEn profile (09/2004) documents a
paper-only scheme never taken into use, the UPU Sep-2025 list
carries Somalia on do-not-require, GeoNames has no SO postal dump
(404), and directories agree.
## South Sudan

The bundled `SouthSudanGeographyProvider` supplies the 10 states
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SS')` after
countries are seeded.
The 84 counties ship as level-2 areas under their states.

South Sudan has no postcode system. Addresses are formatted per the
UPU layout: street or box lines, the town, the state when it differs,
and country; any supplied code prints on its own line.

Revisited (2026-10-04): fix-and-record — tree 10 states + 84
counties exact vs WP Counties of South Sudan + COD-AB SSD v03 +
commissioner-appointment press + WHO IDSR/OCHA bulletins.
Deleted 4 rows: `Districts of Sudan` and `States of South Sudan`
(See-also scrape junk under Jonglei, zero admin signals), `Lopa`
(Lafon/Lopa is a single county per the EES transitional-government
commissioner list — one entry "remains without a Commissioner" —
plus COD Eastern Equatoria-8; the split form survives only on
WP-list and in 2016 Imotong-era appointments), `Adior`
(Yirol East payam per the 2012 Lakes consultation and the 2020
Greater-Yirol placemat "Lakes State consists of 8 counties", plus
COD Lakes-8; county usage is 32-state-era Eastern Lakes only).
Renamed 2: `Vertet County` -> `Verteth` (GPAA chief-administrator
commissioner appointments + WHO IDSR bulletin spell it Verteth;
suffix dropped per naming convention), `Panrieng` -> `Pariang`
(COD-AB + HSBA Small Arms Survey + South Sudanese press; WP-list
says Panrieng but WP's own article is Panriang, so WP is
self-split). Kept against oracles: `Makal` (2021 resolution:
Makal is the county headed by a Commissioner, Malakal the
municipality headed by a Mayor; COD/WP-list `Malakal` is the
city-name confusion), `Akoka` (WHO IDSR Apr-2025 cholera report,
OCHA Aug-2026 snapshot, and a Kiir-appointed commissioner —
bundled is more current than COD-AB v03 here), `Bor` (article
present-tense "a county of Jonglei State"; COD `Bor South` has
no matching Bor North), `Raga` (WP `Raja County` redirects to
`Raga County`; COD `Raja` is a lone signal), short `Nasir`
(WP agrees; COD `Luakpiny/Nasir` is the compound alias),
`Center` spellings (WP agrees; COD `Centre` loses).
Parentage: the 7 Pibor-AA counties stay under Jonglei and the 2
Ruweng-AA counties (Abiemnom, Pariang) under Unity, matching
COD-AB v03's admin1 folding — humanitarian oracles do not model
the AAs as admin1. Pigi (= Canal), Pochalla North/South, and
the GPAA seven were already present and confirmed. Postal `none`
re-confirmed: WP List of postal codes "no codes" + UPU
General-Addressing-Issues doc — no CSVs is correct. Gate
`docs/agents/audit/gate_ss.py` pins the full tree.
## Eswatini

The bundled `EswatiniGeographyProvider` supplies the 4 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SZ')` after
countries are seeded.
The 59 inkhundlas ship as level-2 areas under their regions.

Eswatini addresses are formatted per the UPU layout: P.O. box lines,
the locality, the region-letter + 3-digit postcode on its own line,
and country.

Revisit 2026-10-03 (fix-and-fill: 55->59 tinkhundla, +4 rows, 2 entity
renames, 5 spelling renames, 80->81 codes/links; `gate_sz.py` ALL
PASS): the tree was stale at the pre-2018 55-count. The 55->59 change
took effect for the 2018 elections (2008/2013 were 55; 2018/2023 are
59): EBC 2018 official results + 2018 turnout (one oracle, same
commission) list 59 inkhundla at 15/11/18/15 per region; the 2023
general-election article confirms 59 constituencies "increased from
55 in the 2013 elections"; EBC 2023 winners/posters re-confirm the
59 names. An independent full 59-roster (Eswatini Observer national
army-recruitment schedule, Sep-Nov) agrees 54/59 spellings and
supplies the deciding second signal on four renames; it diverges
from EBC in five cells (Motjane, Zombodze, Shiselweni I/II,
Dvokodweni, LaMgabhi caps), proving it is not an EBC copy.

Per-region post-fix roster (EBC section + Observer block + ISO parent):

Hhohho (15): Hhukwini, Lobamba, Madlangempisi (held, see below),
Maphalaleni, Mayiwane, Mbabane East, Mbabane West, Mhlangatane,
Motjane (held), Ndzingeni, Nkhaba, Ntfonjeni, Piggs Peak (held),
Siphocosini (NEW: EBC + UNDP Hhohho blog + Times + Observer),
Timphisini (renamed from Timpisini: EBC + Observer + gov.sz
Tinkhundla service charter + Times + IFRC 2018 report).

Lubombo (11): Dvokodvweni, Gilgal (NEW ID, replaces Hlane: EBC has
no Hlane inkhundla; SADC ECF 2023 observer statement deployment
list + UN Eswatini World AIDS Day remarks "here in Gilgal in the
Lubombo Region" + Times/Africa-Press 2023 election coverage +
Observer roster + gov.sz new-tinkhundla map; Hlane survives only as
an EBC polling division under Dvokodvweni), Lomahasha, Lubulini
(renamed from Lubuli: EBC + Observer + World Vision FY24; counter:
Statoids-lineage + a parliament 2025 Tinkhundla report print
LUBULI, recorded), Lugongolweni, Matsanjeni North, Mhlume,
Mpolonjeni (renamed from Mpholonjeni: EBC + Observer recruitment
list + Observer roads article naming the same MP + new-Statoids),
Nkilongo, Siphofaneni, Sithobela.

Manzini (18): Kukhanyeni (renamed from Ekukhanyeni: EBC + Observer;
old-Statoids already listed Kukhanyeni as the variant),
Kwaluseni, Lamgabhi (kept: EBC + new-Statoids + ACE agree bundled;
old-Statoids primary Lamghabi dissents), Lobamba Lomdzala,
Ludzeludze, Mafutseni, Mahlangatja (held: EBC contradicts itself,
results MAHLANGATJA vs turnout Mahlangatsha; Observer +
Statoids-primary agree bundled), Mangcongco (kept: unanimous
across EBC x2, Observer, Statoids x2; the suspected EBC split did
not reproduce in fresh PDF extraction), Manzini North, Manzini
South, Mhlambanyatsi (renamed from Hlambanyatsi: EBC +
old-Statoids primary + Observer + Ministry of Agriculture Manzini
RDA list + UNDP-UNCDF report + new-Statoids), Mkhiweni,
Mtfongwaneni (kept: EBC + Observer agree bundled; Statoids-lineage
Mthongwaneni dissents 1-oracle), Ngwempisi (kept: EBC + Observer +
new-Statoids agree bundled), Nhlambeni, Nkomiyahlaba (NEW: EBC +
gov.sz service charter + Observer), Ntondozi (kept: unanimous),
Phondo (NEW: EBC + gov.sz service charter + Observer).

Shiselweni (15): Gege, Hosea, Kubuta, Kumethula (NEW: EBC + gov.sz
service charter KuMethula + Observer + ESCC newsletter Shiselweni
visit list; canonical lowercase-t per EBC + Observer), Maseyisini,
Matsanjeni South, Mtsambama, Ngudzeni, Nkwene, Sandleni,
Shiselweni I + Shiselweni II (held Roman: Observer agrees bundled;
EBC prints arabic 1/2), Sigwe, Somntongo, Zombodze Emuva (NEW ID,
replaces Zombodze: EBC 2018 results header ZOMBODZE/EMUVA +
turnout + 2018 winners + 2023 EBC posters all read ZOMBODZE EMUVA;
bird-story-agency 2023 report confirms "Zombodze Emuva
Constituency in the Shiselweni Region" electing its MP; Observer
shortens to Zombodze and Statoids prints Zombodze, both recorded
as variants).

Spelling renames keep stable source ids (never renamed for spelling
alone); only the Gilgal and Zombodze-Emuva entity renames turn
over ids. Other holds: Motjane (EBC Motshane outvoted by Observer
+ Statoids-lineage; H104 Motshane stays a postal place spelling),
Madlangempisi (EBC results MADLAMPHISI vs turnout + Observer +
Statoids; held), Piggs Peak (EBC results PIGG'S PEAK vs turnout
Piggs Peak; held per EBC-split rule; Observer prints Pigg's).
Nkilongo/Ntondozi/Mangcongco drew no dissent anywhere.

Postcodes 80->81: WP Postal-codes-in-Eswatini (76, youbianku-sourced,
one-source-flagged) + youbianku (81) agree H101 = Swazi Plaza, so
H101 is added with a Hhohho primary; the post-fix 81-set equals the
youbianku 81-set exactly (H 25 / L 18 / M 22 / S 16). Bundled extras
H121 Emsahweni, H124 Mahlanya, H125 Ebuhleni, H126 The Gables, M223
The Hub are all confirmed by youbianku cells and kept. UPU SWZ
profile anchors H100 Mbabane and the 1-letter + 3-digit format.
Links stay region-level primaries following the code letter, so M211
Sithobela + M214 Siphofaneni keep Manzini links although those
tinkhundla sit in Lubombo (postal/admin boundary mismatch, kept by
design). The old overlay note claiming H103 shared Eveni/Swazi
Plaza is dropped: both directories give H103 = Eveni and H101 =
Swazi Plaza. Pins live in `EswatiniGeographyProviderTest` and
`gate_sz.py`. Gaps: no inkhundla-level postcode mapping exists in
any source (directories are locality-based), so links remain L1.

## Togo

The bundled `TogoGeographyProvider` supplies the 5 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TG')` after
countries are seeded.
The 39 prefectures ship as level-2 areas under their regions.

Togo has no postcode system. Addresses are formatted per the UPU
layout: P.O. box or street lines, the locality, the region when it
differs, and country; any supplied code prints on its own line.

Revisit record: the tree was verified name by name against the
Prefectures of Togo list (5 regions, 39 prefectures with region
parents: 7 Savanes, 7 Kara, 5 Centrale, 12 Plateaux, 8 Maritime),
including the nine post-ISO prefectures (Kpendjal-Ouest, Oti-Sud,
Cinkassé, Mô, Akébou, Anié, Kpélé, Agoè-Nyivé, Bas-Mono) that
distinguish the 39-count tree from stale 30-count lists. The
no-postcode verdict was confirmed three ways: the UPU TGO profile
(B.P.-based format, no postcode field), no GeoNames postal dump for
TG, and the countries-without-postal-codes directory listing. No
data changes; pins live in `TogoGeographyProviderTest` and
`docs/agents/audit/gate_tg.py`.
## Tunisia

The bundled `TunisiaGeographyProvider` supplies the 24 governorates
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('TN')` after
countries are seeded.
The 279 delegations ship as level-2 areas under their governorates
(INS 2024 figure, superseding the older 264).

Tunisian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

Revisit 2026-10-03 (fix-and-fill: +174 codes / +176 links, 795→969
codes, 806→982 links, 213→258 delegations covered; `gate_tn.py` ALL
PASS): tree re-verified exact against ISO 3166-2:TN (24 codes; articles
dropped, Kebili/Medenine unaccented) + Delegations of Tunisia (279/279
names). Fresh 4,860-row Mapanet TN re-pull code-set agrees the
Postal-codes-in-Tunisia JSON 969/969 (the old 795-set dropped codes
under transliteration-mismatched r2s); fills attributed by Mapanet r2
with WPC-town agreement, the La Poste Mar-2025 office roster (29
codes), and Nominatim/OSM (seats 3000/4000/8000, 12 namesake offices,
Sfax Ouest post-office nodes). All 9 old shared-code primaries kept
after re-adjudication (ties/singles never move); 2 new multis (1008
Médina, 2089 Le Kram). Singleton sweep: 15 agree, 0 corroborated
errors. Deliberate skips: 7 La Poste-newer codes (single-signal),
UPU-example-only 8129 (in no directory), 21 structural-codeless
delegations (no/empty r2, no directory code).
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

Revisit 2026-10-04 (fix: 1 rename, no count changes; `gate_zm.py`
ALL PASS): all 10 provinces ISO 3166-2:ZM-exact (01–10) and all
116 districts diffed name-by-name against Districts of Zambia
(April-2018 116 set; per-province 11/10/15/12/6/8/12/11/15/16
exact). Only fix: `Mansa District, Zambia` page-title scrape →
`Mansa` (WP + Statoids). Postal verdict stays none: the UPU zmbEn
profile (01/2013) defines the 5-digit slot but states the actual
codes "have not yet been assigned and the coding method is yet to
be defined" (all examples are placeholders), the UPU Sep-2025
list keeps Zambia require-side on paper only, and GeoNames has no
ZM postal dump (404).
## Zimbabwe

The bundled `ZimbabweGeographyProvider` supplies the 10 provinces
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('ZW')` after
countries are seeded.
The 64 districts ship as level-2 areas under their provinces.

Zimbabwe has no postcode system. Addresses are formatted per the UPU
layout: street lines, the suburb, the city, and country; any supplied
code prints on its own line.

Revisit 2026-10-04 (verify-only, zero changes; `gate_zw.py` ALL
PASS): 10/10 ISO 3166-2:ZW provinces; 64/64 districts with exact
per-province counts (1/3/7/8/9/7/7/7/7/8) and names vs the en-wp
Districts list. Second signals: Statoids yzw covers 60/64 (the 4
post-vintage splits Mbire, Mhondoro-Ngezi, Sanyati, Vungu confirmed
separately — Mbire/Mhondoro-Ngezi/Sanyati as ZimStat census
Districts via citypopulation, Vungu via the official Vungu RDC
site + "Vungu District" press usage). The WP Harare section's 15
extra entries (Glen View, Budiriro, Borrowdale, Mabvuku, …) are
suburb pollution, not districts: contradicted by the article's own
64 lede and absent from both Statoids and census — bundled Harare
correctly keeps Harare/Chitungwiza/Epworth. No-postal: UPU
do-not-require list + GeoNames ZW.zip 404.

## Albania

The bundled `AlbaniaGeographyProvider` supplies the 12 counties
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('AL')` after
countries are seeded.
The 61 municipalities ship as level-2 areas under their counties.

Revisit 2026-10-03 (fix-and-fill: 138 retargets + 36 fills + 1 drop,
489 → 524 codes, 490 → 525 links; `gate_al.py` ALL PASS): tree
re-verified clean against ISO 3166-2:AL + Counties/Municipalities of
Albania (12 county codes match; all 61 names and parents match;
Dimal is the current 2021 name of Ura Vajgurore). The GeoNames-only
overlay under-linked at old-district granularity (31/61
municipalities): 138 codes retargeted to the office's own
municipality on official-office-name + Law 115/2014 roster +
directory/gazetteer agreement (whole-block moves: Librazhd→Prrenjas,
Mat→Klos, Skrapar→Poliçan, plus Vau i Dejës ×12, Mallakastër ×9,
Himarë ×9, Selenicë ×8, Maliq ×8, Devoll ×7, Dimal ×6, Divjakë ×6,
Shijak ×6, Finiq ×6, Rrogozhinë ×5, Dropull ×5, Fushë-Arrëz ×5,
Roskovec ×5, Delvinë ×4, Konispol ×4, Cërrik ×4, Belsh ×4, Patos ×3,
Libohovë ×3, Kuçovë +5013 Lumas, Pustec 7020); +36 fills from the
official Posta Shqiptare branch list (Gramsh 3301–3310, Peqin
3501–3506, Tepelenë 6301–6311, Përmet 6401–6409 —
postzipcode-corroborated); 8707 dropped (GeoNames-only franken-row;
Tropojë caps at 8706 in the official list, postzipcode, and the
Bajram Curri infobox). 1029 keeps the Kamëz primary + Tirana
secondary (2017 official coverage doc says Bashkia Kamez; GN Tiranë
row retained). Homonym keeps: 3019 Mollas + 3025 Shushicë Elbasan,
2504 Golem Kavajë, 1046 Selitë Tirana, 1507 Sukth Krujë, 4511/4506
Lezhë, 8511 Kukës, 7024 Dishnicë Korçë, 9335 Selitë Mallakastër,
4604 Selitë Mirditë. Weak keeps: 8520 Morinë customs (GN-only,
Kukës), 5021 Ura e Kuçit (Berat; Dimal/Kuçovë unresolvable without
a current coverage doc). 61/61 municipalities covered; no new areas.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_ad.py` ALL
PASS): 7/7 ISO 3166-2:AD codes; GeoNames AD dump confirms all 7
code→parish links with admin1, UPU andEn anchors AD700
Escaldes.
## Austria

The bundled `AustriaGeographyProvider` supplies the 9 states
(Bundesländer) as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AT')` after countries are seeded.
The 79 districts (Bezirke) and 14 statutory cities
(Statutarstädte) ship as level-2 areas under their states.

Austrian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

Revisit 2026-10-04 (fix-and-fill, 16 primaries retargeted + 1 secondary
added; `gate_at.py` ALL PASS): tree re-verified exact — 9/9 ISO 3166-2:AT
states plus 93/93 L2 (79 districts + 14 statutory cities) matching WP
Districts of Austria on code, name and city-type, with Statistik Austria
confirming the merged Bezirke Murtal and Bruck-Mürzzuschlag; no 324
Wien-Umgebung and no pre-2012 Styria codes, Vienna L1-only by design.
Fresh GeoNames AT dump (19,225 rows / 2,501 codes) matches the bundled
code set exactly (1000–9992, 4-digit per UPU AUT). The build's
row-majority rule was contradicted on 11 codes where GN city votes had
been mapped to the surrounding district: 3100/3104/3105/3107/3109/3140/
3151 → St. Pölten city and 2703/2705/2706/2707 → Wiener Neustadt city
(WP city infoboxes, Nominatim centroids, official 3109 Landhausplatz
addresses, GN 18:0/13:0/5:1/4:2 majorities); 2700 joins the city on the
same evidence (its 4 GN district rows are wrong-code duplicates homed at
2721/2722/2801/2493). Four dupe-built majorities corrected: 1140 Penzing
and 1210 Floridsdorf → Vienna state, 2231 Strasshof → Gänserndorf, 2680
Semmering → Neunkirchen (Nominatim + GN-internal displacement, each
2+ signals). 3140 gains a St. Pölten-district secondary (2 genuine
Böheimkirchen-village rows). All 121 kept secondaries re-verified at 2+
GN rows; x1 minorities stay dropped. Holds: 2751/2752 Wiener Neustadt
district and 3385 St. Pölten district (GN majority + Nominatim beat the
loose de.wp city lists), all genuine-tie PLZ-map breaks (2381/2413/2460/
2473/2485/2663/3973/4550 seat-rule/5562/6182/6314/6850/7033/7212/8291/
8293/8924/8974 plus the 2:2+ duals), 2702 closed-branch and 2704
de.wp-only absent per the unverified-exclusion precedent (as 8471/8565/
9104). Post.at no longer exposes a static PLZ finder URL (JS-walled
online-services), so OSM/Nominatim + de.wp infoboxes + GN-internal
displacement carried the second signals. Per-state primaries:
Burgenland 153, Carinthia 215, Lower Austria 647, Salzburg 143, Styria
376, Tyrol 290, Upper Austria 449, Vienna 127, Vorarlberg 101; every L2
area holds at least one primary (Rust and Waidhofen/Ybbs singletons).
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

Revisit 2026-10-04 (fix-and-fill: 29 link retargets + 2 drops + 19 fills;
3140 codes / 3140 links; `gate_by.py` ALL PASS): the 7-unit L1 / 118-raion
tree re-verified exact (ISO 3166-2:BY BR/HO/HM/HR/MA/MI/VI; raion names and
parents 118/118 vs the Districts-of-Belarus list; per-oblast counts
16/21/21/17/22/21 confirmed against 2023 official estimates — no post-2020
raion changes; Minsk city rayons correctly not separate L2 rows). The fresh
GeoNames BY dump is 3,133 rows / 3,123 unique codes — exactly the shipped set,
zero drift — and its 10 dup rows reconcile as the documented 5 adjudicated
1v1 ties (211227→Lyozna, 211657→Polotsk, 220024→Minsk city, 222374→Myadzyel,
222834→Pukhavichy, all re-confirmed, with three losers' true codes recovered:
Kholopenichi 222024, Volma 222734, Urechye 223834; the other two proven
misfiled rows) plus 5 harmless same-admin2 dupes. The overlay's systematic
failure was GeoNames-side: GN has no Byaroza-raion admin2, so all 25
Byaroza-cluster codes (225205–225247) shipped under Brest district and Byaroza
stood as the only unlinked district — now retargeted on Mapanet raion pages,
Belposhta-family addressed usage, and place/coordinate evidence. Also
retargeted: 211440→Polotsk (Novopolotsk container), 211620→Verkhnedvinsk
(raion center), 231470→Dzyatlava (Novoelnya), 247711→Kalinkavichy (zone-law +
branch-list Vorotyn); dropped corrupt rows 213918 (transposed Vawkavysk code)
and 247047 (one-digit corruption of Pechishchi 247407); filled 19 town/city
codes (Kholopenichi 222024, Pechishchi 247407, Verkhnedvinsk 211631, Klichev
213910, Talachyn 211091/211092, Novopolotsk 211441/211443–211449/211500/211501,
Vawkavysk-city 231891/231894/231896), each with two independent signals (the
integrator fetched the Mapanet Talachyn-town page directly to second-signal
211091, since the worker's pull cache lacked it). Oev=Loyew (shared admin2code
625906) and the empty-admin Rodno row 231778 (Berestovitsa) re-confirmed.
Holds: ~70 Mapanet-only extras (single family, incl. Byaroza-town
225203/225204/225208 which the branch list does not show), the wiki/Mapanet
231894-vs-231918 Dulevtsy conflict, and 211451 (Osveya-run keep, medium
confidence). EOL: areas LF, postal files CRLF.
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

Revisit 2026-10-03 (verify-only, zero data changes; `gate_be.py` ALL
PASS): the 3-region / 10-province tree re-verified exact against
ISO 3166-2:BE (BRU/VLG/WAL + VAN/VOV/VBR/VLI/VWV/WBR/WHT/WLG/WLX/WNA;
all names identical, Liège keeps its accent, Brussels-Capital is the
conventional English name; 5 Flanders / 5 Wallonia, Brussels
childless). The 1146-code set is exactly the fresh GeoNames BE dump
(2,781 rows, 1000–9992, zero added/retired); every link agrees with
the unanimous per-code GN admin2 vote and the fresh dump confirms
zero cross-province codes. Nominatim/OSM independently corroborates
all 12 seat anchors (1000 Bruxelles, 2000 Antwerpen, 3000 Leuven,
4000 Liège, 5000 Namur, 6000 Charleroi, 7000 Mons, 8000 Brugge,
9000 Gent, 1300 Wavre, 3500 Hasselt, 6700 Arlon) plus the Voeren
(3790–3798 Limburg), Comines-Warneton (7780–7784 Hainaut), and
Mouscron (7700–7712 Hainaut) exclaves, the split Brussels periphery
(1640/3080 Flemish Brabant vs 1420/1330 Walloon Brabant), and the
language-border pair (9600 Ronse East Flanders, 7750 Mont-de-l'Enclus
Hainaut). The bundled data reproduces the documented range-sharing
(1xxx across BRU/VBR/WBR, 3xxx across VBR/VLI, 6xxx across WHT/WLX),
ruling out first-digit joins. Institutional specials (1005/1010/1044/
1045/1047/1048/1049 class) stay held out: GeoNames omits them,
Nominatim has no postcode areas for any of the seven probed, and they
are documented reserved numbers for EU institutions, NATO, the
broadcasters, and the parliaments (VC0100 box-only precedent). Watch
item: bpost publishes no downloadable postcode list (finder URLs 404,
homepage exposes no lookup API), so set-drift detection rests on the
GeoNames dump plus addressed-usage news, which shows no 2024–2026
bpost changes; re-check next revisit.
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

Revisit 2026-10-04 (B14 postal fix, `gate_ba.py` 0 FAILURES, tree
untouched): the 143-municipality tree matches the WP entity tables
with zero diffs (79 FBiH + 64 RS; `Istočno Sarajevo` city row kept
per the WP RS table; `Kupres` ×2 and qualified `Trnovo` twins
intentional; FBiH section omits `Široki Brijeg` but the intro
counts 79). Postal 517 codes / 570→575 legs / 50→53 multis:
71000 completed to all 4 Sarajevo-city municipalities (UPU anchor
plus BH Pošta `71000 Sarajevo-Dostava` plus Novi Grad kontakt plus
city structure); 71123 gained Istočno Novo Sarajevo (Pošte Srpske
`71123 Istočno Sarajevo, Zmaj Jovina 9` sits in Lukavica — the same
number is also BH Pošta's Grbavica unit, a genuine operator
overlap); 74208 re-primaried Stanari over Doboj (Pošte Srpske plus
municipality seat, 2014 split); 77253 re-primaried Bosanski
Petrovac over Bihać (PostNet Krnjeuša office plus settlement
article, border zone); Bijeljina main swapped 76000→76300 (retired
SFRY code, town plus 7 usage signals, no village user left).
Bulk-checked: PostNet∩bundle 82 codes agree 78 (2 name-forms,
71123 overlap-case, 77253 fixed); Pošte Srpske∩bundle 52 agree 50
(12 suburb-mappings correct, 71123/74208 fixed). Held: 16 Sarajevo
plus Banja Luka / Mostar / Prijedor / Foča unit codes (office
grain below the routing-manual delivery grain — 73301/79101 held
because WP confirms town delivery sits on 73302/79102);
71126/71213/71216 (directory- or WP-single); 75000/76100 retired
mains; 76000 dropped but 75108/78429 kept as uncontested
manual-delivery (PostNet is a partial PostNet-system list, so its
absence proves nothing); 71335 Pržidi stays omitted per build
note. Build note said 55 dual-linked vs 50 found — post-fix 53
multis / 58 extra legs; the historic drift has no build snapshot
to reconcile against.

Revisit 2026-10-06 (OPEN #12 fill, `gate_ba.py` 0 FAILURES, tree
untouched): the 2013 HP routing table (561 codes with operator
tags) plus all three live operator networks settled delivery-vs-unit
grain for every WP-only code. Postal 517→563 codes / 575→625 legs
/ 53→57 multis: 37 PROVEN-live fills (15 Sarajevo BHP units on the
WP municipality split, duals 71124 Novo-Sarajevo-primary and 71213
Ilidža-primary with Istočna Ilidža secondaries, Kiseljak 71275/71335
— overturning the 71335 build-note omission — 72293 Opara, Orašje
76281, Odžak 76298, Livno 80202/80205, Tomislavgrad 80246, 10 Mostar
units, Široki Brijeg 88221, Ljubuški 88322) plus 10 beyond-65 proven
(71126 Lukavica, duals 71214/71216 Ilidža-primary, 73300 Foča, 79101
Prijedor, 74231 Sivša, 74273 Banja Vrućica, 71212, 71218, 71323
Kobilja Glava). Drops: 79293 erroneous Opara (two operator lineages
plus block fit contradict the shared WP/PBN error; replaced by
72293 in the same pass); retired 74321/75000/88267 confirmed absent
with successors 74231/75101/88266 live. Held in gate + bundle: the
10 LIKELY-live Hodovo-class delivery codes (medium-high, one
addressed sighting each unblocks: 76234/76271/76276, 80203, 88327,
88365/88366/88368/88375/88395), the 15 likely-retired unit codes
(71145, 76100, 76231, 80241, 88005, 88101, 88222, 88241, 88242,
88261, 88264, 88301, 88302, 88321, 88341), and all single-lineage
singletons (HP/BHP/PS finder-only and HP-route-vintage codes need a
second signal each). Weak keeps flagged for review: 78108 (directory-
only), 78249 (Razboj link-target suspect), 74221 (directory-only);
75248 Čelić secondary leg held (HP-route single signal). The 55-vs-50
build note is recorded as dropped-unreconcilable: no build snapshot
exists and the multi-count moved again with the newly proven duals.

## Bulgaria

The bundled `BulgariaGeographyProvider` supplies the 28 districts
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BG')` after
countries are seeded.
The 265 municipalities ship as level-2 areas under their provinces.

Bulgarian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode, and country.

### Revisit (B17, 2026-10-05)

Postal files verified to 4351 codes / 4363 legs / 12 dual-linked
municipality codes (`gate_bg.py` ALL PASS):

- 22 fills for GN-live village codes missing from the bundle
  (each GeoNames + bg.wiki + census/office/OSM): Nevestino 2655
  Murvodol + 2658 Dolna Koznitsa, Septemvri 4446/4456 Gorni/Dolni
  Vurshilo, Smolyan 4848 Chamla, Laki 4888 Dzhurkovo (48xx
  cross-region service), Zlataritsa 5156 Cheshma, Dryanovo 5399
  Runya, Stara Zagora 6233 Pustrovo, Kardzhali 6631 Prileptsi,
  Momchilgrad 6832 Vrelo + 6838 Momina Sulza, Dzhebel 6839
  Kuptsite, Kirkovo 6863 Kayaloba + 6886 Zavoya + 6897 Samokitka,
  Omurtag 7918 Kozma Prezviter, Primorsko 8289 Pismenovo,
  Sredets 8339 Trakiytsi, Dobrichka 9495 Vodnyantsi + 9496 Altsek,
  Varna 9024 Topoli.
- 34 removals: Teteven dead block (13: 5721/5722/5736-5739/
  5742-5745/5747-5749) and Yablanitsa dead block (6: 5735/5751/
  5752/5766-5768) — live 1:1 village sets complete without them
  across wiki + offices + GeoNames, zero OSM trace; 13 orphan
  singles with complete live-sets and zero trace anywhere
  (9633/4476/3521/2906/3163/8258/4577/8839/8840/8143/3036/
  9434/9435); numbered town-branch codes 6609 Kardzhali-9 and
  7101 Byala-1 (quarter branches, no village claims, precedent:
  5701/Sliven-8801s excluded).
- Malko Tarnovo renumber 835x→816x, REVERSING the build note:
  the operator (bgpost offices 8162 town + 8166 Gramatikovo +
  8170 Zvezdets), Google/hotel addresses (Brashlyan 8163,
  Gramatikovo 8166, Stoilovo 8165), OSM mapper tags (town 8162,
  Stoilovo 8165), and a Nov-2017 government tender (Zvezdets
  8170) prove 816x live; mapanet/youbianku/worldpostalcode 835x
  is the stale lineage the bundler trusted. Moved 8350→8162,
  8357→8163, 8359→8165, 8370→8166, 8360→8170. The other 8
  villages (Bliznak 8365, Byala Voda 8361, Evrenozovo 8363,
  Zabernovo 8367, Kalovo 8368, Mladezhko 8364, Slivarovo 8358,
  Vizitsa 8369) are HELD on 835x: no live 816x successor found
  anywhere (partial renumber vs undiscovered values).
- Dual-carrier fix 2789 +belitsa leg (Galabovo, GN + wiki +
  census); 2791 yakoruda leg removed (Avramovo=2795, no Yakoruda
  village is 2791); 6190 Gurkovo leg HELD (GN-only Zhergovec,
  mapcarta is a stale OSM snapshot).
- Office-wins rule established: unnumbered village offices carry
  delivery codes and beat stale wiki+GeoNames pairs in 7 cases
  (2096/2190/3264/5173/7685/9822/9494 stay out; live codes 2076/
  2166/3056/5136/7641/9818/9433 kept) — wiki flips were anonymous
  uncommented 2005-2010 edits, GeoNames echoes them. Proven GN
  stale blocks: Mirkovo-209x, Nesebar-822x, Malko-835x,
  Ivaylovgrad/Topolovgrad-69xx/87xx. Station localities kept
  (4410/5120/6489/6517/8604 Гара-X); resort delivery codes kept
  (9006/9007 Golden Sands + St. Konstantin hotel-used, 8240
  Sunny Beach, 9620 Albena).
- Held (single-signal, kept as-is or kept out): 24 orphan singles
  with unattributed villages, 4 wiki-only (6071/6553/6554/6864),
  8 GN-only opens (2866/4846/5157/5442/5443/5467/6950/9183),
  6843 Turnovtsi (GN-only code), 6190 Gurkovo leg, Malko-8.
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

### Revisit (B20, 2026-10-06)

Verify-only, zero data changes (`gate_hr.py` ALL PASS).
Tree 577/577 exact (name + type + parent, diacritics) vs
two independent compilations — WP towns + municipalities
lists (NN/Ministry-sourced) and citypopulation.de (DZS
census); L1 codes 01–21 = ISO 3166-2:HR, corroborated by
the NN Territories Act and HP's 21 `zupanija` values;
Zagreb town correctly under the City of Zagreb. Postal
1094/1094 HP-covered (900 settlement + 194 office/box-only
per the UPU xx1/xx2 rule; HP office directory re-pulled
2026-10-06, byte-identical); 1089/1094 legs unanimous
across HP-settlement + HP-office + GN, 5 hand-reviewed
keeps (10290/10456 county legs beat GN/office quirks;
10253/10373/10361 city legs follow the UPU office-owns-code
rule). Holds: 10004 customs office + 31200 stale GN code
absent (not filled); 3 L1 display names vs ISO-en held as
convention (Zagreb, Vukovar-Syrmia, City of Zagreb).

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

Revisit 2026-10-04 (fix-and-fill, `gate_cz.py` ALL PASS
post-state; pre-fix FAILS exactly on the 15 added rows):
90 areas / 2694 codes / 2723 -> 2738 links (+15 secondaries,
zero primary moves, zero code adds/removes).

Oracles: GeoNames CZ dump (current, 15507 rows / 2694 codes);
ISO 3166-2:CZ (14 regions + 76 districts); WP Districts of the
Czech Republic (76; regions 13+1); OSM Nominatim postcode
boundaries + ~40 place checks + node-provenance check; Wikidata
P281 referenced to RUIAN (Q12049125) for 20 municipalities/parts;
cs.wikipedia infoboxes; UPU CZE profile (5-digit NNN NN). Ceska
posta PSC finder unreachable (psec/psc subdomains down, old
finder URLs 404) and RUIAN VDP has no scriptable search/VFR
endpoint, so RUIAN-ref WD P281s substituted for the post-office
oracle (each corroborates an in-dump GeoNames row, never used
alone except where noted).

Tree: 14/14 ISO regions (13 kraj + Prague capital_city, L1-only
-- current ISO defines no Prague district, so the bundled
capital_city-without-L2 design is correct) and 76/76 districts
with codes, Czech names, and parents exact, incl. 20A/20B/20C.
Per-region membership 7/7/3/5/5/4/6/5/4/7/12/7/4. WP roster
agrees (76 districts, 13+1 regions).

Codes/primaries: bundled 2694 = GeoNames 2694, zero diff both
ways, range 100 00-798 62, all NNN NN, sorted, unique. All
2694 primaries equal the GeoNames row-majority district.
Prague block airtight: 58 codes 100 00-199 00 <-> capital_city,
zero cross-rows either way in GeoNames (320 Praha rows).
Ties keep bundled primaries per OSM boundaries + stability:
507 91 Jicin 4:4 (Stara Paka office, okres Jicin), 544 43
Trutnov 1:1 (Kuks office, okres Trutnov), 569 94 Svitavy 1:1
(Teleci, okres Svitavy). Thin keeps: 463 42 Liberec 10:9,
788 25 Sumperk 4:3. Every district's primaries share exactly
one routing first-digit. Per-region primaries: Prague 58,
Stredocesky 411, Jihocesky 241, Plzensky 136, Karlovarsky 48,
Ustecky 218, Liberecky 130, Kralovehradecky 234, Pardubicky
191, Vysocina 213, Jihomoravsky 289, Olomoucky 174, Zlinsky
134, Moravskoslezsky 217.

Fill (+15 secondaries; GeoNames x1 row + RUIAN-ref P281 each;
primary = majority, unchanged): 273 51 Praha-zapad 11:1
(Cerveny Ujezd, Q590716); 289 14 Kolin 2:1 (Poricany,
Q2063995); 294 13 Liberec 16:1 (Chlistov/Vselibice, Q1633354);
321 00 Plzen-jih 2:1 (Slovice/Dobrany, Q1019360); 334 52
Domazlice 9:1 (Haje/Srbice, Q2035022); 357 35 Karlovy Vary 7:1
(Mirova, Q1957786); 364 64 Sokolov 7:1 (Nova Ves, Q1818257);
380 01 Trebic 44:1 (Radkovice u Budce, Q247659, cross-prefix
but RUIAN-confirmed); 385 01 Klatovy 33:1 (Horska Kvilda,
Q1629025); 507 13 Semily 15:1 (Bradlecka Lhota, Q896989);
517 61 Usti nad Orlici 4:1 (Zahory/Kunvald, Q1756946);
539 44 Svitavy 19:1 (Priluka, Q1418973); 563 01 Svitavy 28:1
(Koruna, Q2702179); 675 26 Jihlava 8:1 (Jindrichovice,
Q2053735, cross-prefix but RUIAN-confirmed); 783 42 Prostejov
3:1 (Slatinky, Q2024658). Precedent: 544 43/569 94 show x1
minorities are link-worthy when genuine.

Drops kept (29): in-dump duplicate proof -- 256 01 Olsany
(true 286 01), 301 00 Lhota (true 334 52), 431 51 Smilov
(true 364 01); RUIAN-ref true-code differs -- 391 65 Nuzice
(Tyn 375 01), 394 68 Panske Dubenky (378 53), 415 01 Roudniky
(Chabarovice 403 17), 793 51 Mutkov (783 97/785 01); OSM
true-code differs -- 257 56 Paseky (257 48), 285 04 Cerveny
Hradek (281 43), 335 01 Osobovy (335 54), 349 01 Chotesovicky
(330 34), 342 01 Lhota pod Kustrym (341 66), 506 01 Holenice
(507 15), 751 03 Majetin (751 06), 751 31 Slavkov (751 23),
503 51 Vlkov nad Lesy (503 62), 281 26 Labske Chrcice
(533 12), 441 01 Nahorecice (364 55); GeoNames admin2
mislabel, place belongs to the majority side -- 264 01
Bolechovice (Pribram), 326 00 Letkov (Plzen-mesto), 407 11
Decin XXX-Velka Velen (Decin, by name), 463 53 Janovice
v Podjestedi (Liberec), 566 01 Tynistko (Usti nad Orlici),
753 62 Lubomer pod Straznou (Prerov), 783 83 Lipinka
(Olomouc), 507 03 Kozojidky (Hodonin -- total misfile);
namesake split -- 252 10 Chouzava (Pribram one is 262 04,
the 252 10 one is Praha-zapad/Kytin); no corroboration --
257 91 Vratkov (OSM suggests Vratkov/Kolin namesake).
384 01 stays Prachatice-only: the 1:1 Kutna Hora row is
Chlistovice filed with Nebahovy's EXACT coordinates,
cross-prefix, and duplicated in-dump under 285 22 (note:
the old overlay's "real 284 01" is unconfirmed -- the
in-dump duplicate reads 285 22; either way a Kutna Hora
code, so the drop stands regardless).

Holds/gaps (stay single-linked per stability): 285 09
Benešov? -- GeoNames x1 Peliskuv Most but cs.wiki/WD say
256 01, and the OSM node's 285 09 is Nominatim boundary
interpolation (node 1600656052 carries no addr:postcode),
so only 1 real signal; 331 62 Karlovy Vary? -- GeoNames x1
Chlum only, Psov seat is 364 52, needs part-level RUIAN;
353 01 Sokolov? -- GeoNames names Louka (OSM: Louka=354 83)
while WD says Nova Ves municipality uses 353 01,
part-level conflict, needs RUIAN VFR. Granularity gap:
bundled inherits GeoNames settlement-level codes (2694);
Prague is the only street-level block (58 codes / 320
rows). File mechanics: areas LF-only; codes+links CRLF
with trailing CRLF, preserved by the applier.

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

Revisit 2026-10-04 (B12 verify-only, zero changes; `gate_dk.py`
64 checks ALL PASS): 103 areas (5 regions + 98 municipalities),
1,159 codes / 1,159 links, all single-primary. Fresh GeoNames
DK.zip (dump 2026-10-03) matches the bundled code set exactly
(zero diff both ways, range 0800-9990, NNNN per UPU DNK 05/2024)
and all 1,159 primaries equal the GeoNames admin2 kommunekode
join; GeoNames admin1 cross-checks region parents 1159/1159.
Tree: 5/5 ISO 3166-2:DK regions + 98/98 post-2007 municipalities
WP code/name/parent-exact (per-region 29/22/19/17/11), SDS
official codes 98/98 (only delta: 260 is current Halsnæs, ren.
2008). No post-2007 mergers; Ertholmene correctly outside any
municipality. The da-WP-stale five 1311/4942/5943/8981/8983 are
OSM-confirmed live (kept: 2 fresh signals beat 1 stale); the
reinstated islands 4244/4245/4945 (2017) verified. Excluded by
design: outlet/service/company/terminal codes (937 da-WP actives
categorized), GL 39xx, FO 38xx, unassigned 10xx-19xx reserves.
Per-region codes: Capital 641, South 167, Central 146, Zealand
129, North 76. Holds: 0917/0960 single-signal joins kept per
stability; PostNord finder Cloudflare-walled (operator sweep
deferred); DAWA retired (410). Watch: Capital + Zealand merge
into Region Østdanmark on 2027-01-01 (L1-only; revisit due).

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

Revisit 2026-10-04 (B11 fix-and-fill: 11 EHAK cells + 84 fill codes;
`gate_ee.py` ALL PASS post-state, 9 change-pinning FAILs pre-state).
Tree: 93 areas = 15 EHAK counties + 78 municipalities (63 rural + 15
urban), the post-2017-reform 79 minus Toila, which merged into Jõhvi
28.11.2025 (Government regulation 29.04.2025 No. 30; WP Municipalities/
Toila Parish/Jõhvi Parish; ADS no longer lists Toila vald — "Toila
vald" resolves to Jõhvi vald, Toila alevik). 78 is the correct
post-merger roster, confirmed by the stat.ee EHAK changes doc ("78
omavalitsust, 63 valda, 15 linna"), the EMTA KOV table (78 rows, no
Toila), and WP Administrative divisions ("78 municipalities from 28
November 2025"). 11 `code` cells fixed to current EHAK, each triple-
signalled (Maa-amet ADS In-Aadress ehakov + EMTA KOV table + stat.ee
EHAK changes doc with regulation dates): Lääne-Harju 430->431 and
Lääneranna 431->430 were swapped at build time (ISO 3166-2:EE EE-431/
EE-430, GeoNames 0431/0430, and the 2019 EHAK doc's "37 431 8" agree);
Jõhvi 251->250 post-merger ("Jõhvi valla uueks koodiks saab 0250",
28.11.2025); Saue 726->725 + Märjamaa 503->502 (17.07.2020);
Sillamäe 735->736 + Narva-Jõesuu 514->515 (01.01.2023); Valga 855->857
+ Antsla 142->145 (01.01.2024); Põhja-Pärnumaa 638->637 + Tori 809->806
(01.01.2025). All other 67 municipality codes and all 15 county codes
re-verified current (EMTA==bundled==ISO/GeoNames; ADS spot confirmations
incl. Kiili 305, Saku 719, Kohila 317, Saarde 712, Pärnu 624). Parents
follow names and are all correct; the only ISO-parent mismatches were
the 430/431 pair. Existing test pins kept, with Põhja-Pärnumaa 638->637.
Codes/links: the GeoNames EE postal dump (5398 rows / 5293 distinct
codes, current pull) is fully contained in bundled (zero GeoNames codes
missing) with 5289/5293 per-code admin2 sets exact after mapping Toila
rows to Jõhvi (30 codes, incl. dual 30503), Kiili 0304->305, Saku
0718->719 (GeoNames carries pre-2019 codes), and the 0430/0431 names.
The 4 set-diffs are the town-set city primaries 74114/74115 (Maardu over
GeoNames Jõelähtme x3 / Jõelähtme+Tallinn) and 70101/65555 (Viljandi/
Võru city over single rural rows), each corroborated by postiindeks.ee
place pages (74114/74115 "Maardu linn", 70101 "Viljandi linn", 65555
Võrumõisa+Kirumpää+Võru linn over 608 addresses; ADS shows Võrumõisa
tee straddling the Võru city/rural boundary). All 14 GeoNames
multi-admin2 codes are dual-linked (majority primaries 10112 Tallinn
2:1, 76902/76912 Harku 2:1; 1:1 ties broken to the town side except
rural-rural 45202 Haljala first-row) plus the 3 city-add duals = 17
duals / 18 secondaries (74115 triple), all same-county. The 104
GeoNames-missing town-set extras verified intact (Narva 35, Viljandi
20, Rakvere 14, Võru 11, Keila 8, Maardu 7, Sillamäe 5, Loksa 4);
ADS street probes confirm sampled extras (Kreenholmi->21008,
Jaama->76605, Kallavere tee->74117, Tallinna mnt->20304) and Omniva's
locations feed confirms the postkontor base codes 44301/65601/74101/
76601. Fill: postiindeks.ee (5436-code index, ADS-derived, updated
2026-07-16) lists 93 codes outside bundled; each was probed at
street/farm level against ADS and 84 carry exact pii+ADS sihtnumber
agreement with ADS municipality: Narva 21026-21076 x50 (21065
unassigned everywhere), Noarootsi 91201-91233 x21 (91207/91215 already
bundled; 91209/91210/91222-91229 in no universe), Viimsi 74022-74024,
Hiiumaa 92141/92179, Antsla 66304/66306, Tartu-vald 60545, Kehtna 79054,
Tallinn 13525, Peipsiääre 60429, Viljandi-vald 70182, Võru-vald 65501 —
all added single-primary (5481 codes / 5499 links). A 12-code random
sample re-verified pii place == GeoNames place == bundled link. EOL
preserved exactly: areas LF, codes CRLF, links CRLF. Oracles: GeoNames
EE.zip (current), Maa-amet ADS In-Aadress (~310 gazetteer probes:
sihtnumber + ehakov/omavalitsus), EMTA land-tax KOV table (78 rows),
stat.ee EHAK changes doc (20 pp.), ISO 3166-2:EE (pre-merger 79
baseline), UPU EST (5-digit postcodes), Omniva locations.json
(2026-09-24; postkontor ZIPs + 96xxx locker range excluded),
postiindeks.ee index + ~200 code/place pages, WP merger pages,
Government merger regulation. Per-county post-fill primaries:
Harju 733, Hiiu 196, Ida-Viru 332, Järva 223, Jõgeva 226, Lääne 209,
Lääne-Viru 442, Pärnu 458, Põlva 204, Rapla 328, Saare 508, Tartu 471,
Valga 163, Viljandi 314, Võru 674. Holds/gaps: 9 pii-only codes held as
single-signal (15050/15172/41598/43299/50050/50096/80099: 1-6-address
facility-pattern codes whose sampled street numbers return bundled
codes; 66710/86217: village probes return bundled 66246/86216 or miss)
— re-probe if Omniva's JS postcode finder becomes reachable (homepage
app is Cloudflare-walled; only locations.json was usable); 26
Omniva postkontor/PO-box ZIPs (10195/11701/11801/11901/12701/13591/
13801/19098/20399/30301/41501/43101/48301/50191/50600/63301/68299/
71098/74002/79501/80501/88999/91999/92401/93091/93999) held as the
facility layer — ADS shows the same street addresses carrying delivery
codes (Fama->20303, Lai->80011, Reinu põik->71020, Keskallee->30322),
and pii 404s all sampled ones; the 54 bundled-not-in-pii codes stay
(36 GeoNames-backed incl. the 22-code Aespa 797/798 sub-cluster — pii
misses even Tallinn GeoNames codes, so its silence is not a death
signal — plus 18 city-base xx01 codes with town-set/Omniva backing);
full ADS enumeration (beyond gazetteer probes) would be needed to prove
no further delivery codes exist outside bundled+pii.

## Fiji

The bundled `FijiGeographyProvider` supplies the 4 divisions plus
Rotuma as `State` rows with the 14 provinces as level-2 areas (5
Central, 3 each Eastern/Northern/Western; Rotuma standalone) in a
two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('FJ')` after countries are seeded.

Fiji has no postcode system. Addresses print street lines, the
locality, and country; any supplied code prints on its own line.

Revisit 2026-10-03 (verify-only, zero changes; `gate_fj.py` ALL
PASS): 19/19 vs ISO 3166-2:FJ (divisions C/E/N/W, dependency
R, provinces 01-14 with division parents). FJ-08 stays
hyphenated "Nadroga-Navosa" (Provinces of Fiji 6x, ISO "and"
form 0x). No postcode system (UPU fji example + contact only;
no GeoNames FJ postal export).
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

### Revisit (B17, 2026-10-05)

Tree verified 292/292 mainland municipalities exact against the
fi.wiki kunnat table (official codes + Finnish names + region
parents; Åland's 16 excluded per the AX provider) with 107/107
city types exact against the cities category and 18/18 ISO
regions (02–19). Postal code-set equals the GeoNames FI dump
1:1 (3576/3576, zero multis) with legs equal to GN admin3 on
official codes (0/3576 mismatches) modulo the three verified
post-merger mappings (Pertunmaa 194xx→Mäntyharju,
Honkajoki 389xx→Kankaanpää, Valtimo 757xx→Nurmes; old codes
stay live under successor legs per Google addresses). One fix:
00002 hattula→helsinki (the GN row is internally inconsistent —
place Helsinki in admin Hattula; Posti's own address is
FI-00002 Helsinki). `gate_fi.py` ALL PASS.

## Greece

The bundled `GreeceGeographyProvider` supplies the 13
administrative regions plus Mount Athos (code `69`) as `State` rows
and a two-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GR')` after countries are seeded.
The 332 Kallikratis municipalities including the 2019 island splits ship as level-2 areas under their regions.

Greek addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `NNN NN`,
and country. Types are labelled `Periféreia` and `Dímos`.

### Revisit (B18, 2026-10-05)

Tree 346/346 PASS; postal files verified to 974 codes / 984
legs, all ELTA-exact (`gate_gr.py` ALL PASS). Three links moved
(ELTA register + live finder): 14121/14122 Metamorfosi →
Irakleio (Attica), 49083 North Corfu → Central Corfu and
Diapontia Islands.

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

Revisit 2026-10-05 (2 leg drops + 3 fills: 3045/3065/20 ->
3048/3066/18; `gate_hu.py` ALL PASS): tree 240/240 (43/43
ISO L1, 197/197 districts vs en.wiki + hu.wiki + citypop).
2943 Kisbér leg dropped (Tárkány is 2945/Kisbéri, Bábolna
2943-only; hu.wiki + OSM + WD); 9764 Sárvár leg dropped,
Szombathely primary (Meggyeskovácsi is 9757/Sárvári, kept).
Fills: 3244 Parádfürdő→Pétervására (GN + WD + hu.wiki),
3603 Sajóvárkony→Ózd (GN + WD, medium), 9719 Szentkirály→
Szombathely (WD + hu.wiki). 18 surviving multis each leg
WD-confirmed; 7016/8715 OSM-confirmed keeps; 2242/3071
GN-only held out (hu.wiki contradicts). Holds: 8139 Enying
single-source keep, 22 WD-only codes.

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

Revisited (2026-10-02): verify-only — counts 69 areas / 174 codes /
178 pairs confirmed against WP Sveitarfélög roster + Statoids +
GeoNames 2026-09-01. Pre-2024 consolidations correctly folded:
Húnabyggð (Húnavatnshreppur+Blönduósbær both absent),
Skagafjörður (Hofsós-era names absent), Múlaþing (pre-2020 names
absent). Garðabær (`is:municipality:garabr` — note non-obvious id
contraction), Kópavogur, and Grímsnes- og Grafningshreppur all still
separate live municipalities, matching bundled. Gate
`docs/agents/audit/gate_is.py` pins all 3 counts plus dual-parent
postcodes 276/641/701/851 and exclusion of 512/150/155/18 box codes.

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

Revisit 2026-10-03 (one fix: 40700 Mitrovica → Skenderaj;
`gate_xk.py` ALL PASS): 7 districts + 38 municipalities (5/4/6/7/3/8/5)
match the Districts-of-Kosovo table byte-for-byte, post-2013 set with
North Mitrovica. The 7 archived Posta e Kosovës regional PDFs
(UPU-approved) union to exactly the 127 bundled codes plus 10020,
which stays excluded as the non-geographic Transit Postal Centre.
Post-split offices stay mapped to current municipalities (10500
Gračanica, 20540 Mamusha, 51050 Junik, 61050 Klokot, 71510 Hani i
Elezit, 40650 Zubin Potok). 40700 Runikë moved because the office is
in Runik village, Skenderaj (addressed "Runik Skenderaj 40700"
sighting, en/sq wiki, OSM) — the PDF Mitrovica filing is a
postal-hierarchy artefact like 40650. 31030 Goraždevac stays Peja
(filed under PEJË); 60520 Zhegër is official-list-only and stays.
Parteš, Ranilug, and North Mitrovica stay codeless.

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

### Revisit (B20, 2026-10-06)

Tree verified clean (zero changes); postal 697/719 → 697/731
(`gate_lv.py` ALL PASS). L1 42/42 = post-2025-07-01 truth
(Varakļāni merged into Madona — ISO 3166-2:LV is the
laggard, still carrying LV-102); L2 585/585 names vs the
law-cited table. Legs had a systematic builder collapse:
6 L1 (Jelgava/Valmiera/Ogre/Jēkabpils municipalities,
Rēzekne/Ventspils state cities) held zero legs, each
city + municipality pair collapsed onto one side — 108
codes fixed (96 moves + 12 dual adds + 9 primary flips +
5015 leg-swap), convention rural → municipality,
town → city, edge splits dual with municipality primary
(proven by the correctly built pairs + 9/9 built duals).
Varakļāni codes 4835–4838 move Rēzekne → Madona per the
2025 merger law (supersedes the old overlay note). Holds:
19 in-range numbers absent both sources (retired), Pasts
finder unreachable (OSM + lvwiki + structural proof
instead), acc=1 rural moves retained (acc rates coords,
not the code↔place link).

## Liechtenstein

The bundled `LiechtensteinGeographyProvider` supplies the 11
communes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LI')` after countries are seeded.
Postal services follow Swiss rules.

Liechtenstein addresses are formatted per the UPU layout: street
lines, `{postcode} {locality}` with a 4-digit postcode, and country.
The tier is labelled `Gemeinde`.

Revisit 2026-10-03 (verify-only, zero changes; `gate_li.py` ALL
PASS): 11/11 ISO 3166-2:LI codes exact; 13-code overlay exact
vs the GeoNames LI dump incl. the Nendeln → Eschen and
Schaanwald → Mauren locality map (Eschen and Mauren dual-coded).
9489 stays held out (no GN row; swisstopo returns no zipcode hit
for 9489 while 9488 resolves to Schellenberg). UPU lie profile
defers to Switzerland (Swiss Post operates LI post).

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
Panevėžys cities use the same `miestas` convention. All three municipal
types share the `municipality` assignment role so role-filtered lookups
find every level-2 row.

Lithuanian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode, and country.
International mail prefixes `LT-`; the formatter prints the postcode
exactly as supplied. Types are labelled `Apskritis`,
`Rajono Savivaldybė`, `Miesto Savivaldybė`, and `Savivaldybė`.

Revisit 2026-10-04 (verify-only, zero changes; `gate_lt.py` ALL
PASS): the current GeoNames LT dump (21870 rows / 2023 distinct
codes) matches the bundled set exactly — zero diff both ways,
range 00001–99069, all bare 5-digit (`LT-NNNNN` internationally
per UPU/WP addressing). Per-code admin2 sets agree on all 2020
non-stray codes; primaries equal the GeoNames row-majority admin2
on all 2023 codes (incl. the 2:2 ties 44001/45009/47015, city
kept); counties agree on all 2020. The 3 dropped cross-county
singletons are proven GeoNames duplicate-row misfiles, each with
its true row present in the same dump: Padovinio k. under 96001
(true 69016), Pakeliškės k. under 96047 (true 69068), and a
generic Klaipėda-city row under 81001 (true 91001/94007) —
inside 35:1 / 50:1 / 148:1 same-routing majorities, and every
2-digit routing prefix maps to exactly one county, so the drops
stand. The 45 kept dual links are exactly the remaining
GeoNames multi-admin2 codes, all same-county. Tree: 10/10 ISO
3166-2:LT counties; 60/60 municipalities with codes 01–60, 60/60
parents, and the 43/10/7 district/plain/city split confirmed by
the WP municipalities table (which also confirms Marijampolė as
a plain municipality and Kazlų Rūda nominative — the two ISO-page
cells that disagree are that page's own errors, contradicted by
its link targets and GeoNames `Marijampolės sav.` / WP article
titles). Oracles: GeoNames LT.zip (current), ISO 3166-2:LT,
WP Municipalities of Lithuania, WP Postal codes in Lithuania
(format LT-NNNNN; live universe ~16,514), UPU LT-99999 entry.
Per-county primary counts: Alytus 51, Kaunas 102, Klaipėda 52,
Marijampolė 59, Panevėžys 63, Šiauliai 71, Tauragė 51, Telšiai
41, Utena 74, Vilnius 1459 (of which Vilniaus miestas 1349 —
GeoNames is street-level in Vilnius but settlement-level
elsewhere). Holds/gaps: bundled inherits GeoNames granularity
(~2023 of ~16,514 live LP codes; street-level fill outside
Vilnius needs a Lietuvos Paštas sweep — post.lt is bot-walled
and old.post.lt timed out, OSM Nominatim has no LT postcode
index, so no second street oracle was reachable); 16 kept duals
carry thin ×1 same-county minorities (mostly generic city rows)
that stay dual-linked per the stability rule.

## Luxembourg

The bundled `LuxembourgGeographyProvider` supplies the 12 cantons
as `State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('LU')` after
countries are seeded. Canton codes follow current ISO 3166-2:LU
The 100 communes ship as level-2 areas under their cantons.
(`GR` for Grevenmacher, `LU` for Luxembourg).

Luxembourg addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with an `L-NNNN` postcode, and country.

Revisit 2026-10-04 (fix-and-fill; `gate_lu.py` ALL PASS): tree
verify-only — 12/12 ISO 3166-2:LU cantons exact, 100/100 communes
exact against the CACLR COMMUALL current rows (names modulo the
`Luxembourg City` and `Redange-sur-Attert` display variants),
100/100 canton parents exact, zero pre-fusion ghosts, and Garnich
confirmed still a Capellen commune. Postal rebuilt against three
signals: the CACLR national address registry (ACT, 2026-09-28: TR
street extract, CODEPT 4430 = 4305 N + 125 B-type boîte/CEDEX,
IMMEUBLE buildings), the fresh GeoNames LU dump (4330 codes —
identical universe to the pre-fix bundle), and the 2018 Post
Luxembourg street file (4209 codes). Dropped 15: 10 GN-only phantoms
absent from both official vintages (3208, 3556, 4006, 4007, 4009,
4100, 7202, 8007, 8302, 9203), 4 retired street codes (3613 Quartier
Brill gone, 3923 Rue d'Esch recoded 3920–3922, 4262 Quai Neudorf
gone, 6721 Courtsgaessel gone), and 4008 (no street or building
history ever). Filled 18: the airport code L-1110 (Sandweiler
primary + Niederanven secondary), 10 more 2018-vintage street codes
missed by the build (1614, 1843, 1846, 2264, 4329, 5827, 7461,
7611, 7616, 9741 Boxhorn survivor), and 7 post-2018 codes (1507,
2618, 3942 new Mondercange quarter, 4090–4093 new Esch-Grenz
quarter) with dated CACLR records plus Nominatim street-exists.
Added 24 cross-commune secondary legs (TR street rows; seconded by
the 2018 file, Nominatim commune placement, or dated 2026
new-street records). Held out: 65 live B-type CEDEX codes (out of
bundle scope), 12 retired codes lingering N-type, 14 reserved
N-type codes with no street or building history (incl. pre-merger
LIBs 8300 SEPTFONTAINES, 8712 BOEVANGESURATTERT), dormant
provisional L-7300, and fully retired L-5845. New totals 4333
codes / 4435 links / 94 multi-leg (72 inherited duals verified
exact against the GeoNames cross-commune set, +21 new duals +
L-1110; 3 codes grown to 3–4 legs). The integrator corrected the
worker's multi-97 to the simulated 94 (three of the 24 legs land
on already-multi codes). The old "pre-2018 communes mapped to
merged names" claim is now verified cell-by-cell: 13 stale
GeoNames admin2 labels fold into the 8 current communes with zero
primary misses.

Open-case retry 2026-10-06 (blocked-log #8; `gate_lu.py` ALL
PASS): fresh BD-Adresses (ACT, 2026-10-05, 179,635 buildings)
+ Nominatim retired the dated legs — L-7533 Fischbach
secondary dropped (Place de l'Église 18/18 Mersch, Nominatim
Mersch-only; new totals 4333/4434/93), L-1634 Hesperange
secondary confirmed (Turbelfiels/Itzig, 1/31), L-2632
Luxembourg-city secondary confirmed (Rue de Trèves Findel
69/69 Sandweiler primary stands), L-7300 still 0-address
dormant (hold stands).

## Malta

The bundled `MaltaGeographyProvider` supplies the 68 local
councils as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MT')` after countries are seeded.

Maltese addresses are formatted per the UPU layout: street lines,
the locality, the `AAA NNNN` postcode on its own line, and country.

Revisit 2026-10-03 (fix-and-fill, 8-cell count-neutral swap; `gate_mt.py` ALL
PASS): MaltaPost finder re-swept exhaustively via the current
`/postcode/api/v1` endpoints (GetAllTowns 89 -> GetAllStreets 8656 rows /
8139 unique ids, 8046 with addresses, 93 verified-404-empty across 3 passes)
for a live universe of 27823 codes, then cross-checked cell-by-cell against
the bundled set plus Search point-lookups. Retired 4 (sweep-absent +
Search-404): GZR 1564, MXK 4084, RBT 4104, RBT 4105. Added 4 (sweep-present
+ Search exact-hit with street addresses): MXK 4081 (Marsaxlokk, Xrobb
l-Ghagin limits), RBT 4120/4121 (Bahrija, Triq Halq ic-Cawl), XBX 1096
(Ta' Xbiex, Triq Sir Augustus Bartolo). Churn pairs inside shared street
blocks (MXK 408x, RBT 41xx Bahrija) read as renumbering. Zero council moves
across 27819 shared codes; CBD 5060 is dual-locality live (Santa Venera +
Qormi) so the Santa Venera primary stands per the stability rule. Tree:
68/68 ISO 3166-2:MT codes exact (7 English-vs-Maltese exonym variants:
Cospicua/Bormla, Senglea/Isla, Victoria/Rabat Ghawdex, Rabat/Rabat Malta,
St. Julian's/San Giljan, St. Paul's Bay/San Pawl il-Bahar, Żebbuġ
Gozo/Ghawdex), LCA roster 68/68 entities, MaltaPost 89 towns = 68 seats +
21 sub-locality/CBD/Comino rows. GeoNames MT dump (73 prefix rows) agrees
on all 73 shared prefixes incl. KMN/SCM/MTP/XLN/MFN parent filings; UPU
MLT profile (01/2013) anchors the `AAA NNNN` format, the locality
abbreviation table (incl. MFN Marsalforn, VCT/RBT Rabat split, MTP 1001 HQ
contact), and the SLM 1000 example. Per-council code counts post-fix:
Amrun 460, Attard 629, Balzan 219, Birgu 218, Birkirkara 1187, Birżebbuġa
602, Cospicua 375, Dingli 265, Fgura 451, Floriana 165, Fontana 57,
Għajnsielem 247, Għarb 137, Għargħur 195, Għasri 61, Għaxaq 379, Gudja 200,
Gżira 252, Iklin 170, Kalkara 174, Kerċem 166, Kirkop 186, Lija 234, Luqa
359, Marsa 341, Marsaskala 640, Marsaxlokk 287, Mdina 59, Mellieħa 790,
Mġarr 291, Mosta 1170, Mqabba 258, Msida 391, Mtarfa 108, Munxar 128,
Nadur 390, Naxxar 1041, Paola 465, Pembroke 163, Pietà 164, Qala 215,
Qormi 979, Qrendi 305, Rabat 932, Safi 182, San Ġwann 718, San Lawrenz 53,
Sannat 177, Santa Luċija 143, Santa Venera 332, Senglea 212, Siġġiewi 612,
Sliema 678, St. Julian's 446, St. Paul's Bay 980, Swieqi 615, Ta' Xbiex
140, Tarxien 521, Valletta 312, Victoria 645, Xagħra 346, Xewkija 391,
Xgħajra 105, Żabbar 914, Żebbuġ Gozo 245, Żebbuġ Malta 802, Żejtun 953,
Żurrieq 796. Holds/gaps: street-code churn continues (finder vs bundled
will drift again); HMR 1428 (UPU illustration) exists in neither dataset;
CBD 5060 dual filing kept single-primary; no second street-level oracle
exists (GeoNames/UPU are prefix/format-level), so future street churn
needs the same two-method finder agreement.

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

### Revisit (B20, 2026-10-06)

Tree PASS, no changes; postal 1214/1220 → 1215/1220
(`gate_md.py` ALL PASS). L1 37/37 codes + types vs ISO
3166-2:MD; L2 981/981 names + types vs the Law 764-XV
annex transcription (all 37 parents). Root cause of the
postal fixes: legs copied GeoNames admin1 1:1, inheriting
three GN misfilings — 15 moves + 1 delete + 1 add, all
de-jure Law 764, each 2+ signals (GN place + Law 764 tree
+ OSM): Basarabeasca unfolded from Cimișlia (MD-6701 +
MD-6711–6716; MD-6717 Troițcoe correctly stays),
right-bank Bender-zone places out of Transnistria
(MD-3200/3252 → Bender, MD-3251 → Anenii Noi,
MD-4316/4317/3351/5714 → Căușeni), Roghi MD-4523 →
Dubăsari; MD-5222 Rîșcani leg deleted (Stepanovca absent
from Law 764, a Drochia quarter); MD-5219 Lazo ADDED (GN
row + infobiz live use; the old "district conflict"
omission is superseded). Holds: 5 dual primaries (7333 a
1v1 tie), de-jure-over-de-facto policy, 2025
amalgamations not absorbed, Poșta SPA + legis.md blocked.
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
Per the UPU Monaco sheet, 98000 covers all physical delivery and
98001+ are institutional/CEDEX codes rather than quarter codes (98050
follows the Office des Timbres across two street addresses), so the
postal CSVs list only 98000 with no area links.

Revisit 2026-10-03 (verify-only, zero changes; `gate_mc.py` ALL
PASS): 17/17 ISO 3166-2:MC quarters exact (2013-ordinance
wards stay out). Sole code 98000 vs the UPU mco profile (00 =
delivery to addressee; 01-99 special delivery types incl.
CEDEX, held out) + GeoNames MC dump (29 rows, all 98000);
zero links.
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

Revisit 2026-10-04 (fix-and-fill: 1 retarget, counts unchanged
326/326; `gate_mk.py` ALL PASS): tree verified exact — all 80
post-2013 municipalities match the WP roster name-for-name and all
80 codes match the ISO 3166-2:MK current series (MK-101..MK-817).
Two WP spellings kept over ISO romanization per stability
(Debarca=ISO Debrca 304, Mavrovo and Rostusa=ISO Mavrovo i
Rostuse 607). The 2013 mergers check out: Kicevo (307) present,
the absorbed Drugovo/Zajas/Oslomej/Vranestica absent; Greater
Skopje is 10 flat L1 municipalities with no umbrella Skopje area.
Codes verified against the 2016 official Makedonska Posta
delivery-post list (dostavni_posti.pdf via Wayback 2016-08-03):
327 official codes, repo carries 326 = official minus 1137
(Skopje 37, Krste Misirkov bb with no naselba — the boulevard
straddles Cair/Centar, cf. 1132 Bitpazar -> Cair vs 1103 court ->
Centar — so the "uncertain commune" exclusion stands). All 326
links re-derived independently: 209 GeoNames-coded offices via OSM
boundary containment of GN coords (200 direct ISO matches; 9 GN
coord errors adjudicated for the repo — 1010/1020/1040 centroids,
1054 Rakotinci vs Rakitnica, 1235 Negotino-Polosko vs Negotino
town, 2434 off-coords, 2436 Drazevo vs Dracevo-Skopje, 6260
off-coords, 6306 Leskoec-Ohridski vs Leskoec/Resen, each confirmed
by the official unit name + en-wiki village municipality) and the
117 non-GN offices via unit street/naselba/village geocodes + the
2018 official units overview (Pregled 2018: 1113 "15 Korpus, Gazi
Baba", 1140 "nas. 11 Oktomvri, Kisela Voda" explicit). THE FIX:
1128 (airport post office) petrovec -> ilinden — the terminal +
post POI sit in Mralino/Ilinden per OSM boundaries, history.mk
("since the 1996 boundary redefinition the airport is in Ilinden
municipality"), and the terminal counter address (Mralino,
Ilinden); "Petrovec" is the airport's conventional name (nearest
village, mk-wiki) and postal routing (1043), not the office
commune. Holds (not gaps): 7515 Novo Lagovo (mk-wiki/wikidata
only; absent from 2005/2016/2018 official lists + GN — likely
post-2018 opening); 21 post-2016 openings from the 2018 list
(1012/1013/1014/1065/1205/1208/1245/1246/1329/1336/1340/1404/1412/
1432/1486/2103/2311/2334/2405/2406/2422) + 1127/1135 (Skopje
branches absent in 2016, back in 2018) held for a vintage refresh;
19 pre-2016 retirements correctly excluded (11 GN-stale:
1434/6245/6256/6259/7213/7214/7224/7242/7316/7506/7508; 1046
Cresovo marked closed in the 2005 list itself, 1124 Skopje 24,
1253 Lazaropole seasonal, 1490 Bogorodica superseded by 1482
crossing, 6103 Ohrid 3). UPU MKD 07/2019 anchors hold: 1020
Skopje -> Karpos, 1310 Kumanovo, 2314 Blatec -> Vinica. All files
LF; provider stateDefinitions identical to the areas CSV.

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

### Revisit (B19, 2026-10-05)

Tree verify-only: 15 post-2024 counties + 2 arctic regions exact
vs SSB Klass-104, 357 municipalities exact vs SSB Klass-131
(9999 Uoppgitt correctly excluded), 2024 splits per Bring
(`gate_no.py` ALL PASS). ISO 3166-2:NO is stale (11 counties,
2020–24 scheme) and loses to operator+catalogue; 30 Sami/
qualifier name diffs deliberately kept per FI/SE short-name
precedent. Postal files to 5110 codes / 5110 1:1 links: 14
fills (8 logg-nye 2024/25/26: 1426/4075/4238/5245/7061/8845/
8866/9653; 6 pre-1999 gaps in both Bring vintages:
0040/0540 Oslo, 9173–9176 Svalbard) + 40 drops (39 dated
opphør→9999 waves 2022/24/25/26 + 8128→8120 redirect).
Reconciliation 5136+14−40=5110, +2 held S-codes = 5112 =
operator current, exact. Holds: 0046/0047 S out of scope,
0018/0045 S kept (no churn); drops rest on two Bring artifacts
(dated opphør logg judged sufficient, GN proven stale).

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

Revisit (B13): Mapanet full scrape (86/86 n3 pages, 219 rows) reproduced
the bundled 62-code set exactly, so the build is a faithful Mapanet
transcription, but Mapanet itself is incomplete against the archived
Post PNG office list (39 entries). Two independent signals each
confirmed five fills: 135 Gordons→North-East (PNGEC 2022 polling
schedule, Gordons booths under the NORTH-EAST header), 332
Tabubil→North Fly (LLG table), 512 DWU→Madang (seat town),
613 Kokopo→Kokopo (seat), 635 Lihir→Namatanai (island district).
Held out: 541 Lorengau (Post PNG says 541 but Mapanet assigns Manus
641 and three addressed business usages print `Manus Province 641`,
so the addressed usage wins) and 417 Gusap (office confirmed, but
no LLG, article, or second source pins Markham vs Nawae district).
293 keeps its Wapenamanda primary: Tsak LLG→Wapenamanda and Wage
LLG→Kandep split the Mapanet cell 2v2 and the populations are tied
within noise (~71.8k vs ~73k), so there is no positive evidence to
overturn the standing call. Tree fix: `Bulolo_District` renamed to
`Bulolo` (slug `pg:district:bulolo`) per the canonical WP district
title. Post-pass: 118 areas, 67 codes, 76 links.

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

### Revisit (B18, 2026-10-05)

Tree oracle-exact (DGT-scheme codes + pt/en wiki + mirror tags);
postal files verified to 197,772 codes / 197,772 1:1 links
(`gate_pt.py` ALL PASS): format `NNNN-NNN` pure, 750 prefixes
1000–9980, stratified 25/25 spot-checks vs the CTT-structured
mirror (Lisboa/Porto urban, Beja/north rural, Madeira, Azores
incl. Corvo 9980). Two fixes: district + municipality `Lisbon`
→ `Lisboa` (source_id unchanged), and postal pair CRLF → LF
(was 100% CRLF vs LF areas; KN precedent; content + order
identical). Holds: CTT finder API 403, DGT/INE hosts
unreachable, ISO OBP 403 (all mitigated by mirror + oracle
agreement).

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

### Revisit (B21, 2026-10-06)

Tree 17 renames, postal ZERO changes
(`gate_ro.py` ALL PASS). L1 42/42 ISO
codes; L2 3180/3180 UATs vs SIRUTA-2022,
0 type/parent/county errors; comma-below
diacritics clean. Renames triangulated
(SIRUTA + GN-PPLA2/village + ro.wiki;
ADM2==SIRUTA never double-counted):
Daia Română, Dârmănești, Sânmărtin,
Gălăuțaș, Piatra-Neamț, Piatra-Olt,
Boldești-Gradiștea, Șărmășag, C.A.
Rosetti, I.C. Brătianu, Smârdan, Șelaru
(drop wiki-disambiguation suffix),
Alexandru I. Cuza, Pădina (not seat
village), Roșiori de Vede, Beidaud,
Mihail Kogălniceanu; 15 vindications
incl. Petreu Dec-2022 rename. Bucharest
exonym HELD (L1 State-row migration
needs states.json + prune — maintainer
call). Postal: bundle == GN export
exactly (same lineage, not proof);
mirror partial (Covasna 186/186 + Ilfov
105/105 set-equal); integrator H3
prefix-join 37914/37914 clean; H4
330-sample + Arad/Călărași/Alba
shortfalls held.

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

Revisit 2026-10-04 (fix: KN0111 primary flipped St Peter→Cayon +
areas EOL normalized; `gate_kn.py` ALL PASS): 14 parishes
ISO-exact (01–13 + 15, 14 skipped by ISO; islands K/N) and all 92
villages diffed against the 14 parish articles. Naming holds (PDF
+ CSV over article-list variants): Sir Gillee's, St Paul's
(PDF "St Paul's Station Street"), Spooners, Parsons, Barnaby
(PDF over article-prose "Burnaby"). Disputed filings confirmed:
Lodge→Christ Church (both neighbours claim it; Christ Church
lists Lodge Village + PDF KN0601), New Road→St Peter (St Peter
lists it), Keys→Cayon (Cayon lists it, unopposed). Postal: post.kn
zones PDF code set 32/32 exact (no 07 zone in the source either);
all 7 duals justified place-by-place (0108 Basseterre boundary,
0111 Keys/Canada straddle, 0202 Old Road East, 0403 Newton
Ground, 0501 Mansion/Christ Church, 0802 Bath, 1201 Craddocks
straddle). KN0111 flipped on majority-holds-primary (Keys
cluster 5 Cayon mentions + village anchor vs Canada Estate 1).
UPU knaEn (12/2017): KN + 4 digits, zone + district.
## San Marino

The bundled `SanMarinoGeographyProvider` supplies the 9
municipalities (officially castelli) as `State` rows and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('SM')` after countries are seeded.

Sammarinese addresses are formatted per the UPU layout (Italian CAP
system): street lines, `{postcode} {locality}` with a `47890–47899`
postcode, and country.

Revisit 2026-10-03 (verify-only, zero changes; `gate_sm.py` ALL
PASS): 9/9 ISO 3166-2:SM codes; UPU smrEn full locality list
confirms all 10 code→castello links (Serravalle holds 47891 +
47899 via Dogana/Falciano/Rovereta/Galazzano/Fiorina).
## Serbia

The bundled `SerbiaGeographyProvider` supplies the 29 districts,
2 provinces, and Belgrade as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('RS')` after countries are seeded.
The 117 municipalities, 27 cities and 17 Belgrade city-municipalities ship as level-2 areas under their districts.
`city` spans both levels (Romania pattern): Belgrade keeps the
`city` role while county cities share the `municipality`
assignment role.

Serbian addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit delivery-office number, and
country. The street-level 6-digit PAK has no field and is not printed.

Revisit 2026-10-05 (fix-and-fill: +4 cities, 45 district→city
moves, 8 primary flips, 1 secondary added, 2 L1-generic moves, 7
fills — 189→193 areas / 1334→1341 codes / 1407→1415 links;
`gate_rs.py` ALL PASS): tree verified — 117/117 municipalities
(names + parents exact vs the law-ordered WP list), 23/23 cities
+ Niš/Vranje/Požarevac/Užice added at wiki-table positions
14/3/19/26, Belgrade 17/17, L1 32 (29 districts + Belgrade +
KM/VO; Kosovo empties deliberate, XK overlaid). GN RS.txt
(1149 codes) is a strict subset of the bundle; Nominatim
corroborated 66/185 bundle-only codes. THE MOVES: all 45
district-crutch legs to the 4 new cities, each ≥2 signals (GN
settlement + sr.wiki membership + Pošta PAK delivery rows);
31311 Bela Zemlja → Užice per operator Drijetanj/Ljubanje rows
over the GN algorithmic Čajetina hierarchy. Flips: 11118
Vračar, 11120/11160 Zvezdara, 11158 Stari Grad (operator
streets + OSM); 15226 Koceljeva, 37202 Kruševac, 37233
Aleksandrovac, 18411 Doljevac (settlement membership + GN web
/ operator). 11102 gains a Savski Venac secondary (operator ×2
+ OSM); 11150 → Novi Beograd, 11167 → Vračar off L1-generic.
Fills: Niš 18101/18103/18104/18105, Užice 31109, Voždovac
11042, Novi Beograd 11197 (operator + OSM). Singles sample
24/24. Holds: 5 district legs (17508 Sveti Ilija unresolved,
18110 no signals, 18251/18252/18411 span claims), 11040/11050
primaries (OSM disagrees, operator silent), L1-generic branch
codes incl. 11165/11189 (single-signal), 11000 as-is. OSM
Belgrade points are unreliable (11231 misplaced at Beli
Potok — operator confirms Rakovica; 11102 half-right) —
operator wins every conflict.

Retry 2026-10-06 (1415→1411 legs, 69→67 multis; `gate_rs.py`
0 FAILURES): 11040 → Savski Venac primary + Voždovac secondary
(PAK 12:2 streets + PlanPlus Beograd-33 office row; Rakovica
drops); 11050 → Zvezdara primary + Voždovac secondary
(delivery-office rule: Beograd-22 office at Ustanička 182;
Vračar drops); 17508 → Vranje primary + Trgovište secondary
(Sveti Ilija solved as a Barelić hamlet post office; district
drops); 18110 → Niš (PAK Nikola Tesla + addressed journal
article); 18251 → Niš primary + Nišava span marker (office
town Mramor); 18252 Merošina-only and 18411 Doljevac-only
(district span legs drop). L1 branch tail: 91/92 codes to
city-municipalities (office street → PAK opština × PlanPlus
× OSM per code); only 11161 stays generic (zero signals
anywhere). Holds: 11161; WPC-only slices (18251-Merošina,
17508-Trgovište); 18252 single-oracle caveat; 11113/11140
borderline rows.
## Slovakia

The bundled `SlovakiaGeographyProvider` supplies the 8 regions as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SK')` after
countries are seeded.
The 79 districts ship as level-2 areas under their regions.

Slovak addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country. Types are labelled `Kraj` and `Okres`.

Revisit 2026-10-04 (fix-and-fill: 114 region→district cell moves,
zero count changes — 87 areas / 3480 codes / 3514 links;
`gate_sk.py` ALL PASS post-state, pre-fix FAILs on exactly the 114
move checks + 3 aggregates): tree verified 8 regions (ISO codes
8/8) + 79 districts (names + parents 79/79 vs WP, per-region
13/8/11/7/13/9/7/11). Code set GN-identical (3480 distinct,
010 01–992 14 both sides; 860 01–899 99 internal range correctly
absent). Every multi-link set equals the GN per-code admin2 set
and every primary equals the GN row-majority admin2; 12 exact ties
keep bundled primaries per stability. THE FIX: pre-state put ALL
329 KI blank-only office codes at region while all 1740 non-KI
blank-only codes were town→district resolved — 114 non-Košice-city
codes resolve unanimously under the same rule (Trebišov town ×60
→ trebišov, Kráľovský Chlmec ×22 → trebišov, Spišská Nová Ves ×13,
Michalovce ×10, Rožňava ×5, Moldava nad Bodvou ×2 →
košice-okolie, Sobrance ×1, 044 54 železiarne → košice-ii per GN
place + Šaca steelworks article), each ≥2 signals. Post-state:
215 region primaries (all true "Košice N" city office codes),
79/79 districts covered. Holds: 215 Košice-city codes stay at
region (no digit rule — 040 01 spans I+IV etc.); posta.sk finder
dead (2006 URLs 404, current site JS-driven); UPU SVK sheet via
mirror (live link redirects home). Oracles: GN SK postal dump
(5233 rows) + full dump, ISO 3166-2:SK, WP districts + town
articles, UPU profile (010 01 / 960 01 / 058 06 / 917 01 anchors
match), orsr.sk Bratislava pins re-confirmed.

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

Revisit 2026-10-05 (verify-only, zero data changes;
`gate_si.py` ALL PASS): tree 212/212 (names + ISO codes +
types exact vs ISO 3166-2:SI). Postal: 467/468 codes exist
in GN SI.txt (9246 Razkrižje OSM-confirmed, GN's only
gap); all 89 GN extras are PO-box / large-user / internal
(9 explicit predali + city x5xx/x600 blocks + covered-town
1371/4501/9502), correctly excluded; single multi 3231
Grobelno dual Šentjur-primary confirmed by sl.wiki (both
settlements listed); 30-code OSM attribution sample 30/30.
Hold: 6323 Strunjan seasonal post (single GN signal).

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

Revisit 2026-10-05 (verify-only, zero data changes;
`gate_sb.py` ALL PASS): L1 10/10 (9 provinces + Honiara CT,
ISO codes CE/CH/GU/CT/IS/MK/ML/RB/TE/WE vs WP + Statoids).
All 183 ward names + parents byte-identical to the OCHA
COD-AB slb_admbnda_adm3 SINSO census geography (DBF-parsed);
per-province counts match citypopulation (13/14/22+12/16/20/
33/10/17/26; citypop groups Honiara's 12 wards under
Guadalcanal presentationally, COD-AB ADM1 SB10 confirms the
Honiara parent). Holds: ~20 citypop/Statoids spelling
variants (Banika, Tepazaka, Baolo, Fataleka, Gaongau,
Tenggano/Tenggno, Kanava/Kanara, Gangoto/Gantogo,
Mbuini/Mbuin, Santa Anna, Wagina, separator styles) —
official SINSO spellings kept; directories disagree with
official AND with each other. The West Baegu NBSP +
'Fatale' string is verbatim SINSO (SB0707190705). 2019
census Vol 2 ward list unlocated (Vol 1 only; Pacific Data
Hub Cloudflare-walled); ward `code` pcodes mix SINSO
vintages (pre-existing).
## Sweden

The bundled `SwedenGeographyProvider` supplies the 21 counties as
`State` rows and a two-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('SE')` after
countries are seeded.
The 290 municipalities ship as level-2 areas under their counties.

Swedish addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 5-digit postcode written `XXX XX`,
and country. An `SE-` prefix passes through when supplied.

Revisit 2026-10-05 (B17 worker: 62 postal leg moves + 1
name-only rename; `gate_se.py` ALL PASS): tree 311/311 — 21
counties ISO SE-A..Z exact, 290 municipalities 290/290 codes
vs SCB Statistikdatabasen + sv/en.wiki (Heby correctly under
Uppsala since 2007; Knivsta 2003 newest). Rename: Gothenburg
-> Göteborg (name cell only, slug stable; SCB official +
sv.wiki + 289/290 endonym convention). Moves (sv.wiki tätort
+ Bring postort-valid and/or Nominatim kommun, GN places
corroborate): Kisa/Rimforsa/Horn 59036-46 -> Kinda (Kinda
0->10 codes), Storvreta 743xx x12 -> Uppsala, Höör 243xx
x20 -> Höör (6->26), Vintrosa 719xx x10 -> Örebro, Hållnäs
81963-65 -> Tierp (GN Hällnäs rows are GN errors), Rockneby/
Läckeby 38030/31 -> Kalmar, Stugun 83076 -> Ragunda, Ydre
57374-77 -> Ydre (1->5); no municipality left at zero.
Postal set == GN set exactly (18887/18887, 0 multis, 0
removals — Bring-invalid and OSM-NOHIT both proven lossy).
Keeps: genuine straddles (74197 Almunge, Mariannelund,
Dikanäs, Slagnäs, Kvicksund, 27035, 29062), GN place-label
traps (34341/73119/73345/58150), Billdal->Gothenburg slug.
Holds: ~3815 box/storföretag/svarspost/tävlingspost codes
need a contract decision (digit rules + 10x/20x/40x series);
3-segment habo slug; empty native/geo fields. Skipped: F-L
CRLF->LF (CRLF is the established postal norm for several
bundles repo-wide; EOL preserved byte-wise).
## Switzerland

The bundled `SwitzerlandGeographyProvider` supplies the 26
cantons as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('CH')` after countries are seeded.
The 146 districts, regions and constituencies ship as level-2 areas under their cantons.

Swiss addresses are formatted per the UPU layout: street lines,
`{postcode} {locality}` with a 4-digit postcode (office numbers and
canton abbreviations pass through), and country.

Revisit 2026-10-05 (B15 fix pass, 10 ops; `gate_ch.py` 0
FAILURES): 26/26 cantons vs ISO 3166-2:CH, 146 L2 vs the BFS
commune register 2026-01-01 (kept design deviations: Luzern
Stadt+Land merged, Raron Westlich/Östlich split, AI 5 districts,
NE 6 pre-2018 districts). Renames: Jura-North Vaudois→Jura-Nord
vaudois, district Zurich→Zürich. Code set 3177 = GeoNames ∩
swisstopo exactly (185 PO-box/firm/city-base exclusions + 13
Liechtenstein 9485–9498 vindicated; 9000 St. Gallen is
geographic and included). Leg moves: 2740 Moutier-primary
(Moutier→Jura 1 Jan 2026, Roches sliver keeps Jura bernois
secondary), 1595 Bern-Mittelland→See/Lac (Clavaleyres→Murten
2022), 1015 Lausanne→Ouest lausannois (EPFL/UNIL campus, zero
Lausanne-commune rows); drops: 1911 Conthey
(Mayens-de-Chamoson is 1955), 6825 Lugano (pre-2022 Rovio dupe),
2333 La Chaux-de-Fonds (La Cibourg is a Renan/BE hamlet —
Renan commune tourism page + La Ferrière commune heritage article,
2026-10-06, triple-signal);
primary flips: 3994 Östlich Raron→Goms (Lax 312 vs Martisberg
19), 1958 Sion→Sierre (St-Léonard 2,460 vs Uvrier ~1,400).
3220 legs, 42 multis. Held: ~324 swisstopo-only micro-slivers
(zero GeoNames corroboration — legs require it), canton
exonyms, and the EN-Wikipedia "Moutier District" form.
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

Revisit 2026-10-03 (verify-only, zero changes; `gate_ax.py` ALL
PASS): 16/16 municipalities vs Municipalities of Aland +
GeoNames AX admin2 (AX- prefix per the UPU ala profile).
33-code street overlay exact vs the GeoNames AX dump + the
Aland Post postcode directory (22110/22120/22140 read MARIEHAMN
but sit in Jomala). Held out: PO Box twins 22101/22111/22411
(AP postboxar labels; UPU fin ends-in-1 rule), GN-only 22151,
PostNord-only 22271 — all end in 1, box-type per the rule.
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

Revisit 2026-10-03 (verify-only, zero changes; `gate_gg.py` ALL
PASS): 10 parishes + Alderney + Sark exact vs Statoids (no ISO
3166-2:GG codes exist); no-rename hold on the St-spellings (WP
prose + UPU example use "St", "Saint" only in article titles;
matches the JE/GG in-repo island convention). GY1–GY10 exact vs
the GY postcode-area table + GeoNames GG dump, both of which
corroborate both sides of the GY6/7/8 duals; Herm GY1 3HR +
Jethou GY1 4AB stay in St Peter Port, Lihou/Brecqhou unlisted.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_im.py` ALL
PASS): 6 sheadings + 21 L2 exact vs Local government in the
Isle of Man (types/parents incl. the Garff + Arbory-and-Rushen
mergers; no ISO 3166-2:IM codes). Overlay 9/27 exact vs the WP
IM postcode-area coverage + GeoNames IM dump + Photon hamlet
checks (IM4 Marown via Braaid/Crosby; IM7 Ballasalla GN row is
centroid noise — Ballasalla is IM9/Malew; Stuggadhoo kept as
built on a single weak GN row). IM86/87/99 box/large-user held
out (UPU imn example uses IM99).

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_as.py` ALL
PASS): 3 districts + Rose/Swains atolls + 15 counties exact vs
Statoids (no ISO 3166-2:AS codes; atolls = Statoids' single
"Unorganized" row; Fofo corroborated by its WP article —
Statoids predates the split). Sole code 96799 vs the UPU asm
profile + GeoNames AS dump; zero links.

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
(`98600 MATA-UTU`); Futuna splits Alo 98610 / Sigave 98620.

Revisit 2026-10-03 (verify-only, zero changes; `gate_wf.py` ALL
PASS): 3/3 ISO 3166-2:WF kingdoms + 3 Uvea districts; La Poste
Hexasmal confirms 98600 UVEA / 98610 ALO / 98620 SIGAVE
(UPU wlfEn shows 98600 only).

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_mh.py` ALL
PASS): 2 chains + 24 municipalities exact vs ISO 3166-2:MH
(L/T + trigram codes). Overlay 2/2: 96960 Majuro + 96970
Ebeye (Kwajalein) vs the UPU mhl range + GeoNames MH
localities (GN "Ailinginae" admin2 is centroid noise); outer
atolls route via the hubs.

## Guam

The bundled `GuamGeographyProvider` supplies the 19 villages as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('GU')` after
countries are seeded. Villages are municipalities governed by
elected mayors; there is no administrative tier below them (the
North/Central/South regions are statistical groupings only).

Guamanian addresses use the US ZIP layout
(`{locality} GU {ZIP}`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_gu.py` ALL
PASS): 19/19 villages vs the Statoids set + Villages of Guam
(current Chamorro forms with old forms parenthesized). Overlay
21/21 exact vs the GeoNames GU dump (USPS city mapping); UPU
gum range 96910-96931 is stale (misses 96932, corroborated by
GN + mirrors). Holds: 96920/96924 unassigned (absent from GN +
all mirrors); Chalan Pago-Ordot codeless (no post office —
96910 spill is ZCTA-style overlap, not USPS city assignment).

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

### Revisit (B19, 2026-10-05)

L1 22/22 ISO, L2 membership 340/340, parents clean, 548/548
L1 links correct (`gate_gt.py` ALL PASS). Three renames:
Antigua → Antigua Guatemala (Correos operator + IDH-INE +
muni self-name + annex + GN), San Bartolo → San Bartolo Aguas
Calientes (muni PDF + MINFIN + IDH; Correos short discounted
as proven shorthand), Quetzaltepeque → Quezaltepeque + slug
(IDH-INE + Correos + GN; zero leg cascade, zero external
refs). Fill 01025 Zona 25 (directory + legal notice +
manifests + listings). Postal files to 549/549. Holds: H1–H4
article case, H5/H6 accents, H7 01000 single-signal, H8 Petén
PDF 404 (verified via GN + directory 29/29), H9 walled
officials; 05008/01020 gaps real.

## Nauru

The bundled `NauruGeographyProvider` supplies the 14 districts as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NR')` after
countries are seeded. The 169 villages are historical (1908
expedition source, merged into a single coastal settlement, no
current admin function) and are intentionally not bundled.

Nauru has a sole national postcode, NRU68, printed on its own
line below the district.

Revisit 2026-10-03 (verify-only, zero changes; `gate_nr.py` ALL
PASS): 14/14 ISO 3166-2:NR codes exact; NR-05 stays "Baiti"
(Statoids + the ISO-noted local variant; "Baitsi" held out —
the UPU district list is OCR-corrupted with Anabare/Denig, so
Baitsi has no solid second signal). Sole code NRU68 vs the UPU
nru profile + GeoNames NR dump, zero links (district-only
addressing).

## Niue

The bundled `NiueGeographyProvider` supplies the 14 villages as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('NU')` after
countries are seeded. Villages double as municipalities and
electoral districts; there is no tier below them.

Niue has a sole island code, 9974, printed right of the
locality.

Revisit 2026-10-03 (verify-only, zero changes; `gate_nu.py` ALL
PASS): 14/14 villages exact vs Statoids Villages of Niue (no
ISO 3166-2:NU codes; 01-14 synthetic). Sole code 9974 vs the
UPU niu profile (single postcode for the whole territory) +
GeoNames NU dump; zero links.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_fm.py` ALL
PASS): 4/4 ISO 3166-2:FM codes (TRK KSA PNI YAP); 75/75
municipalities with exact per-state counts (40/4/11/20) and
names vs the en-wp admin-divisions table (incl. the duplicate
Piherarh row shipped once, as documented). Prior holds
re-confirmed: Tol stays municipality (WP-bold "city" still a
single unexplained signal; Tol island article claims no
cityhood; Statoids-2001 lists Tol as a plain municipality —
though that snapshot is Trust-Territory-era throughout:
Dublon= Tonoas, Moen=Weno, Fala-Beguets=Fanapanges,
Fefan=Fefen, Lukunor=Lukunoch, Magur=Makur, Map=Maap,
Mokil=Mwoakilloa, Nama=Nema, Ono=Onou, Onari=Unanu,
Param=Parem, Pisaras=Piherarh, Pulap/Pulusuk/Puluwat=
Pollap/Houk/Polowat, Romanum=Ramanum, Uh=U, Ngatik=
Sapwuahfik, plus uninhabited Gaferut/Sorol/Oroluk and
absorbed Ulul/Walung/Pis-Losap — current names follow WP).
Utwe spelling kept: dedicated article "Utwe (or Utwa)" +
Statoids "Utwe FM.KO.UT" (Utwa stays the alternative name).
ZIPs 4/4 exact vs GeoNames FM.zip (96941 Pohnpei, 96942
Chuuk, 96943 Yap, 96944 Kosrae), all state primaries.

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

Tuvalu has no bundled postcode file; the formatter prints any supplied
code on its own line.

Revisit 2026-10-03 (verify-only on the tree + Nanumaga alias, zero
CSV changes; `gate_tv.py` ALL PASS): 8/8 ISO 3166-2:TV codes;
Niulakita has no code (administered with Niutao, no ninth row).
Nanumanga kept (GeoNames + en-wiki article agree); Nanumaga (the
ISO/UPU official form) ships as an areaNames alias, QA pattern.
Gap (not verdict none): UPU tuvEn (08/2023) specifies a live
TUV+3-digit system (TUV150 Vaiaku, TUV120 Fakaifou, TUV710
Lolua), but no complete public directory exists to bundle.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_pw.py` ALL
PASS): 16/16 ISO 3166-2:PW states exact. Overlay 2/16: UPU plw
covers two codes (US system); 96939 is Ngerulmud-only (capital
settlement in Melekeok), 96940 the rest with Koror primary
(USPS bulletin build source). GeoNames PW carries only 96940.

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

### Revisit (B18, 2026-10-05)

Itumalo membership verified as governing parentage over SBS
constituency geography (`gate_ws.py` ALL PASS): 5 exclave
villages re-parented (Satuimalufilufi → A'ana, Faleapuna →
Va'a-o-Fonoti, Salamumu Tai/Uta + Le'auva'a → Gaga'emauga) and
2 legs moved to district level (WS1434 → Atua, WS2491 →
Vaisigano). Postal files hold at 223 codes / 240 links; the 17
extra legs are legitimate multi-village postcodes per the
SamoaPost table. Holds H1–H7 (Tafua parent, WS2374 Lolua,
WS2375/WS1424 Siufaga splits, and 4 minor) kept as-is for lack
of a second signal.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_ky.py` ALL
PASS): 3 islands + 7 districts per the district table (5 Grand
Cayman + 2 self-parented; no ISO 3166-2:KY codes); UPU cymEn
confirms the box-only system (street address alone
undeliverable) — deliberate no-import, codes pass through.

## Anguilla

The bundled `AnguillaGeographyProvider` supplies the 14
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AI')` after countries are
seeded. Districts are terminal; there is no tier below them.

Anguillan postcodes print on their own line below the locality
(`The Valley`, `AI-2640`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_ai.py` ALL
PASS): 14/14 district names exact vs Statoids (no ISO 3166-2:AI
codes exist). Sole code AI-2640 vs the GeoNames AI dump + The
Anguillian 2007 introduction report (via WP citation), zero
links; UPU has no AI profile (live URL serves the site shell,
no archive).

## Antigua and Barbuda

The bundled `AntiguaAndBarbudaGeographyProvider` supplies the 6
parishes and 2 dependencies (Barbuda and uninhabited Redonda)
as `State` rows and a single-level administrative hierarchy. It
is selected with `SeedCountryGeographiesAction::execute('AG')`
after countries are seeded. Parishes and dependencies are
terminal.

Antigua and Barbuda has no postcode system.

Revisit 2026-10-03 (verify-only, zero changes; `gate_ag.py` ALL
PASS): 8/8 ISO 3166-2:AG codes exact; UPU atgEn is a contact
block only.

## Aruba

The bundled `ArubaGeographyProvider` supplies the 8 regions
and the capital Oranjestad as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('AW')` after countries are
seeded. Regions are statistical and terminal.

Aruba has no postcode system.

Revisit 2026-10-03 (verify-only, zero changes; `gate_aw.py` ALL
PASS): 8 CBS census regions (citypopulation cross-check; English
East/Nicolaas forms) + the intentional capital Oranjestad
addressing row; no ISO 3166-2:AW codes; UPU abwEn carries no
postcode section.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_bb.py` ALL
PASS): tree verified — 11/11 ISO 3166-2:BB codes exact (BB-01
Christ Church first, then Saints alphabetical through BB-11 Saint
Thomas). Live BPS finder JS re-pull (2,866 district rows):
1,161/1,161 BB+5 codes set-identical with zero link-set mismatches
after normalizing finder sub-labels (St. Michael 1/2/3 = blocks
11/12/14, Christ Church 1/2 = blocks 17/15); every block is
single-parish except BB23. The 15 finder-absent x00 office bases
reconcile exactly (BB26000 Speightstown is in the finder itself as
"St. Peter Post Office") and all 16 match the 18-district-office
table parishes (BB11000 GPO + Cruise Terminal, BB24000 Holetown +
West Terrace; BB13000 Welches St. Michael, BB16000 Airport/Seawell
Christ Church). The 9 BB23 duals carry finder-majority primaries
(8 Saint James incl. the BB23027 1v1 tie broken by block context —
BB23 is a St James series, 24 of 35 primaries — and BB23037 Saint
Michael 4v2); geo spot-checks corroborate both sides of the
splits (Welches Grove Photon St James, Warrens Nominatim St
Michael, Nominatim BB23006 tag on Bagatelle St James) with no
2-signal case to move any primary. BB190215 held out (6-digit
Todds Land typo vindicated: BB19021 carries other districts,
BB19215 absent, no safe retarget). UPU profile unreachable (live
fileadmin URL serves the site shell, Wayback 429, parcel compendium
timeout, GeoNames has no BB postal export) — no confidence impact,
the finder JS is the stronger official oracle.

## Belize

The bundled `BelizeGeographyProvider` supplies the 6 districts
as `State` rows and a single-level administrative hierarchy. It
is selected with `SeedCountryGeographiesAction::execute('BZ')`
after countries are seeded. City, town, village, and community
councils exist below the districts but no consolidated
district-mapped list ships, and the 31 constituencies are
electoral only, so districts stay terminal.

Belize has no postcode system.

Revisit 2026-10-03 (verify-only, zero changes; `gate_bz.py` ALL
PASS): 6/6 ISO 3166-2:BZ codes exact; UPU blzEn (05/2021)
confirms Post-Office-reference addressing with no postcode
system.

## Bermuda

The bundled `BermudaGeographyProvider` supplies the 9
parishes as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BM')` after countries are
seeded. The City of Hamilton and the Town of St. George ship as
level-2 municipalities under Pembroke and Saint George's
parishes respectively.

Bermudian postcodes print right of the locality
(`SMITH'S FL 07`).

Revisit 2026-10-03 (fix-and-fill, 105 → 113 links; `gate_bm.py`
ALL PASS): tree exact vs Statoids (no ISO 3166-2:BM codes
exist); parish code HA held (GEC parish trigram is HAM but HA
is provider-baked, internal, single-signal). Links rebuilt from
the BPO 2013 Blue Pages street vote (2,063 rows, official
directory) with GeoNames BM corroboration: 80-code street set
exact (box codes GE CX / HM GX class excluded); primary moves
DV 04 → Paget 18v2, FL 01/FL 03 → Devonshire, FL 04 →
Hamilton, SB 04 → Southampton 25v0, WK 01 → Southampton,
HS 02 → Saint George's 15v13v9 plurality, GE 03/GE 05 → Town;
HM restructure (BPO lists parishes street-by-street with City
rows only for HM 08/09/10/11/12/17/19 — GeoNames agrees on the
exact set — so HM 01-07/13-16/18/20 dropped the city link and
HM 14/15/16/18/19/20 gained Devonshire); ties keep status-quo
primaries with added secondaries (DV 03 12v12, PG 01 15v14,
SB 03 14v13); GE 01 dropped the town link (0 town rows in BPO
and GeoNames). Municipality renamed to BPO-exact "Town of
St. George".

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

Revisit 2026-10-04 (fix: 8 renames, no count changes; `gate_bo.py`
ALL PASS): all 9 departments ISO-exact and all 112 provinces
diffed name-by-name against the 9 EN WP department tables +
Statoids HASC + ES WP + GeoNames 2025. Renames: Campero→Narciso
Campero, Murillo→Pedro Domingo Murillo, Atahuallpa→Sabaya
(documented rename: EN article "formerly Atahuallpa" + ES WP +
GeoNames ADM2 2025; Statoids pre-rename), Burnet→Burdett O'Connor
(EN canonical redirect + ES infobox/body/category + person
etymology; the lone Burnet hit is a stale map filename),
Pantaléon→Pantaleón Dalence, Tomas→Tomás Barrón, Sur→Sud
Chichas/Lípez. Held deliberately: Marbán, Loayza, Jaime Zudáñez,
Azurduy (WP + CSV over Statoids' full-form "Juana Azurduay de
Padilla"), Bolívar (WP + CSV over Statoids' full form), Sebastián
Pagador, Manuel María Caballero, Obispo Santistevan (article
titles over display-text variants). Verdict stays none: UPU bolEn
profile (02/2026) codeless on all three examples, UPU Sep-2025
list carries Bolivia on do-not-require, GeoNames has no BO postal
dump (404).

## Caribbean Netherlands

The bundled `CaribbeanNetherlandsGeographyProvider` supplies
the 3 special municipalities (Bonaire, Saba, Sint Eustatius) as
`State` rows and a single-level administrative hierarchy. It is
selected with `SeedCountryGeographiesAction::execute('BQ')`
after countries are seeded. Municipalities are terminal.

Addresses print the island as its own line below the town
(`KRALENDIJK`, `Bonaire`). The tier is labelled
`Bijzondere Gemeente`.

Revisit 2026-10-03 (verify-only, zero changes; `gate_bq.py` ALL
PASS): 3/3 ISO 3166-2:BQ codes exact; no postcode system (UPU
besEn carries operator info only, no postcode section; NL
postcodes article: "do not as yet have postal codes"). Watch:
Dutch government plans island postcodes by end 2026.

## Dominica

The bundled `DominicaGeographyProvider` supplies the 10
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('DM')` after countries are
seeded. Parishes are terminal.

Dominica has no postcode system.

Revisit 2026-10-03 (verify-only, zero changes; `gate_dm.py` ALL
PASS): 10/10 ISO 3166-2:DM codes (02–11, 01 unassigned); UPU
dmaEn is example + contact only.

## Grenada

The bundled `GrenadaGeographyProvider` supplies the 6 parishes
and the Carriacou dependency as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GD')` after countries are
seeded. Parishes and the dependency are terminal.

Grenada has no postcode system.

Revisit 2026-10-03 (verify-only, zero changes; `gate_gd.py` ALL
PASS): 7/7 ISO 3166-2:GD codes (GD-10 Southern Grenadine Islands
ships as dependency Carriacou); UPU grdEn carries no postcode
section.

## Jamaica

The bundled `JamaicaGeographyProvider` supplies the 14
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('JM')` after countries are
seeded. Parishes are terminal; postal towns below them are not
administrative.

Jamaican addresses print street lines, locality, post town,
parish, and country.

Revisit 2026-10-03 (verify-only, zero changes; `gate_jm.py` ALL
PASS): 14/14 ISO 3166-2:JM codes exact. No postcode system per
the UPU jam profile (Kingston sector codes are not postcodes
and ship no directory), so no postal files exist.

## Saint Lucia

The bundled `SaintLuciaGeographyProvider` supplies the 10
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('LC')` after countries are
seeded. Districts are terminal.

Saint Lucian postcodes print right of the locality
(`CASTRIES, LC04  101`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_lc.py` ALL
PASS): 10/10 ISO 3166-2:LC codes (04/09 unassigned); government
postcode table re-pulled — 47 delivery codes set-identical with
all 47 district links matching (Babonneau town in Castries
Quarter), Marisule LC01 501 dual kept (Castries primary per the
table, Gros Islet border secondary), 7 private-box codes
excluded.

## Saint Vincent and the Grenadines

The bundled `SaintVincentAndTheGrenadinesGeographyProvider`
supplies the 6 parishes as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('VC')` after countries are
seeded. Parishes are terminal.

Vincentian postcodes print on their own line below the town
(`KINGSTOWN`, `VC0120`).

Revisit 2026-10-03 (fix: VC0360 Layou saint-patrick → saint-andrew,
56/56 links kept; `gate_vc.py` ALL PASS): tree verified — 6/6 ISO
3166-2:VC codes; all 56 code→locality→parish links cross-checked
(SVG official table via Wayback × Photon counties + GeoNames
admin1 + parish/village articles + Nominatim). Layou moves on 5
seat signals (Layou infobox parish, St Andrew capital claim,
GeoNames PPLA/02, Statoids chief town, Mapanet coded filing) over
the OSM boundary + St Patrick list error. VC0170 Edindoro/Ottley
Hall kept St Andrew (delivery point is the leeward
Edinboro/Ottley Hall area; St George mirror filings are noise —
Mapanet demonstrably misfiles Belair/Evesham/Buccament).
VC0100 held out (Kingstown box-only, Andorra-precedent exclusion)
and VC0292 Mesopotamia held out (disputed: Photon St George vs
GeoNames Charlotte) — both build omissions vindicated.

## Trinidad and Tobago

The bundled `TrinidadAndTobagoGeographyProvider` supplies the
5 boroughs, 7 regions, 2 cities, and 1 ward as `State` rows and
a single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TT')` after countries are
seeded. The first level is already the municipal level, so
there is no tier-2.

Postcodes print right of the locality (`CHAGUANAS 500234`).

Revisit 2026-10-03 (verify-only on the tree, zero changes;
`gate_tt.py` ALL PASS): 15/15 ISO 3166-2:TT corporations exact
incl. types. Gap (not verdict none): TT runs a live 6-digit
postcode system (UPU tto 05/2014; 72 postal districts, first-2
= delivery office) but no public district directory exists
(TTPost finder is per-address only; mirrors carry fragments),
so no overlay can be built (TV TUV-gap precedent).

## Turks and Caicos

The bundled `TurksAndCaicosGeographyProvider` supplies the 6
districts as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('TC')` after countries are
seeded. Districts are terminal.

The UK-style postcode prints on its own line (`TKCA 1ZZ`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_tc.py` ALL
PASS): 6 districts (2 Turks + 4 Caicos; East Caicos under South
Caicos, West Caicos under Providenciales; no ISO 3166-2:TC
codes); UPU tcaEn (10/2025) confirms the single TKCA 1ZZ
code-only import.

## Montserrat

The bundled `MontserratGeographyProvider` supplies the 3
parishes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MS')` after countries are
seeded. Parishes are terminal; villages below them have no
separate administration. Saint Peter is the only inhabited
parish; Saint Anthony and Saint Georges are volcanic exclusion
zone (Plymouth sits in Saint Anthony).

Montserrat postcodes print right of the locality
(`Brades, MSR1110`).

Revisit 2026-10-03 (fix: phantom parish drop + 1 retarget, 8/8
links kept; `gate_ms.py` ALL PASS): dropped `ms:parish:saint-patrick`
— Saint Patrick's is a destroyed village (GeoNames PPLW), not a
parish; three parish articles + Statoids + GENC + the ISO draft
all say three parishes, and no ISO 3166-2:MS codes exist.
Retargeted MSR1310 Cudjoe Head saint-anthony → saint-peter on 3
signals (UPU parish digit 1, Photon county Saint Peter, Saint
Anthony wholly uninhabited). All 8 sub-post-office codes are
1xxx → Saint Peter primaries; Saint Georges/Anthony codeless by
exclusion zone.

## Greenland

The bundled `GreenlandGeographyProvider` supplies the 5
municipalities as `State` rows and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('GL')` after countries are
seeded. Municipalities are terminal; towns are municipal seats,
not administrative units.

Greenlandic postcodes print left of the locality
(`3900 Nuuk`). The tier is labelled `Kommune`.

Revisit 2026-10-03 (fix: +1 fill 3985 → Sermersooq, 28/28 links;
`gate_gl.py` ALL PASS): tree verified — 5/5 ISO 3166-2:GL codes;
all 28 code→town→municipality links cross-checked (GeoNames GL
dump × wiki towns list + Nuussuaq/Qaarsut/Kangilinnguit/Ikerasassuaq
articles). 3985 Nerlerit Inaat / Constable Pynt fills on official
Mittarfeqarfiit addressed usage + 3 mirrors (GeoNames misses live
3984/3985). 3970 Pituffik kept on enclosing Avannaata (unincorporated
enclave, served_by). Out of the municipal system: 3972 Station
Nord + 3982 Mestersvig + 3984 Danmarkshavn (all National Park) +
2412 Santa novelty.

## Saint Martin

The bundled `SaintMartinGeographyProvider` supplies the single
overseas collectivity as the `State` row and a single-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('MF')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97150 SAINT-MARTIN`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_mf.py` ALL
PASS): single collectivity (ISO 3166-2:MF defines no codes);
UPU mafEn (08/2011) confirms the single code 97150.

## Saint Pierre and Miquelon

The bundled `SaintPierreAndMiquelonGeographyProvider` supplies
the single overseas collectivity as the `State` row and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PM')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97500 Saint-Pierre`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_pm.py` ALL
PASS): single collectivity (ISO 3166-2:PM defines no codes);
UPU spmEn (08/2011) confirms the single code 97500 shared by
both communes (worked example is a Miquelon address).

## Saint-Barthélemy

The bundled `SaintBarthelemyGeographyProvider` supplies the
single overseas collectivity as the `State` row and a
single-level administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('BL')` after countries are
seeded. There is no tier-2.

Addresses follow the French layout with the code left of the
locality (`97133 Gustavia`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_bl.py` ALL
PASS): single collectivity (ISO 3166-2:BL defines no codes);
UPU blmEn (08/2011) confirms the single code 97133.

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

Revisit 2026-10-03 (fix-and-fill, areas-only, zero link changes;
`gate_gf.py` ALL PASS): tree 22/22 + overlay 25/25 exact vs
Hexasmal + GeoNames GF (52 CEDEX rows correctly excluded).
Fixes: "Papaichton"→"Papaïchton" + "Remire-Montjoly"→
"Rémire-Montjoly" (GeoNames + WP titles; ST Lemba precedent)
and the Papaichton area code 97340→97316 (Hexasmal lists
Papaichton only under 97316; 97340 belongs to Grand-Santi).

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_tf.py` ALL
PASS): 5 districts match the UPU atfEn enumeration (ISO
3166-2:TF includes no codes); UPU confirms uninhabited with no
domestic postcode system (TF mail routes via foreign Réunion
codes).

## US Minor Outlying Islands

The bundled `USMinorOutlyingIslandsGeographyProvider` supplies
the 9 islands as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('UM')` after countries are
seeded. The islands are uninhabited (military and wildlife
stations) and terminal.

Addresses print the station and island lines with no postcode.

Revisit 2026-10-03 (verify-only, zero changes; `gate_um.py` ALL
PASS): 9/9 ISO 3166-2:UM codes exact; UPU umiEn confirms the
islands follow the US postal system (state code UM) with no
permanent population and no UM domestic system.

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

### Revisit (B20, 2026-10-06)

Verify-only, zero data changes (`gate_pr.py` ALL PASS).
Tree == U.S. Census 2024 gazetteer exactly: 78/78 L1
(FIPS + name accent-exact) and 901/901 L2 (key + name +
type + per-municipio counts); L1 seconded by wiki /
pr.gov / Ley 70; the wiki "902 barrios" infobox claim is
a stale 2011 cite, rejected. ISO 3166-2:PR defines no
subdivisions (stub real). Postal set == GN 177/177, legs
177/177 match GN municipio; two directory-omission scares
(00636, 00930) proven stale-index artifacts and kept.
HOLD H1: 00938 kept on split evidence (GN-current + usage
+ federal docs vs 3 aggregator omissions) — needs a human
USPS-finder lookup. HOLD H2: USPS/HUD oracles 403/404 to
programmatic fetch, so GN-current + directory consensus +
federal docs covered the oracle role.

## U.S. Virgin Islands

The bundled `USVirginIslandsGeographyProvider` supplies the 3
districts as `State` rows and a two-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('VI')` after countries are
seeded.
The 20 subdistricts ship as level-2 areas under their districts.

Virgin Islander addresses use the US ZIP layout
(`ST THOMAS VI 00802-1222`, ZIP+4 supported).

Revisit 2026-10-03 (verify-only, zero changes; `gate_vi.py` ALL
PASS): 3 districts + 20 subdistricts exact vs Statoids (codes
SC/SJ/ST; no ISO 3166-2:VI codes). Overlay 16/16 at district
level exact vs the GeoNames VI dump + UPU vir range
00801-00851 (Christiansted/Frederiksted/Kingshill 00820-00851
all Saint Croix); no public per-subdistrict directory exists.

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

Revisit 2026-10-03 (verify-only, zero changes; `gate_sh.py` ALL
PASS): 8 districts + 2 islands (ISO SH-AC/SH-TA; HL districts
synthetic 01–08); UK postcode areas list + territory article
confirm STHL/ASCN/TDCU 1ZZ (UPU shnEn covers STHL only);
STHL shared by all 8 districts with Jamestown GPO primary.

## Mayotte

The bundled `MayotteGeographyProvider` supplies the 17
communes as `State` rows and a single-level administrative
hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('YT')` after countries are
seeded. The first level is already the municipal level, so
there is no tier-2.

Mahoran addresses follow the French layout with the code left
of the locality (`97600 MAMOUDZOU`).

Revisit 2026-10-03 (verify-only, zero changes; `gate_yt.py` ALL
PASS): 17/17 communes vs Hexasmal INSEE 97601-97617 + GeoNames
YT admin2 (01-17 synthetic; no ISO 3166-2:YT codes). Overlay
11/18 exact vs Hexasmal (both sides of all 7 duals; primaries
keep bundled orientation — Hexasmal lists both communes as
acheminement) + GeoNames YT (2 Mamoudzou centroid-dup rows on
97650/97680 correctly excluded). UPU myt confirms the 976
department digit.

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

1229 postal localities (suburbs, towns, rural-delivery names)
ship as level-3 `locality` areas under their district, with a
`postal` hierarchy (region > locality, refined by district)
and the `postal_locality` role. The 1769-code overlay links
each code to its locality (0110–9893; 1081 shared by Ostend
and Surfdale, Ostend primary; 4180 leg-less pending PAF).
58 places needed manual
resolution: GeoNames rows with empty admin2 (Tararua and
Waitaki Valley clusters, Petone, Turangi), name variants
(Puk-kura = Pukekura, Weheka = Fox Glacier), and Waioruarangi
7300 assigned to Kaikōura on NZ Post delivery rather than its
Waiau-side dump coordinate.

New Zealand postcodes print left of the locality
(`6011 Wellington`).

### Revisit (B21, 2026-10-06)

Tree 6 renames + 28 appends, postal
1737/1738 → 1769/1769 (`gate_nz.py` ALL
PASS). L1 17/17 ISO (incl. CIT); L2
67/67 TA exact. Renames: Manawatū
macron, Chatham Islands Territory,
Whanganui ×2, Feilding/Hastings case.
28 L3: 13 homonym splits (Woodhill/
Kensington/Meremere/Hillsborough ×2/
Tokomaru/Waverley ×2/Richmond/Avondale/
Sunnyvale/Ngapuna/Parkvale) + 15
suburbs (One Tree Point, Flat Bush,
Hamurana, Te Puna, Minden, Tauwhare,
Acacia Bay, Linton, Swannanoa, Eyrewell,
Pegasus, West Melton, Levels, Tora,
Chatham Islands). Postal: 32 onzp-
street adds (0 drops) + 23 retargets
(21 homonym side-fixes + Nukuhau
phantom + doubly-wrong Waipawa→Tora).
Judgment calls recorded: 3384 Wairakei
over Oruanui (10-10 tie, 8v7 single +
principal settlement), 5583 Manakau
over Ōtaki 7v6, 7050 OSM-Westland
refuted by prefix allocation. Holds:
4180 leg (no 2-signal target), ~87
lobby-code gap (paid PAF), 57
waimate-only singles.

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
php artisan db:seed --class="AIArmada\Addressing\Database\Seeders\AddressCountrySeeder"
php artisan db:seed --class="AIArmada\CommerceSupport\Database\Seeders\CurrencySeeder"
php artisan db:seed --class="AIArmada\CommerceSupport\Database\Seeders\LanguageSeeder"
php artisan db:seed --class="AIArmada\CommerceSupport\Database\Seeders\TimezoneSeeder"
php artisan db:seed --class="AIArmada\Addressing\Database\Seeders\AddressingSeeder"
```

This is idempotent — running it multiple times is safe.
Revisit 2026-10-03 (1 secondary added; `gate_me.py` ALL PASS): a fresh
Pošta CG branch-network pull (165 branches, 150 unique codes via the
`poste` REST collection plus the listing-grid AJAX) matches the bundled
149 exactly once the 80000 Express-hub service code is set aside — zero
bundled-only codes, so no fills. The 25 municipalities match ISO
3166-2:ME code for code (01–25 incl. 22 Gusinje, 23 Petnjica, 24 Tuzi,
25 Zeta); ME-06 stays `Old Royal Capital Cetinje` (official
Prijestonica style, corroborated by the Municipalities of Montenegro
lead — ISO's short `Cetinje` is the outlier). All 164 branch pins were
reverse-geocoded: 157/157 agree with the bundled municipality. The one
fix: 85333 gains a Kotor secondary — Pošta's directory carries both the
Lepetane office (Tivat, opened Dec 2023) and Dobrota 2 (Kotor) under
85333, Pošta's own Jun-2023 notice names "pošta 85333 Dobrota 2", and
Dobrota usage corroborates; the Tivat primary stands on overwhelming
Lepetane addressed usage. Two pin artefacts were overruled, not
followed: the 84216 pin lands 85 km off in Nikšić but Kovačevići
village is Pljevlja (sr.wiki, Nominatim, RC/block fit), and the 85317
pin sits ~350 m across the border in Podlastva (Budva) while Lastva
Grbaljska village, itself tagged 85317, is Kotor. Retired with evidence:
81122 (Pošta's 2017 opening news, Brskutska 5 Zlatica), 81125 (branch
page 2021–2023), 81128 (live Aug 2022 per Pošta notice, Boška Buhe 24;
page until the Sep-2025 rebuild), and the Ljubotinj branch (2021–2023;
mail now addressed 81250) — all with zero live traces. Held out as
stale: 85354/85357 circulate in commercial geodata but scatter across
codes in Čanj (85355/85000/85357) and central Bar, with zero Pošta
traces; 81201 is a single OSM depot tag (Pošta carinjenja); the OSM
83510/81310/85434-class hits are digit transpositions (Pošta's own
notices even print `83313` for 85313). UPU anchors: 81000 Podgorica,
85000 Bar, 84000 Bijelo Polje, 85330 Kotor, 81205 Ubli. Per-municipality
links: Podgorica 29, Nikšić 16, Kotor 12 (11 primary + 85333
secondary), Bar 10, Bijelo Polje 8, Budva 8, Herceg-Novi 8, Pljevlja 8,
Cetinje 7, Tivat 6, Ulcinj 6, Kolašin 5, Rožaje 4, Danilovgrad 4,
Plužine 3, Šavnik 3, Andrijevica 2, Berane 2, Plav 2, Žabljak 2,
Gusinje 1, Mojkovac 1, Petnjica 1, Tuzi 1, Zeta 1.
Revisit 2026-10-03 (1-cell fix: Phoenix council display name
Canton→Kanton, source_id `ki:council:canton` stable; 25/25 links
verified, zero link changes; `gate_ki.py` ALL PASS): the MICTTD
official 37-code table was re-pulled via archive (Oct-2020 + Jan-2022
snapshots byte-identical on the table, zero drift; page 404 since
May-2022, live micttd.gov.ki unreachable) — bundled 25 =
KI0101–KI0121 + KI0201 + KI0301–KI0303, each MICTTD island label
mapping 1:1 to its council (Tabnorth/Tabsouth→Tabiteueas, South
Tarawa (Betio)→Betio, Christmas→Kiritimati); KI0106+KI0107 both
South Tarawa per the table's explicit Tangintebu–Tanaea /
Bairiki–Taborio zones, GPO Bairiki on KI0107 (UPU kirEn examples
corroborate KI0102/KI0107/KI0108/KI0303 usage). Kanton rename:
MICTTD + 2020 census Table G-1 (pop 41) + CLGF/MISA + Factbook
council lists all spell Kanton, and the tree already prefers
I-Kiribati forms (Kiritimati over Christmas); en-wiki article
title + Canton Island Airport keep Canton as the recognized
alternate. The 12 held-out codes (KI0202–KI0208 Birnie, Enderbury,
Manra, McKean, Nikumaroro, Orona, Rawaki; KI0304–KI0308 Malden,
Starbuck, Millennium/Caroline, Vostok, Flint) are each uninhabited
per en-wiki and absent from census Table G-1 — no fill case,
exclusion stands; GeoNames has no KI postal export (KI.zip 404,
verified). Tree holds: short group names kept (ISO 3166-2:KI's
Gilbert/Line/Phoenix Islands is the sole rename signal; MICTTD
carries no group labels, census splits Gilbert Group vs Line
Islands); 24 councils reconciled against CLGF's 23 island + 3
town (TUC is the Island Council of South Tarawa per 2024 MFMRD
usage, Kiritimati single per en-wiki/Factbook — the +2 are
dual-role double counts); census Teeraina + CLGF Tabuaran/Butariti
variants held out (MICTTD/UPU agree Teraina/Tabuaeran/Butaritari).
Revisit 2026-10-03 (verify-only, zero changes; `gate_ie.py` ALL
PASS): 4 provinces + 26 counties (12/6/5/3 Leinster/Munster/Connacht/Ulster)
match the ISO 3166-2:IE table code-for-code, prefetched and live
re-fetches agreeing; LK/TA/WD are the ISO second parts (single-letter
L/T/W are vehicle-registration marks, not ISO). Dublin holds as a single
`ie:county:dublin` post-county (IE-D); no oracle demands the Fingal /
South Dublin / Dun Laoghaire admin split on the postal surface. The 139
bundled Eircode routing keys match three independent rosters exactly
(Wikipedia routing-areas table, Autoaddress routing-keys article with a
139-count docs anchor, WooCommerce issue roster), stable since 2018, with
D6W the sole alpha-tail key. All 139 primaries match the wiki post-county
across 153 town rows, and exactly two keys straddle: A82 Meath primary +
Cavan secondary (Kells vs Kingscourt/Virginia) and A92 Louth primary +
Meath secondary (Ardee/Drogheda vs Laytown-Bettystown-Mornington),
each confirmed by wiki post-county plus Autoaddress descriptors plus OSM
town lookups — 141 links, first-listed primaries, no moves, no missing
secondaries per the stability rule. UPU irlEn.pdf anchors the semantics:
7-char Eircode, routing key as "principal post town span of delivery",
county line with Eircode last, matching the provider's full-code-strips-
to-routing-key lookup. Postal files stay CRLF, areas LF; every county
holds at least one key (Dublin 34, Cork 23).
## Sri Lanka (LK) revisit — B7 bulk verification (verify-only, no changes)

Verdict: VERIFY-ONLY. All 2121 bundled codes and all 2121 district primaries
confirmed against independent oracles. No adds, no moves, no removals.
Gate `docs/agents/audit/gate_lk.py` reports ALL PASS (97 checks) on the
current tree.

### Oracles fetched (October 2026, independently)

1. ISO 3166-2:LK (`/tmp/geo-verify/B7/iso-LK.json`, prefetched): 9 provinces
   (LK-1..LK-9) + 25 districts (LK-11..LK-92). Roster, names, and
   district-first-digit parent rule all match the bundled areas file.
2. SL Post Post Code Directory, official 2022 PDF book
   (`https://slpost.gov.lk/wp-content/uploads/2022/11/POST-CODE-BOOK-.pdf`,
   55 pages, parsed to 2111 distinct codes with district tags APR/AR/AD/BC/
   BD/CO/GL/GQ/HB/JA/KE/KG/KO/KT/KY/MB/MH/MJ/MP/MT/NW/PR/PX/RN/TC/VA/WP).
   Every one of the 2111 book codes is bundled, and all 2111 district tags
   agree with the bundled primaries (Ampara uses two SL Post tags: APR for
   the Ampara region, AR for the Kalmunai region; WP rows carry a GQ tag;
   three HTML-source artefacts — `Kandy KY)`, `Kannattota KE)`, `Bopitiya
   (SABARA)( KE)` — resolve to KY/KE/KE by inspection).
3. SL Post online postcode lookup (`https://slpost.gov.lk/postcode_new/`,
   embedded office list, 2111 distinct codes): identical code set to the
   2022 book, 0 codes missing from bundled, district tags agree 2111/2111.
4. GeoNames LK.zip postal dump (1837 rows, 1833 distinct codes): district
   agreement 1798/1798 on overlapping codes, 0 mismatches, 0 multi-district
   codes. GeoNames has no Kilinochchi rows and ~no Northern Province
   coverage otherwise, which explains most of the 323 bundled-only codes.
5. calllanka Colombo pages (`postal-code-colombo.php`,
   `Sri-Lanka-Postal-Code-District-Wise.php`) + advice.lk Colombo 1-15
   guide (`advice.lk/colombo-suburbs-list/`): both list all 15 Colombo
   city zones 00100..01500 with names. advice.lk explicitly notes the
   non-office zones are "Not a separate entry" in the Post Code Directory
   (delivery sectors served from main offices), explaining their absence
   from oracles 2-3. GeoNames independently confirms 00100 (Fort), 00300
   (Colpetty), 00400 (Bambalapitiya), 01500 (Mutwal).

### Bulk crosswalk (all 2121 codes, not samples)

- 2111 codes: bundled == SL Post book == SL Post online, districts agree.
- 10 codes (00100, 00300, 00400, 00700, 00900, 01000, 01100, 01200, 01400,
  01500 — all Colombo): absent from both SL Post surfaces as expected
  (delivery sectors, not separate offices); each confirmed by 2+
  independent signals (calllanka + advice.lk for all 10; GeoNames for
  00100/00300/00400/01500; 00200/00500/00600/00800/01300 are in SL Post
  too). KEPT per the stability rule.
- 35 GeoNames-only codes: every one proven stale, none added (single
  GeoNames signal only, contradicted by SL Post): Nuwara Eliya block
  renumbered 205xx->225xx / 206xx->226xx / 2074x-2075x->2274x-2275x (same
  office names in the book, e.g. 20560 Kotmale->22560, 20680 Ginigathena->
  22680 Ginigathhena, 20748 Maturata->22748); 22040->22042, 50567->31017
  Pulmoddai (GeoNames district Anuradhapura also wrong, true Trincomalee),
  70252/70256->91252/91256, 81318->81308, 82401->82104, 82586->82506,
  91040/91042->32040/32042 (GeoNames district Monaragala also wrong, true
  Ampara), 96167->90167; 20186/20568/20684/32155/70254 have no SL Post
  office behind them at all.
- Cross-block keeps verified in SL Post oracles: 10660/10662/10664 Pugoda
  (Gampaha inside the 106xx Colombo range), 42530/42532/42534
  Puthukkudiyiruppu (Mullaitivu inside the 425xx Kilinochchi range),
  43583 Bogaswewa (Vavuniya outlier), 32198 Malwatta (AR/Kalmunai
  sub-range inside Ampara district).

### Per-district primary code counts (25 districts, sum 2121)

| district | codes | district | codes | district | codes |
|---|---|---|---|---|---|
| ampara | 67 | gampaha | 134 | matale | 74 |
| anuradhapura | 134 | hambantota | 67 | matara | 82 |
| badulla | 145 | jaffna | 51 | monaragala | 66 |
| batticaloa | 48 | kalutara | 84 | mullaitivu | 18 |
| colombo | 71 | kandy | 179 | nuwara-eliya | 79 |
| galle | 96 | kegalle | 102 | polonnaruwa | 68 |
| kilinochchi | 28 | kurunegala | 217 | puttalam | 90 |
| mannar | 25 | ratnapura | 132 | trincomalee | 43 |
| vavuniya | 21 | | | | |

### Holds / gaps

- None blocking. The 10 Colombo delivery-sector codes rest on
  calllanka + advice.lk (+ GeoNames for 4 of them) rather than SL Post
  surfaces; both zone guides agree on names and numbers, and the
  Colombo 01-15 scheme is corroborated by idam.lk, lakpura.com, and the
  lankapost PyPI dataset. No second SL Post surface lists them because
  SL Post directories enumerate post offices, not delivery sectors.
- GeoNames remains unusable as a primary LK oracle for the Northern
  Province (entire Kilinochchi district + most of Jaffna/Mannar/
  Mullaitivu/Vavuniya absent) and carries ~35 stale renumbers; SL Post
  oracles take precedence wherever they disagree.

## Seychelles (SC) revisit — B7 verification (verify-only, no changes)

B7 revisit of the 27-district tree against ISO 3166-2:SC (live
re-fetch), the WP districts article, the UPU syc profile (Wayback
2002 addressing sheet), and the GeoNames postal-dump index. Verdict:
verify-only — zero data changes; `gate_sc.py` ALL PASS.

### Oracles fetched

- ISO 3166-2:SC wikitext (live): 27 current codes SC-01..SC-27,
  exact code match with bundled. OBP changes: SC-26 Ile Perseverance
  I + SC-27 Ile Perseverance II added 2020-11-24; SC-24/25 added
  2010-06-30.
- WP Districts of Seychelles: STALE — still claims 26 districts
  with Nr 26 = Outer Islands and no Perseverance rows; predates the
  2020 ISO additions. Not used as a roster oracle.
- UPU syc profile: Victoria/Plaisance addressing example carries
  no postcode.
- GeoNames export index: no SC.zip (MW/RE/GP/IE/LK all present in
  the same index) — second no-system signal.

### Name forms (bundled local French vs ISO ASCII)

- Anse-aux-Pins, Grand'Anse Mahé, Grand'Anse Praslin,
  La Rivière Anglaise, Pointe La Rue kept over the ISO ASCII
  renderings (Anse aux Pins, Grand Anse Mahe, English River,
  Pointe Larue) — local official forms, WP titles agree.
- Roche Caiman kept ASCII: the WP table displays "Roche Caïman"
  but the article title + ISO are ASCII — single weak display
  signal held out per the stability rule.

### Holds / gaps

- Outer Islands carry no ISO code and ship no row (WP's
  district claim is pre-2020 stale); revisit if ISO adds one.
- No postal files ship (no-system); gate asserts their absence.

## Tonga (TO) revisit — B7 verification (verify-only, no changes)

B7 revisit of the 5-division / 23-district tree against
ISO 3166-2:TO (live re-fetch), WP Administrative divisions of
Tonga, Statoids, the UPU ton profile (Wayback 2002 sheet), and the
GeoNames postal-dump index. Verdict: verify-only — zero data
changes; `gate_to.py` ALL PASS.

### Oracles fetched

- ISO 3166-2:TO wikitext (live): TO-01..TO-05 divisions only;
  district codes are not ISO.
- WP Administrative divisions of Tonga: division names
  (Tongatapu, Vavaʻu, Haʻapai, ʻEua, Ongo Niua) + full 23-row
  district table with TO-011..TO-056 codes matching bundled
  exactly — except the WP row for Haʻano duplicates TO-024
  (Muʻomuʻa's code), a typo; bundled TO-025 sequential is correct.
- Statoids Divisions of Tonga: primary division name "Niuas"
  with "Ongo Niua (variant)" — bundled Niuas stands on
  ISO + Statoids vs the single WP-article rendering.
- UPU ton profile: contact-only sheet, no addressing example.
- GeoNames export index: no TO.zip — second no-system signal.

### Per-division district counts

ʻEua 2, Haʻapai 6, Niuas 2, Tongatapu 7, Vavaʻu 6 (sum 23).

### Holds / gaps

- District codes TO-011..TO-056 are WP-table convention, not ISO;
  kept as bundled (full 23-row pin in `gate_to.py`).
- Ongo Niua recorded as the recognized division-name variant.
- Minerva Reefs intentionally unlisted (no district per WP note).
- No postal files ship (no-system); gate asserts their absence.

## Bahamas (BS) revisit — B7 verification (verify-only, no changes)

B7 revisit of the 32-subdivision tree against ISO 3166-2:BS (live
re-fetch), the UPU bhs profile (Wayback addressing sheet), and the
GeoNames postal-dump index. Verdict: verify-only — zero data
changes; `gate_bs.py` ALL PASS.

### Oracles fetched

- ISO 3166-2:BS wikitext (live): 32 current codes (1 island New
  Providence + 31 districts) — exact code/name/category match
  with bundled. BS-AC/FC/GH/GT/HR/KB/MH/NB/RS/SP/SR appear only
  in the Changes section as retired.
- UPU bhs profile: explicit "The Bahamas do not apply a
  postcode system or home delivery system."
- GeoNames export index: no BS.zip — second no-system signal.

### Holds / gaps

- Retired BS-SP etc. correctly absent from the tree.
- Nassau N-0000-style commercial renderings are not postcodes;
  none ship. No postal files exist; gate asserts their absence.

## Réunion (RE) revisit — B7 verification (fix: 97428 out, 97490 in)

B7 revisit of the 4-district / 24-commune tree and 37-code overlay
against La Poste Hexasmal (current, via data.laposte.fr), GeoNames
RE.txt, the BAN address API, and the WP communes table. Verdict:
fix-and-fill — phantom 97428 removed, real 97490 added (37 codes /
37 links before and after); `gate_re.py` ALL PASS.

### Oracles fetched

- Hexasmal 974: 36 geographic codes; every bundled code maps to
  the same commune INSEE except bundled-only 97428 (absent) and
  Hexasmal-only 97490 ST DENIS (97411).
- GeoNames RE.txt (152 rows): no 97428 anywhere (not even CEDEX);
  97490 Saint-Denis present; all other unlisted rows are CEDEX
  (974xx + 977/978 BL/MF CEDEX), correctly excluded.
- BAN api-adresse: zero addresses for 97428; live 97490
  Saint-Denis addresses (Chemin Finette etc.).
- WP Communes of the Réunion department: 24/24 INSEE + names +
  arrondissement parents exact — but its single-code Postal column
  lists stale 97428 for Saint-Paul (WP-table simplification; the
  commune's live codes are 97411/97422/97423/97434/97435/97460).

### Fix applied

- Removed `RE,97428` + `97428,re:commune:saint-paul` link
  (Hexasmal + GeoNames + BAN agree it is not a live code).
- Added `RE,97490` + `97490,re:commune:saint-denis` primary link
  (Sainte-Clotilde quarter; same three oracles).
- Saint-Denis now carries 97400/97417/97490; Saint-Paul keeps 6
  live codes. Overlay counts unchanged (37/37) so no overlay edit.

### Holds / gaps

- 97415 + 97443-97449 unassigned in Hexasmal; nothing to fill.
- CEDEX + 977/978 BL/MF rows intentionally excluded.

## Guadeloupe (GP) revisit — B7 verification (verify-only, no changes)

B7 revisit of the 2-district / 32-commune tree and 33-code overlay
against La Poste Hexasmal (current), GeoNames GP.txt, and the WP
communes table. Verdict: verify-only — zero data changes;
`gate_gp.py` ALL PASS.

### Oracles fetched

- Hexasmal 971: 33 GP-commune rows map exactly to bundled
  (code + commune INSEE), incl. 97134 Saint-Louis (97126) and
  double-coded Les Abymes (97139/97142); 97133 ST BARTHELEMY
  (97701) + 97150 ST MARTIN (97801) are BL/MF rows, correctly
  absent from GP.
- GeoNames GP.txt (105 rows): 33 non-CEDEX codes, exact set
  match with bundled.
- WP Communes of the Guadeloupe department: 32/32 INSEE + names
  exact (incl. accented Morne-à-l'Eau, Pointe-à-Pitre,
  Trois-Rivières, La Désirade, Saint-François); its principal-
  code column is consistent with bundled for all 32 communes.

### Holds / gaps

- 97124/97132/97135/97138 unassigned in Hexasmal; nothing to fill.
- CEDEX rows intentionally excluded.
Revisit 2026-10-03 (B7 fill: new 491-code / 491-link overlay;
`gate_mw.py` ALL PASS): tree verified clean — 3 regions + 28
districts (13/9/6 Southern/Central/Northern) match ISO 3166-2:MW
code-for-code; bundled region names stay English
(Central/Northern/Southern, matching WP + census) rather than the
ISO Chichewa forms. New overlay at district grain from the primary
oracle, Government Gazette 2019-05-10 General Notice 37 "Malawi
Postcodes 2019" pp.169-177, which reconciles all 491 GeoNames rows
(480 clean + 10 OCR-damaged rows + the Lulanga typeset-dupe row
adjudicated 301100 by the 100-pointer scheme rule and nowmsg +
ipostalcode mirrors). Corroboration: MACRA post-codes page via
Wayback 2026-05-17 (199-code subset, all matching; its 205112
Mavwere dupe loses to the gazette + GN 205113), UPU MWI profile
01/2021 (6-digit-before-locality format + exact 204101 Chakhaza /
312200 Blantyre CBD), UPU Aug-2022 type table (Malawi 999999 N),
WP List of postal codes (MW NNNNNN, citing MACRA),
postalcodes.com.ng (Dedza 9/9, Blantyre Rural 11/11, Dowa 10/10),
prostobank + ipostalcode spots, the faceofmalawi MACRA-launch
article (207201/312200/105200 CBD pins), and WP town articles
pinning Luchenza to Thyolo and Mzuzu to Mzimba. The UPU 2002 MWI
profile (Lilongwe delivery-area digit only) predates the system and
is superseded, as are stale "no system" mirrors; the UPU 2021
102010/309070/309010 examples are scheme-inconsistent
illustrations contradicted by the gazette and MACRA's own 312225
Chichiri footer. All 491 codes are single-primary, zero
secondaries: Lumbadzi Township 204108 stays Dowa per the gazette
block with no second Lilongwe signal, and the Ngabu twins coexist
(315110/315111 Chikwawa + 316106 Nsanje). Per-district counts:
Lilongwe 76, Blantyre 50, Kasungu 39, Mzimba 32, Zomba 32,
Mangochi 28, Machinga 19, Thyolo 17 (incl. Luchenza 309300),
Chikwawa 16, Nkhata Bay 14, Mchinji 14, Rumphi 13, Salima 12,
Ntcheu 12, Nsanje 12, Balaka 11, Chitipa 10, Nkhotakota 10, Dowa
10, Chiradzulu 10, Mulanje 10, Dedza 9, Ntchisi 8, Phalombe 8,
Karonga 7, Neno 6, Mwanza 4, Likoma 2 — no codeless district, no
holds, no gaps. Gazette-origin spellings ("Machinja BOMA", "Nkhota
Kota") live only in oracle place names, not in the code/link CSVs.
Revisit 2026-10-03 (verify-only, zero changes; `gate_fo.py` ALL
PASS): 29/29 municipalities + 6 sýslur regions vs WP Municipalities
of the Faroe Islands (Jan 2024 table; still 29 per Subdivisions +
Hagstova 29-municipality references; no ISO 3166-2:FO codes exist).
118-code delivery overlay exact vs the da "Færøske postnumre"
Posta table (130 rows, 12 marked postboks: 110/165/215/355/375/405/
515/535/610/710/810/910) + the fo "Postnummur í Føroyum" table (120
rows = 118 + postsmoga 110/165) + the GeoNames FO dump (130 rows;
every held-out row duplicates its delivery locality). WP Towns
crosswalk: 116/116 town codes in-bundle with agreeing
municipalities; FO-485 dual-leg (Runavík primary, Eystur secondary)
corroborated by both Skálafjørður rows. UPU fro profile confirms
FO+3 format (FO-100 Tórshavn example). Per-municipality primaries:
Tórshavn 17, Runavík 15, Sunda 12, Klaksvík 9, Eystur 6, Hvannasund
5, Kvívík 5, Sjóvar 5, Sørvágur 4, Sumba 4, Tvøroyri 4, Eiði 3,
Húsavík 3, Nes 3, Vágar 3, Fuglafjørður 2, Fugloy 2, Hvalba 2,
Kunoy 2, Skúvoy 2, Vágur 2, Fámjin 1, Hov 1, Porkeri 1, Sandur 1,
Skálavík 1, Skopun 1, Vestmanna 1, Viðareiði 1 (sum 118). Holds:
12 postboks codes stay out (non-geographic box variants). Name
holds: Porkeri, Vágar, Eystur kept per roster (tree convention;
WP Towns Sandavágur→Vágur cell is a typo — Miðvágur row + roster
confirm Vágar; Sunda spans Eysturoy/Streymoy, kept under Eysturoy).
Gaps: posta.fo is a JS SPA with no fetchable static directory
(covered by the da/fo Posta-derived tables); FO-510 Gøta + FO-925
Nes (Vágur) have no WP Towns row but are pinned by da+fo+GN.

## Rwanda (RW) revisit — B8 verification (verify-only, no changes)

B8 revisit of the 5-province / 30-district tree against
ISO 3166-2:RW (live re-fetch), WP Districts of Rwanda, and
citypopulation. Verdict: verify-only — zero data changes;
`gate_rw.py` ALL PASS.

### Oracles fetched

- ISO 3166-2:RW: current RW-01 City of Kigali + RW-02..05
  provinces, exact; RW-B..M are retired prefectures (Changes).
- WP Districts of Rwanda: current 30-district list by province,
  exact names + parents (East 7, Kigali 3, North 5, South 8,
  West 7).
- citypopulation Rwanda admin: same 30 districts by province,
  exact (parsed via per-district admin URLs).
- No-system triple: WP List of postal codes "no codes" + absent
  from the UPU Aug-2022 postcode-type table + no RW.zip in the
  GeoNames index. (No Wayback snapshot exists for the legacy
  UPU rwa sheet at any prefix.)

### Holds / gaps

- No postal files ship (no-system); gate asserts their absence.
- Statoids Rwanda carries provinces only — not used.

## Botswana (BW) revisit — B8 verification (fix: Selebi spelling)

B8 revisit of the 17-L1 / 23-subdistrict tree against
ISO 3166-2:BW (live re-fetch), WP Districts + Sub-districts of
Botswana, and Statoids. Verdict: fix-and-fill — one display cell
(Selibe -> Selebi Phikwe, source id stable) + matching provider
stateDefinitions cell; `gate_bw.py` ALL PASS.

### Oracles fetched

- ISO 3166-2:BW: 16 current codes, exact names/categories
  (Orapa absent — ISO lags; see below).
- WP Districts of Botswana: 10 districts + 2 cities + 5 towns
  (incl. Orapa town, pop 8648) — bundled L1 exact; urban table
  spells "Selebi-Phikwe".
- WP Sub-districts of Botswana: 23 subdistricts by district
  (Chobe + North-East N/A), exact names + parents.
- Statoids: "Selebi-Phikwe" spelling; Orapa township confirmed.
- gov.bw DailyNews + citypopulation (2022 census table): "Selebi
  Phikwe" — 4 signals vs stale ISO "Selibe Phikwe".
- No-system triple: WP List "no codes" + absent from UPU
  Aug-2022 type table + no BW.zip in GeoNames. (No Wayback
  snapshot for the legacy UPU bwa sheet.)

### Fix applied

- `bw:town:selibe-phikwe` name Selibe Phikwe -> Selebi Phikwe
  (space kept per the council's own "Selebi Phikwe Town
  Council" rendering; WP/citypopulation hyphen is house style).
- Provider `stateDefinitions()` SP entry updated in sync.

### Holds / gaps

- North-East/North-West hyphenated forms kept (WP titles).
- No postal files ship (no-system); gate asserts their absence.

## Burundi (BI) revisit — B8 verification (verify-only, no changes)

B8 revisit of the 5-province / 42-commune tree against the
enacted 2023 delimitation law (CENI scan, OCR), WP Provinces of
Burundi, citypopulation, and the UPU bdi profile. Verdict:
verify-only — zero data changes; `gate_bi.py` ALL PASS.

### Oracles fetched

- Loi Organique 1/05 du 16 mars 2023 (CENI scan, 78pp, OCR):
  Article 5 lists all 42 communes by province — exact match
  with bundled (Buhumuza 7, Bujumbura 11, Burunga 7,
  Butanyerera 8, Gitega 9; OCR artefacts Mayinga/Muyinga +
  Bururt/Bururi only). Capitals Cankuzo/Bujumbura/Makamba/
  Ngozi/Gitega match WP.
- WP Provinces of Burundi: 5-province reform (effective 2025,
  governors sworn Jul-2025) + territorial correspondence with
  the 18 former provinces.
- citypopulation Burundi admin: 42 communes by province, exact.
- fr.wiki Communes du Burundi + Statoids: STALE (119 communes
  / 18 provinces) — not used as roster oracles.
- ISO 3166-2:BI still lists the 18 former provinces — noted,
  bundled follows the live 2025 structure.
- No-system quadruple: UPU bdi profile example (BP 1323, no
  code) + WP List "no codes" + absent from UPU Aug-2022 type
  table + no BI.zip in GeoNames.

### Holds / gaps

- Province codes 01-05 are bundled convention (not ISO).
- No postal files ship (no-system); gate asserts their absence.

## Martinique (MQ) revisit — B8 verification (verify-only, no changes)

B8 revisit of the 4-district / 34-commune tree and 30-code
overlay against La Poste Hexasmal (current, via data.laposte.fr),
GeoNames MQ.txt, and the WP communes table. Verdict: verify-only
— zero data changes; `gate_mq.py` ALL PASS.

### Oracles fetched

- Hexasmal 972: 30 distinct codes; all 35 distinct legs match
  bundled exactly, incl. shared 97218 (Basse-Pointe/
  Grand-Rivière/Macouba), 97222 (Bellefontaine/Case-Pilote),
  97250 (Fonds-Saint-Denis/Le Prêcheur/Saint-Pierre).
- GeoNames MQ.txt (100 rows): 30 non-CEDEX codes exact; shared
  legs list the same communes.
- WP Communes of Martinique: 34/34 INSEE + names exact, zero
  diffs; its principal-code column is consistent with bundled.

### Holds / gaps

- Primaries on the 3 shared codes kept per the stability rule
  (Hexasmal defines no primary): Basse-Pointe, Bellefontaine,
  Saint-Pierre.
- CEDEX rows intentionally excluded.

## New Caledonia (NC) revisit — B8 verification (fix: Koné + Poya)

B8 revisit of the 3-province / 33-commune tree and 50-code
overlay against the OPT-NC Feb-2025 postcode table, GeoNames
NC.txt, and the UPU ncl profile. Verdict: fix-and-fill — 2
areas cells (Koné markup strip + Poya parent South->North);
codes verify-only; `gate_nc.py` ALL PASS.

### Oracles fetched

- OPT-NC "Codes postaux de NC (MAJ Février 2025)": all 50
  geographic codes map to the bundled communes, incl. 98880
  LA FOA vs 98881 FARINO, 98859/98860 KONE (domicile/BP),
  98809/98810 MONT DORE, 98832 VAO (Isle of Pines), 98840
  TONTOUTA (Païta), 98877 NEPOUI (Poya), 98820 WE + 98884/5
  Lifou BP codes, 98828 TADINE + 98878 LA ROCHE (Maré).
- GeoNames NC.txt (52 rows): 50 non-CEDEX codes, exact set;
  MAP? rows are all locality-in-commune except "Farino 98880"
  — a locality artefact (OPT assigns 98880 to LA FOA and
  gives Farino its own 98881; no second leg).
- UPU ncl profile: 5-digit 988xx system confirmed.
- Poya parent: WP Poya extract (largest part + main settlement
  + 2592/2802 inhabitants in Poya-Nord) + GeoNames Province
  Nord admin tag on both Poya rows → North.
- No ISO 3166-2:NC codes exist (FR-NC under France).

### Fixes applied

- `nc:commune:kone` name `'''Koné'''` -> `Koné` (wiki-markup
  leakage; WP title Koné).
- `nc:commune:poya` parent south-province -> north-province.
  Counts now North 17 / South 13 / Loyalty 3.

### Holds / gaps

- Poya-Sud's 210 inhabitants stay reachable via the commune
  (single-parent tree; majority-side convention noted).
- CEDEX rows (incl. 98845-98899 NOUMEA CEDEX) excluded.
# doc05.txt — French Polynesia revisit section for 05-country-data.md

Target: `packages/addressing/docs/05-country-data.md`, `## French Polynesia`
section (replace whole section).

```md
## French Polynesia

The bundled `FrenchPolynesiaGeographyProvider` supplies the 5
administrative subdivisions as `State` rows and a two-level
administrative hierarchy. It is selected with
`SeedCountryGeographiesAction::execute('PF')` after countries are seeded.
The 48 communes ship as level-2 areas under their subdivisions
(Marquesas 6, Tuamotu-Gambier 17, Austral 5, Leeward 7, Windward 13),
each carrying its INSEE commune code (98711–98758).

Addresses follow the French layout with the code left of the
locality (`98714 PAPEETE`), plus the island after the municipality
per the UPU PYF profile (`98709 MAHINA TAHITI`). Types are labelled
`Subdivision` and `Commune`.

Revisit 2026-10-03 (verify-only, `gate_pf.py` ALL PASS): the tree is
exact in four oracles — ISPF RP2022 legal-population roster (48/48
numbered 11–58, all top-level communes, none a commune associée),
the WP administrative-divisions table (48/48 names + INSEE + parents),
missionfranceguichet commune pages (48/48 INSEE + subdivision tags),
and Etalab geo.api.gouv.fr (INSEE COG noms). Display names use the
Tahitian-diacritic WP forms (Faʻaʻā, Pīraʻe, Puka-Puka, Punaʻauia);
INSEE COG/ISPF print ASCII (Faaa, Pirae, Pukapuka, Punaauia) — kept
on tie. Division codes 01–05 are build-local alphabetical numbers:
ISO 3166-2:PF defines no codes and INSEE exposes no subdivision
layer (geo.api.gouv.fr returns no arrondissement for PF), so the
GeoNames admin1 numbering (01 Vent … 05 Australes) is GeoNames-
internal and is not a contradiction.

All 83 postcodes and 93 legs verify clean in three bulk oracles:
GeoNames PF.zip (207 rows: 83/83 codes, 93/93 legs), the 48
missionfranceguichet commune pages (per-commune sets + sharing
notes), and Etalab (per-commune sets, union 83). The 10 secondary
legs each carry 3/3 signals and are KEPT: 98732→Maupiti (Huahine
primary), 98735→Taputapuatea + Tumaraa (Uturoa primary, subdivision
seat), 98790→Anaa + Fakarava + Hao + Hikueru + Makemo + Takaroa
(Rangiroa primary, largest holder), 98796→Hiva-Oa (Nuku-Hiva
primary, Marquesas seat). No oracle adjudicates primaries, so all
four shared-code primaries stay per the no-move-on-tie rule —
notably 98732/Huahine, where the OPT agency seat argues Maupiti
(own 98732 agency) but largest-holder argues Huahine.

Stale codes from the ~2012 OPT 81-agency listing stay excluded:
98702 Faaa-aéroport, 98713/98715 Papeete BP/messageries, the 98717
Punaauia annexe row, and 98791 Henuaparea/Taenga (now 98790 in
GeoNames) — none appears in any modern oracle. The UPU PYF profile
(08/2011) confirms the 987xx format and 4th-digit island-group
scheme. Label note: the 18-postal-overlays PF row cites "La Poste
Hexasmal", but the La Poste-derived commune-level source is Hexavia
(via Etalab); street-level Hexasmal coverage is not expected for
OPT-served PF — counts (83/93) are unaffected.
```
Revisit 2026-10-03 (fix-and-fill: +11 UPU-listed codes HT1131/HT1212/HT2333/HT3223/HT3311/HT3312/HT3341/HT4330/HT6112/HT6350/HT8316, zero tree changes, zero moves; `gate_ht.py` ALL PASS): tree verified — 10/10 ISO 3166-2:HT department codes, 42/42 arrondissement names + parents exact vs WP Arrondissements of Haiti (IHSI-sourced), geoBoundaries HTI-ADM2, Statoids Pc table and the UPU 42-district prefix table. HT-GA keeps the Haitian national spelling Grand'Anse (Statoids/Mapanet/WP agree; ISO 3166-2 alone spells it Grande'Anse). Codes are HT + 4 digits (first = department, first two = arrondissement, first three = commune; 75 split by 3rd digit: 752 Baradères vs 751/753/754 Anse-à-Veau). Three UPU-rooted directories agree set-identical on 235 codes across 2019→2026 (Parcelforce Sep19 list with dept/arrondissement/commune attribution, Mapanet live scrape, postcode.info live index); GeoNames HT.zip (230 codes) misses those 11 but adds 5 structurally valid sub-locality codes the UPU list lacks (Moreau Paye 4530, Thomassin/Fermathe/Pergnier 6145–6147, La Colline 8313 — all Krezicart-confirmed, all kept). Union 240, every code primary-linked to its prefix arrondissement; overlap arrondissement agreement 224/224 after the 752 Baradères keep (UPU district table lists 7520 BARADERES as its own district over the Parcelforce Anse-à-Veau grouping). HT3408 stays excluded (GeoNames-only, impossible 34 prefix, street-address place "Puits Blain 38", dept-digit/dept mismatch).

Per-arrondissement code counts (240 total): Acul-du-Nord 7, Anse-à-Veau 3, Anse d'Hainault 5, Aquin 7, Arcahaie 4, Bainet 2, Baradères 2, Belle-Anse 7, Borgne 6, Cap-Haïtien 8, Cerca-la-Source 4, Chardonnières 5, Corail 4, Côteaux 5, Croix-des-Bouquets 8, Dessalines 5, Fort-Liberté 6, Gonaïves 4, Grande-Rivière-du-Nord 2, Gros-Morne 4, Hinche 6, Jacmel 6, Jérémie 8, La Gonâve 2, Lascahobas 4, Léogâne 6, Les Cayes 8, Limbé 3, Marmelade 3, Miragoâne 5, Mirebalais 5, Môle-Saint-Nicolas 7, Ouanaminthe 3, Plaisance 3, Port-au-Prince 33, Port-de-Paix 7, Port-Salut 3, Saint-Louis-du-Nord 5, Saint-Marc 7, Saint-Raphaël 5, Trou-du-Nord 8, Vallières 5.

Holds/gaps: HT3408 excluded (see above); no other directory lists codes outside the 240 union (Krezicart 213 = bundled − Grand'Anse render gap + HT6112); IHSI 2015 PDF used via WP citation (live ihsi.ht domain squatted, Wayback PDF truncates); Marmelade holds 3 codes though the arrondissement has 2 communes (4530 Moreau Paye is a Saint-Michel sub-locality code, GN + Krezicart agree).

## Jersey (JE) revisit — B9 verification (verify-only, no changes)

B9 revisit of the 12-parish / 56-subdivision tree and 2-district
overlay against the WP Vingtaine master table (opendata.gov.je
census sourcing), parish articles, the JE postcode-area table,
and GeoNames JE.txt. Verdict: verify-only — zero data changes;
`gate_je.py` ALL PASS.

### Oracles fetched

- WP Vingtaine master table: all 56 vingtaines/cantons/
  cueillettes by parish, exact names + parents (Grouville 4,
  St Brelade 4, St Clement 3, St Helier 7, St John 3,
  St Lawrence 6, St Martin 5, St Mary 2, St Ouen 6,
  St Peter 5, St Saviour 6, Trinity 5).
- St Helier's 2 cantons confirmed as listed rows (electoral
  split of the Vingtaine de la Ville; kept as the finer
  modelled level).
- St Saviour's Grande Longueville confirmed real (parish
  Feb-2026 vingtenier minutes + parish polling list + Jersey
  electoral law); the WP parish article's 5-row table was an
  incomplete vingtenier-district view.
- JE postcode-area table: JE2 = St Helier (sectors 3-4) +
  St Clement (6) + St Saviour (7); JE3 = 9 rural parishes
  (sectors 1-9) — bundled legs exact.
- JE1 large-users + JE4 PO-boxes + JE5 bespoke delivery are
  non-geographic per Royal Mail/Jersey Post — correctly
  excluded. GeoNames JE.txt carries the same area-level rows.

### Holds / gaps

- Primaries kept per the stability rule: JE2 St Helier
  (capital + 2 sectors), JE3 Grouville (lowest parish code
  among 9 single-sector legs; no post-town distinction — all
  JERSEY).
- St spellings kept per the JE/GG in-repo island convention.
