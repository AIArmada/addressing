# Addressing Data Resources

`countries.php` is the only bundled dataset. It contains ISO 3166-1 country/territory address entities, not only sovereign countries.

`countries.audit.json` is included in this instruction pack to make review and comparison easier. It should include `entity_type` and nullable `is_independent` for every record.

Do not add states, cities, districts, postcodes or other locality datasets to this directory in the core package. Use `AddressAreaSource` imports instead.
