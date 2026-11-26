---
name: addressing-development
description: Develop Addressing integrations using the core addressing feature boundaries and Laravel Dune architecture.
---

# Addressing development

- Resolve formatting, validation, normalization, navigation links and lookup through the contracts in `AIArmada\Addressing\Contracts`.
- Keep geography providers declarative: return CountryStateSeedData and area metadata; do not write database rows from a provider.
- Use CountryGeographySeeder and AddressingDatasetSeeder to orchestrate the reference imports.
- Use AddressWriter, AddressAreaWriter, PostalCodeWriter and AddressSnapshotWriter for mutations; they return persisted IDs. Pass null as AddressWriter's assignment map to preserve existing compatible assignments, or an explicit map to replace them. Pass assignment metadata (for example `['source' => 'filament-addressing']`) when the caller owns the provenance.
- Manual postcode coverage is owned by `source = manual`; imports replace only the `(source, source_id)` scopes present in their payload and reject retained-scope conflicts instead of overwriting another source's edge.
- Google Maps link resolution follows only the documented Maps, shortener, and consent hosts; unsupported regional domains fail closed and server lookups are skipped without a configured API key.
- Attach, update, detach and select primary addresses through AddressAttachmentWriter. Pass persisted identifiers; revalidate owner scope on every mutation.
- AddressAreaSearch returns a list of immutable AddressAreaMatchData values, not Eloquent models.
- Report unresolved values through ResolutionGapReporter::report(), which returns a persisted gap ID. Use ResolutionGapCuration to match or ignore that ID; do not write gap records from adapters.
- Keep external HTTP requests in Drivers. Do not call Google or OneMap directly from Filament components.
- Use immutable AddressData for formatting, validation and navigation. Do not duplicate the normalization rules in consuming packages.

```php
use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Contracts\AddressFormatter;
use AIArmada\Addressing\Contracts\AddressValidator;
use AIArmada\Addressing\Contracts\AddressNavigationLinks;

$address = app(AddressNormalizer::class)->normalize($input);
$errors = app(AddressValidator::class)->validate($address);
$formatted = app(AddressFormatter::class)->format($address);
$links = app(AddressNavigationLinks::class)->build($address);
```

## Verification

From the commerce monorepo root:

```sh
composer --working-dir=packages/addressing arch:check
./vendor/bin/pint --test packages/addressing
./vendor/bin/pest tests/src/Addressing
```

Do not add compatibility aliases, migration backfills, host application imports, or inline static-analysis suppressions.
