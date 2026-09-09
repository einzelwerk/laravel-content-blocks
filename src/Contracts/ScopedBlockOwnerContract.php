<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Contracts;

/**
 * An owner that narrows which blocks may be attached to it. The scope is
 * matched against {@see BlockContract::scopes()}: a block declaring scopes
 * is offered only to owners whose scope is listed, a block without scopes
 * is offered to every owner. Owners that do not implement this contract
 * get the unscoped blocks only.
 *
 * Typical use: a page model whose `type` column distinguishes plain pages
 * from templates rendered in the context of another entity.
 */
interface ScopedBlockOwnerContract extends HasContentBlocksContract
{
    public function blockScope(): ?string;
}
