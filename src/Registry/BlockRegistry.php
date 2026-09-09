<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Registry;

use Ewk\ContentBlocks\Contracts\BlockContract;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Exceptions\DuplicateBlockCodeException;
use Ewk\ContentBlocks\Exceptions\InvalidBlockClassException;
use Illuminate\Contracts\Container\Container;

final class BlockRegistry implements BlockRegistryInterface
{
    /** @var array<string, class-string<BlockContract>> */
    private array $blocks = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    public function register(string $blockClass): void
    {
        $blockClass = $this->validated($blockClass);
        $code = $blockClass::code();

        $existing = $this->blocks[$code] ?? null;

        if ($existing !== null && $existing !== $blockClass) {
            throw DuplicateBlockCodeException::forCode($code, $existing, $blockClass);
        }

        $this->blocks[$code] = $blockClass;
    }

    public function override(string $blockClass): void
    {
        $blockClass = $this->validated($blockClass);

        $this->blocks[$blockClass::code()] = $blockClass;
    }

    public function has(string $code): bool
    {
        return isset($this->blocks[$code]);
    }

    public function classFor(string $code): ?string
    {
        return $this->blocks[$code] ?? null;
    }

    public function make(string $code): ?BlockContract
    {
        $blockClass = $this->blocks[$code] ?? null;

        if ($blockClass === null) {
            return null;
        }

        $block = $this->container->make($blockClass);
        \assert($block instanceof BlockContract);

        return $block;
    }

    public function all(): array
    {
        return $this->blocks;
    }

    public function availableFor(?string $scope): array
    {
        $available = [];

        foreach ($this->instances() as $code => $block) {
            if ($this->isAvailableFor($block, $scope)) {
                $available[$code] = $this->blocks[$code];
            }
        }

        return $available;
    }

    public function options(): array
    {
        return $this->optionsOf($this->instances());
    }

    public function optionsFor(?string $scope): array
    {
        $available = [];

        foreach ($this->instances() as $code => $block) {
            if ($this->isAvailableFor($block, $scope)) {
                $available[$code] = $block;
            }
        }

        return $this->optionsOf($available);
    }

    private function isAvailableFor(BlockContract $block, ?string $scope): bool
    {
        $scopes = $block->scopes();

        if ($scopes === []) {
            return true;
        }

        return $scope !== null && \in_array($scope, $scopes, true);
    }

    /**
     * @param array<string, BlockContract> $blocks
     *
     * @return array<string, string|array<string, string>>
     */
    private function optionsOf(array $blocks): array
    {
        $options = [];

        foreach ($blocks as $code => $block) {
            $category = $block->category();

            if ($category === null) {
                $options[$code] = $block->title();

                continue;
            }

            $group = $options[$category] ?? [];
            \assert(\is_array($group));

            $group[$code] = $block->title();
            $options[$category] = $group;
        }

        return $options;
    }

    /**
     * @return array<string, BlockContract>
     */
    private function instances(): array
    {
        $instances = [];

        foreach (array_keys($this->blocks) as $code) {
            $block = $this->make($code);

            if ($block !== null) {
                $instances[$code] = $block;
            }
        }

        return $instances;
    }

    /**
     * @param class-string $blockClass
     *
     * @return class-string<BlockContract>
     */
    private function validated(string $blockClass): string
    {
        if (! is_subclass_of($blockClass, BlockContract::class)) {
            throw InvalidBlockClassException::forClass($blockClass);
        }

        return $blockClass;
    }
}
