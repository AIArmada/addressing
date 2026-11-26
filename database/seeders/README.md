# Seeders

- `AddressingSeeder.php` seeds the full dataset via `AddressingDatasetSeeder`: countries, currency/timezone references, states, cities, every configured country geography, then every bundled postcode dataset.
- `AddressCountrySeeder.php` seeds the bundled ISO 3166-1 countries via `CountryDatasetSeeder`. It must not duplicate country import logic.
- `PostalCodeSeeder.php` seeds every bundled postcode dataset via `PostalCodeSeeder`. It must not duplicate postal import logic.
