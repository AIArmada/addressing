# Addressing Data Resources

`countries.json` is the primary bundled dataset (ISO 3166-1 country/territory address entities). `states.json` and `cities.json` are bundled for address geography.

Source: [nnjeim/world](https://github.com/nnjeim/world) — the country, state, and city JSON files are copied from the source. Seed actions assign UUID PKs at runtime (int IDs from source are stripped). Shared currency, language, and timezone data is provided by `commerce-support`.

Do not add districts, postcodes or other locality datasets to this directory in the core package. Use `AddressAreaSource` imports instead.
