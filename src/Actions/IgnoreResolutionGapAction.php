<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\ResolutionGap;

final class IgnoreResolutionGapAction
{
    /**
     * Runs inside the caller's owner scope; model guards reject writes
     * from any other context.
     */
    public function execute(ResolutionGap $gap): ResolutionGap
    {
        if ($gap->status === 'ignored') {
            return $gap;
        }

        $gap->status = 'ignored';
        $gap->save();

        return $gap;
    }
}
