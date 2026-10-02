<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

/**
 * Supplies display labels for a country's area types.
 *
 * Labels narrow to the types present in scope: a Johor district selector
 * reads District, a Putrajaya locality selector reads Precinct, and mixed
 * scopes keep a combined label. Only states whose proper term differs
 * from the base need overrides (Kelantan calls districts Jajahan).
 *
 * Bundled providers label every supported hierarchy type explicitly, even
 * when the label matches the headline fallback. The contract stays optional
 * for third-party providers: undeclared types fall back to headline rendering.
 */
interface CountryAreaTypeLabelProvider
{
    /**
     * Country-wide display labels per area type.
     *
     * Bundled providers declare every hierarchy type. Third-party providers
     * may declare a subset; types without a declared label fall back to a
     * headline rendering (`minor_district` becomes `Minor District`).
     *
     * @return array<string, string>
     */
    public function areaTypeLabels(): array;

    /**
     * Per-state display labels per area type.
     *
     * A list (not a code-keyed map) because PHP casts zero-padded numeric
     * codes such as '03' to int keys.
     *
     * @return list<array{state_code: string, type_labels: array<string, string>}>
     */
    public function stateAreaTypeLabels(): array;
}
