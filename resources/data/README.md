# Addressing Data Resources

`countries.json` is the primary bundled dataset (ISO 3166-1 country/territory address entities). `states.json` and `cities.json` are bundled for address geography.

Source: [nnjeim/world](https://github.com/nnjeim/world) — the country, state, and city JSON files are copied from the source. Seed actions assign UUID PKs at runtime (int IDs from source are stripped). Shared currency, language, and timezone data is provided by `commerce-support`.

Curated deltas in `states.json` (2026-09-20, both upstreams stale): Burundi
18 → 5 provinces (July 2025 reform; codes 01-05 provisional pending ISO
3166-2:BI), Estonia dropped Toila (merged into Jõhvi 2025). Burkina Faso
keeps the pre-2025 list — the 2 new province names and new ISO codes are
unavailable in accessible sources.

Do not add districts, postcodes or other locality datasets to this directory in the core package. Use `AddressAreaSource` imports instead.
