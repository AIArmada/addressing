<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Models\AddressCountry;

interface CountryAddressAreaMetadataProvider
{
    /** @return array<string, list<array{role: string, country_code?: string, is_primary?: bool}>> */
    public function areaRoles(AddressCountry $country): array;

    /** @return array<string, list<array{name: string, name_type?: string, is_preferred?: bool}>> */
    public function areaNames(AddressCountry $country): array;

    /** @return array<string, list<array{parent_source_id: string, relationship_type: string, hierarchy_type: string}>> */
    public function areaRelationships(AddressCountry $country): array;
}
