<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Platform;

use RuntimeException;

final class FeatureCatalog
{
    /** @var array<string, array{features: array<string, string>, owners: array<string, string>, writes: array<string, list<string>>, extensions: array<string, list<string>>}> */
    private static array $memory = [];

    public static function flushMemory(): void
    {
        self::$memory = [];
    }

    /**
     * @return array{features: array<string, string>, owners: array<string, string>, writes: array<string, list<string>>, extensions: array<string, list<string>>}
     */
    public static function load(string $root, string $packageNamespace, string $sourceDir = 'src'): array
    {
        $key = mb_rtrim($root, '/') . '|' . mb_trim($packageNamespace, '\\') . '|' . mb_trim($sourceDir, '/');
        if (isset(self::$memory[$key])) {
            return self::$memory[$key];
        }

        return self::$memory[$key] = self::scan($root, $packageNamespace, $sourceDir);
    }

    /**
     * @return array{features: array<string, string>, owners: array<string, string>, writes: array<string, list<string>>, extensions: array<string, list<string>>}
     */
    public static function scan(string $root, string $packageNamespace, string $sourceDir = 'src'): array
    {
        $root = mb_rtrim($root, '/');
        $pkgNs = mb_trim($packageNamespace, '\\');
        $sourceDir = mb_trim($sourceDir, '/');

        $features = [];
        $owners = [];
        $writes = [];
        $extensions = [];

        $paths = glob($root . '/' . $sourceDir . '/Features/*', GLOB_ONLYDIR) ?: [];
        sort($paths, SORT_STRING);

        foreach ($paths as $folder) {
            $name = basename($folder);
            $manifestPath = $folder . '/feature.json';

            if (! is_file($manifestPath)) {
                throw new RuntimeException("Missing feature manifest: {$manifestPath}.");
            }

            $data = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            if (! preg_match('/^[A-Z][A-Za-z0-9]*$/D', $name)
                || ($data['schema'] ?? null) !== 1
                || ($data['name'] ?? null) !== $name
                || ! is_array($data['owns'] ?? null)) {
                throw new RuntimeException("Invalid package feature manifest: {$manifestPath}.");
            }

            $features[$name] = $folder;
            $realFolder = realpath($folder);
            $prefix = $pkgNs . "\\Features\\{$name}\\";

            foreach ($data['owns'] as $resource => $classes) {
                if (! is_string($resource)
                    || ! preg_match('/^(table|store):[A-Za-z0-9_.:-]+$/D', $resource)
                    || ! is_array($classes)
                    || ! array_is_list($classes)
                    || $classes === []
                    || count($classes) !== count(array_unique($classes, SORT_REGULAR))) {
                    throw new RuntimeException("{$manifestPath}: resource '{$resource}' requires a non-empty unique list of local Actions/Persistence FQCNs.");
                }

                if (isset($owners[$resource])) {
                    throw new RuntimeException("Duplicate owner: '{$resource}' belongs to {$owners[$resource]}, also claimed by {$name}.");
                }

                $owners[$resource] = $name;

                foreach ($classes as $class) {
                    if (! is_string($class)
                        || ! str_starts_with($class, $prefix)
                        || ! preg_match('/^(Actions|Persistence)\\\\[A-Za-z_][A-Za-z0-9_\\\\]*$/D', mb_substr($class, mb_strlen($prefix)))) {
                        throw new RuntimeException("{$manifestPath}: write class '{$class}' must live under {$prefix}Actions\\ or Persistence\\.");
                    }

                    $relClass = str_replace('\\', '/', mb_substr($class, mb_strlen($pkgNs) + 1));
                    $file = $root . '/' . $sourceDir . '/' . $relClass . '.php';
                    $realFile = realpath($file);
                    if ($realFile === false || $realFolder === false || ! str_starts_with($realFile, $realFolder . DIRECTORY_SEPARATOR)) {
                        throw new RuntimeException("{$manifestPath}: write class '{$class}' not found at {$file}.");
                    }

                    $writes[$class][] = $resource;
                }
            }

            if (isset($data['extensions']) && is_array($data['extensions'])) {
                foreach ($data['extensions'] as $slot => $extClasses) {
                    if (! is_string($slot) || ! preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $slot) || ! is_array($extClasses) || ! array_is_list($extClasses)) {
                        throw new RuntimeException("{$manifestPath}: invalid extension slot '{$slot}'.");
                    }
                    foreach ($extClasses as $extClass) {
                        if (! is_string($extClass)
                            || ! str_starts_with($extClass, $prefix)
                            || ! preg_match('/^Extensions\\\\[A-Za-z_][A-Za-z0-9_\\\\]*$/D', mb_substr($extClass, mb_strlen($prefix)))) {
                            throw new RuntimeException("{$manifestPath}: extension '{$extClass}' must live under {$prefix}Extensions\\.");
                        }
                        $relExt = str_replace('\\', '/', mb_substr($extClass, mb_strlen($pkgNs) + 1));
                        $extFile = $root . '/' . $sourceDir . '/' . $relExt . '.php';
                        $realExt = realpath($extFile);
                        if ($realExt === false || $realFolder === false || ! str_starts_with($realExt, $realFolder . DIRECTORY_SEPARATOR)) {
                            throw new RuntimeException("{$manifestPath}: extension '{$extClass}' not found at {$extFile}.");
                        }
                        $extensions[$slot][] = $extClass;
                    }
                }
            }
        }

        ksort($extensions, SORT_STRING);

        return compact('features', 'owners', 'writes', 'extensions');
    }
}
