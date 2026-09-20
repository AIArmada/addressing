<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Commands;

use AIArmada\Addressing\Actions\ExportResolutionGapAliasesAction;
use AIArmada\Addressing\Models\ResolutionGap;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ReportResolutionGapsCommand extends Command
{
    protected $signature = 'address:resolution-gaps
        {--country= : Only show gaps for a two-letter country code (e.g. ID).}
        {--days=30 : Only show gaps seen within the last N days.}
        {--reason= : Only show gaps with the given reason (unmatched, ambiguous).}
        {--status= : Only show gaps with the given status (open, matched, ignored).}
        {--limit=20 : Maximum number of gaps to list.}';

    protected $description = 'Report address resolution gaps ordered by hits';

    public function handle(ExportResolutionGapAliasesAction $export): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('The --days option must be a positive integer.');

            return self::FAILURE;
        }

        $reason = $this->option('reason');
        $reason = is_string($reason) && mb_trim($reason) !== '' ? mb_strtolower(mb_trim($reason)) : null;

        if ($reason !== null && ! in_array($reason, ['unmatched', 'ambiguous'], true)) {
            $this->error('The --reason option must be one of: unmatched, ambiguous.');

            return self::FAILURE;
        }

        $status = $this->option('status');
        $status = is_string($status) && mb_trim($status) !== '' ? mb_strtolower(mb_trim($status)) : null;

        if ($status !== null && ! in_array($status, ['open', 'matched', 'ignored'], true)) {
            $this->error('The --status option must be one of: open, matched, ignored.');

            return self::FAILURE;
        }

        $country = $this->option('country');
        $country = is_string($country) && mb_trim($country) !== '' ? mb_strtoupper(mb_trim($country)) : null;

        $limit = max(1, (int) $this->option('limit'));

        $gaps = ResolutionGap::query()
            ->when($country !== null, fn ($query) => $query->where('country_code', $country))
            ->when($reason !== null, fn ($query) => $query->where('reason', $reason))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->where('last_seen_at', '>=', CarbonImmutable::now()->subDays($days))
            ->orderByDesc('hits')
            ->orderByDesc('last_seen_at')
            ->limit($limit)
            ->get(['source', 'country_code', 'role', 'value', 'reason', 'status', 'hits', 'last_seen_at']);

        if ($gaps->isEmpty()) {
            $this->info('No resolution gaps found.');
        } else {
            $this->table(
                ['Value', 'Country', 'Role', 'Reason', 'Status', 'Hits', 'Last seen'],
                $gaps->map(static fn (ResolutionGap $gap): array => [
                    (string) $gap->value,
                    (string) $gap->country_code,
                    (string) $gap->role,
                    (string) $gap->reason,
                    (string) $gap->status,
                    (int) $gap->hits,
                    $gap->last_seen_at?->toDateTimeString() ?? '—',
                ])->all(),
            );
        }

        $this->line(sprintf(
            'Matched but not yet in providers: %d',
            $this->promotionBacklog($export, $country),
        ));

        return self::SUCCESS;
    }

    private function promotionBacklog(ExportResolutionGapAliasesAction $export, ?string $country): int
    {
        $countries = $country !== null
            ? [$country]
            : ResolutionGap::query()
                ->where('status', 'matched')
                ->distinct()
                ->pluck('country_code')
                ->map(static fn (mixed $code): string => mb_strtoupper(mb_trim((string) $code)))
                ->all();

        $backlog = 0;

        foreach ($countries as $countryCode) {
            try {
                $backlog += $export->execute($countryCode)->candidateCount();
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $backlog;
    }
}
