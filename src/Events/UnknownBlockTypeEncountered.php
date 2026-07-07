<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Events;

use Ewk\ContentBlocks\Models\ContentBlock;

/**
 * Dispatched when a stored block references a type absent from the registry
 * (e.g. a block class was removed while rows remain). The renderer skips
 * such blocks; listen to this event to log or clean them up.
 */
final readonly class UnknownBlockTypeEncountered
{
    public function __construct(
        public ContentBlock $block,
    ) {}
}
