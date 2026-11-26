<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\ResolutionGaps;

use AIArmada\Addressing\Contracts\ResolutionGapCuration;
use AIArmada\Addressing\Contracts\ResolutionGapReporter;
use AIArmada\Addressing\Features\ResolutionGaps\Actions\CurateResolutionGapAction;
use AIArmada\Addressing\Features\ResolutionGaps\Actions\ReportResolutionGapAction;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->singleton(ResolutionGapReporter::class, ReportResolutionGapAction::class);
        $app->singleton(ResolutionGapCuration::class, CurateResolutionGapAction::class);
    }

    public function boot(Application $app): void {}
}
