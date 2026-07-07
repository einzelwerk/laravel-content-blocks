<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Contracts;

use Ewk\ContentBlocks\Exceptions\DuplicateBlockCodeException;
use Ewk\ContentBlocks\Exceptions\InvalidBlockClassException;

interface BlockRegistryInterface
{
    /**
     * Register a block class. Registering a different class under an
     * already-taken code throws — use {@see override()} to replace a block
     * deliberately.
     *
     * @param class-string $blockClass
     *
     * @throws InvalidBlockClassException
     * @throws DuplicateBlockCodeException
     */
    public function register(string $blockClass): void;

    /**
     * Register a block class, replacing any block previously registered
     * under the same code.
     *
     * @param class-string $blockClass
     *
     * @throws InvalidBlockClassException
     */
    public function override(string $blockClass): void;

    public function has(string $code): bool;

    /**
     * @return class-string<BlockContract>|null
     */
    public function classFor(string $code): ?string;

    /**
     * Resolve a block instance through the container, or null for an
     * unknown code.
     */
    public function make(string $code): ?BlockContract;

    /**
     * @return array<string, class-string<BlockContract>> code => class
     */
    public function all(): array;

    /**
     * @return array<string, string> code => title, for admin selectors
     */
    public function options(): array;
}
