<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use Generator;
use InvalidArgumentException;
use RuntimeException;

/** @internal */
final class ReferenceExportFile
{
    /** @return array<string, mixed> */
    public function header(string $path): array
    {
        $stream = $this->open($path);

        try {
            $line = fgets($stream);

            if ($line === false) {
                throw new InvalidArgumentException(sprintf('Reference export [%s] is empty.', $path));
            }

            $header = ReferenceExportContract::decode(mb_rtrim($line, "\r\n"), 1);
            ReferenceExportContract::validateHeader($header);

            if (ReferenceExportContract::encode($header) !== mb_rtrim($line, "\r\n")) {
                throw new InvalidArgumentException('Reference export header is not canonically encoded.');
            }

            return $header;
        } finally {
            fclose($stream);
        }
    }

    /** @return Generator<array<string, mixed>> */
    public function records(string $path): Generator
    {
        $stream = $this->open($path);
        $lineNumber = 0;
        $counts = array_fill_keys(ReferenceExportContract::TYPES, 0);
        $digest = hash_init('sha256');
        $lastSortKey = null;

        try {
            $headerLine = fgets($stream);
            $lineNumber++;

            if ($headerLine === false) {
                throw new InvalidArgumentException(sprintf('Reference export [%s] is empty.', $path));
            }

            $header = ReferenceExportContract::decode(mb_rtrim($headerLine, "\r\n"), $lineNumber);
            ReferenceExportContract::validateHeader($header);

            while (($line = fgets($stream)) !== false) {
                $lineNumber++;
                $decoded = ReferenceExportContract::decode(mb_rtrim($line, "\r\n"), $lineNumber);

                if (($decoded['kind'] ?? null) === 'footer') {
                    $this->validateFooter($decoded, $counts, hash_final($digest), $lineNumber);

                    if (fgets($stream) !== false) {
                        throw new InvalidArgumentException(sprintf('Reference export [%s] has content after its footer.', $path));
                    }

                    return;
                }

                ReferenceExportContract::validateRecord($decoded, $lineNumber);
                $sortKey = ReferenceExportContract::sortKey($decoded['type'], $decoded['key']);

                if ($lastSortKey !== null && strcmp($sortKey, $lastSortKey) < 0) {
                    throw new InvalidArgumentException(sprintf('Reference export [%s] has unsorted identities on line %d.', $path, $lineNumber));
                }

                $lastSortKey = $sortKey;
                $canonicalLine = ReferenceExportContract::encode($decoded);

                if ($canonicalLine !== mb_rtrim($line, "\r\n")) {
                    throw new InvalidArgumentException(sprintf('Reference export line %d is not canonically encoded.', $lineNumber));
                }

                $counts[$decoded['type']]++;
                hash_update($digest, $canonicalLine . "\n");
                yield $decoded;
            }

            throw new InvalidArgumentException(sprintf('Reference export [%s] has no footer.', $path));
        } finally {
            fclose($stream);
        }
    }

    /** @param array<string, int> $counts @param array<string, mixed> $footer */
    private function validateFooter(array $footer, array $counts, string $digest, int $lineNumber): void
    {
        $expectedKeys = ['counts', 'kind', 'records_sha256'];
        $actualKeys = array_keys($footer);
        sort($actualKeys, SORT_STRING);
        sort($expectedKeys, SORT_STRING);

        if ($actualKeys !== $expectedKeys || $footer['kind'] !== 'footer' || ! is_array($footer['counts']) || ! is_string($footer['records_sha256'])) {
            throw new InvalidArgumentException(sprintf('Reference export footer on line %d is invalid.', $lineNumber));
        }

        if (ReferenceExportContract::encode($footer['counts']) !== ReferenceExportContract::encode($counts)) {
            throw new InvalidArgumentException(sprintf('Reference export record counts do not match the footer on line %d.', $lineNumber));
        }

        if (! hash_equals($digest, $footer['records_sha256'])) {
            throw new InvalidArgumentException(sprintf('Reference export digest does not match the footer on line %d.', $lineNumber));
        }
    }

    /** @return resource */
    private function open(string $path): mixed
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new RuntimeException(sprintf('Could not open reference export [%s].', $path));
        }

        return $stream;
    }
}
