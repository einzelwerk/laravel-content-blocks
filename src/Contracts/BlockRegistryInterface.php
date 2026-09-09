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
     * All registered blocks, keyed by code.
     *
     * @return array<string, class-string<BlockContract>>
     */
    public function all(): array;

    /**
     * Blocks that may be attached to an owner with the given scope: the
     * unscoped ones plus those listing the scope. A null scope (an owner
     * without {@see ScopedBlockOwnerContract}) yields the unscoped blocks.
     *
     * @return array<string, class-string<BlockContract>>
     */
    public function availableFor(?string $scope): array;

    /**
     * Selector options of all registered blocks: `code => title`, with the
     * blocks of a category nested under its label (`category => [code => title]`)
     * in registration order.
     *
     * @return array<string, string|array<string, string>>
     */
    public function options(): array;

    /**
     * Selector options limited to {@see availableFor()} the scope.
     *
     * @return array<string, string|array<string, string>>
     */
    public function optionsFor(?string $scope): array;
}
