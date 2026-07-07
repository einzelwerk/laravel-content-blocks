<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Models\Concerns;

use Ewk\ContentBlocks\ContentBlocks;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Attach to any Eloquent model that owns content blocks. Implement
 * {@see \Ewk\ContentBlocks\Contracts\HasContentBlocksContract} alongside so
 * the renderer accepts the model.
 */
trait HasContentBlocks
{
    /**
     * @return MorphMany<ContentBlock, covariant static>
     */
    public function contentBlocks(): MorphMany
    {
        return $this->morphMany(ContentBlocks::modelClass(), 'blockable');
    }
}
