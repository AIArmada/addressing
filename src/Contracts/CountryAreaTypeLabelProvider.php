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
 */
interface CountryAreaTypeLabelProvider
{
    /**
     * Country-wide display labels per area type.
     *
     * Types without a declared label fall back to a headline rendering
     * (`minor_district` becomes `Minor District`), so only declare types
     * whose proper term differs.
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
