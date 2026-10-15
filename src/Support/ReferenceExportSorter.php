<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Generator;
use RuntimeException;
use SplPriorityQueue;
use Throwable;

/** @internal */
final class ReferenceExportSorter
{
    private const CHUNK_SIZE = 5_000;

    private const MERGE_FAN_IN = 64;

    /** @param iterable<array<string, mixed>> $records @return Generator<array<string, mixed>> */
    public function sort(iterable $records, string $temporaryDirectory): Generator
    {
        $buffer = [];
        $runs = [];

        try {
            foreach ($records as $record) {
                $buffer[] = $record;

                if (count($buffer) >= self::CHUNK_SIZE) {
                    $runs[] = $this->writeRun($buffer, $temporaryDirectory);
                    $buffer = [];
                }
            }

            if ($buffer !== []) {
                $runs[] = $this->writeRun($buffer, $temporaryDirectory);
            }

            while (count($runs) > self::MERGE_FAN_IN) {
                $nextRuns = [];

                try {
                    foreach (array_chunk($runs, self::MERGE_FAN_IN) as $group) {
                        $nextRuns[] = $this->mergeToRun($group, $temporaryDirectory);
                    }
                } catch (Throwable $exception) {
                    foreach ($nextRuns as $run) {
                        @unlink($run);
                    }

                    throw $exception;
                }

                foreach ($runs as $run) {
                    @unlink($run);
                }

                $runs = $nextRuns;
            }

            yield from $this->merge($runs);
        } finally {
            foreach ($runs as $run) {
                @unlink($run);
            }
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function writeRun(array $records, string $directory): string
    {
        usort($records, static fn (array $left, array $right): int => strcmp(
            ReferenceExportContract::sortKey($left['type'], $left['key']),
            ReferenceExportContract::sortKey($right['type'], $right['key']),
        ));

        $path = tempnam($directory, '.addressing-reference-sort-');

        if ($path === false) {
            throw new RuntimeException('Could not create a private reference export sort run.');
        }

        chmod($path, 0600);
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            @unlink($path);

            throw new RuntimeException('Could not open a reference export sort run.');
        }

        try {
            foreach ($records as $record) {
                $this->writeAll($stream, ReferenceExportContract::encode($record) . "\n");
            }
        } catch (Throwable $exception) {
            fclose($stream);
            @unlink($path);

            throw $exception;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    /** @param list<string> $runs */
    private function mergeToRun(array $runs, string $directory): string
    {
        $path = tempnam($directory, '.addressing-reference-sort-');

        if ($path === false) {
            throw new RuntimeException('Could not create an intermediate reference export sort run.');
        }

        chmod($path, 0600);
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            @unlink($path);

            throw new RuntimeException('Could not open an intermediate reference export sort run.');
        }

        try {
            foreach ($this->merge($runs) as $record) {
                $this->writeAll($stream, ReferenceExportContract::encode($record) . "\n");
            }
        } catch (Throwable $exception) {
            fclose($stream);
            @unlink($path);

            throw $exception;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    /** @param list<string> $runs @return Generator<array<string, mixed>> */
    private function merge(array $runs): Generator
    {
        if ($runs === []) {
            return;
        }

        $streams = [];
        $queue = new SplPriorityQueue;
        $queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);

        try {
            foreach ($runs as $index => $path) {
                $stream = fopen($path, 'rb');

                if ($stream === false) {
                    throw new RuntimeException('Could not read a reference export sort run.');
                }

                $streams[$index] = $stream;
                $this->enqueueNext($queue, $stream, $index);
            }

            while (! $queue->isEmpty()) {
                /** @var array{record: array<string, mixed>, run: int, sort_key: string} $item */
                $item = $queue->extract();
                yield $item['record'];
                $this->enqueueNext($queue, $streams[$item['run']], $item['run']);
            }
        } finally {
            foreach ($streams as $stream) {
                fclose($stream);
            }
        }
    }

    /** @param SplPriorityQueue<array{record: array<string, mixed>, run: int, sort_key: string}, string> $queue */
    private function enqueueNext(SplPriorityQueue $queue, mixed $stream, int $run): void
    {
        $line = fgets($stream);

        if ($line === false) {
            return;
        }

        $record = ReferenceExportContract::decode(mb_rtrim($line, "\r\n"), 0);
        $sortKey = ReferenceExportContract::sortKey((string) $record['type'], $record['key']);
        $queue->insert(['record' => $record, 'run' => $run, 'sort_key' => $sortKey], $this->reverse($sortKey));
    }

    private function reverse(string $value): string
    {
        $reversed = '';

        for ($index = 0, $length = mb_strlen($value); $index < $length; $index++) {
            $reversed .= chr(255 - ord($value[$index]));
        }

        return $reversed;
    }

    /** @param resource $stream */
    private function writeAll(mixed $stream, string $value): void
    {
        while ($value !== '') {
            $written = fwrite($stream, $value);

            if ($written === false || $written === 0) {
                throw new RuntimeException('Could not write a reference export sort run.');
            }

            $value = mb_substr($value, $written);
        }
    }
}
