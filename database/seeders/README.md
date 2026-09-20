# Seeders

- `AddressCountrySeeder.php` seeds the bundled ISO 3166-1 countries via `SeedAddressCountriesAction`. It must not duplicate country import logic.
- `MalaysiaPostalCodeSeeder.php` seeds Malaysia's postcode dataset from the bundled `malaysia-postal-codes.csv` and `malaysia-postal-code-areas.csv` files.
