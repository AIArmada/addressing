<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

final class ConsoleSeedProgress
{
    /**
     * Build a progress callback that renders one console progress bar per
     * labelled phase. Returns null when no output is available so callers
     * can pass the result straight into seeding actions.
     *
     * @return ?callable(string, int, int, ?string=): void
     */
    public static function for(?OutputInterface $output): ?callable
    {
        if (! $output instanceof OutputStyle) {
            return null;
        }

        $bars = [];
        $current = null;

        return function (string $phase, int $done, int $total, ?string $label = null) use ($output, &$bars, &$current): void {
            $key = $label ?? $phase;

            if ($current !== null && $current !== $key && isset($bars[$current])) {
                $bars[$current]->finish();
                $output->writeln('');
                unset($bars[$current]);
            }

            $current = $key;

            // Indeterminate totals (streamed phases with unknown size): grow a
            // single bar instead of finishing a fresh 1-step bar per chunk.
            // A (0, 0) call finishes the bar via the normal path below.
            if ($total === 0 && $done > 0) {
                if (! isset($bars[$key])) {
                    $bars[$key] = $output->createProgressBar($done + 1);
                    $bars[$key]->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
                    $bars[$key]->setMessage($key);
                    $bars[$key]->start();
                } else {
                    $bars[$key]->setMaxSteps($done + 1);
                }

                $bars[$key]->setProgress($done);

                return;
            }

            if (! isset($bars[$key])) {
                $bars[$key] = $output->createProgressBar(max($total, 1));
                $bars[$key]->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%%");
                $bars[$key]->setMessage($key);
                $bars[$key]->start();
            }

            $bars[$key]->setProgress(min($done, $total));

            if ($done >= $total) {
                $bars[$key]->finish();
                $output->writeln('');
                unset($bars[$key]);
                $current = null;
            }
        };
    }
}
