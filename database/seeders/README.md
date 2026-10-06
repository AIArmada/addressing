# Seeders

- `AddressingSeeder.php` seeds the full dataset via `SeedAddressingAction`: countries, currency/timezone references, states, cities, every configured country geography, then every bundled postcode dataset.
- `AddressCountrySeeder.php` seeds the bundled ISO 3166-1 countries via `SeedAddressCountriesAction`. It must not duplicate country import logic.
- `PostalCodeSeeder.php` seeds every bundled postcode dataset via `SeedPostalCodesAction`. It must not duplicate postal import logic.
