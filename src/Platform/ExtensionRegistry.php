<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Platform;

use Illuminate\Contracts\Container\Container;
use RuntimeException;

final class ExtensionRegistry
{
    /** @param array<string, list<class-string>> $slots */
    public function __construct(
        private readonly Container $container,
        private readonly array $slots,
    ) {}

    /** @return list<class-string> */
    public function classesFor(string $slot): array
    {
        return $this->slots[$slot] ?? [];
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>|null  $expectedInterface
     * @return list<T|object>
     */
    public function resolve(string $slot, ?string $expectedInterface = null): array
    {
        $resolved = [];
        foreach ($this->classesFor($slot) as $class) {
            $instance = $this->container->make($class);
            if ($expectedInterface !== null && ! $instance instanceof $expectedInterface) {
                throw new RuntimeException("Extension {$class} in slot '{$slot}' must implement {$expectedInterface}.");
            }
            $resolved[] = $instance;
        }

        return $resolved;
    }
}
