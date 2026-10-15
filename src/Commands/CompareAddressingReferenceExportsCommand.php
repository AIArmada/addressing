<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\CompareAddressingReferenceExportsAction;
use Illuminate\Console\Command;
use Throwable;

final class CompareAddressingReferenceExportsCommand extends Command
{
    protected $signature = 'address:reference:compare {before : Earlier NDJSON export} {after : Later NDJSON export}';

    protected $description = 'Compare two addressing reference exports offline';

    public function handle(CompareAddressingReferenceExportsAction $action): int
    {
        try {
            $result = $action->execute((string) $this->argument('before'), (string) $this->argument('after'));
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $rows = [];

        foreach ($result['counts'] as $status => $types) {
            foreach ($types as $type => $count) {
                if ($count > 0) {
                    $rows[] = [$status, $type, $count];
                }
            }
        }

        $this->table(['Review status', 'Reference type', 'Rows'], $rows);

        if ($result['examples'] !== []) {
            $this->newLine();
            $this->line('Review examples (up to 100):');

            foreach ($result['examples'] as $example) {
                $this->line(json_encode($example, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'Could not encode a review example.');
            }
        }

        $differences = array_sum($result['counts']['added'])
            + array_sum($result['counts']['ambiguous'])
            + array_sum($result['counts']['changed'])
            + array_sum($result['counts']['missing'])
            + array_sum($result['counts']['scope_unknown']);

        if ($differences > 0) {
            $this->components->warn('Differences require human review. Missing records outside the other export scope are reported as scope_unknown. Nothing was applied.');

            return self::FAILURE;
        }

        $this->components->info('Exports contain the same reference records in the compared country scopes.');

        return self::SUCCESS;
    }
}
