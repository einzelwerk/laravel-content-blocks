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
     * Blocks in their admin-defined order: by position, then by key for
     * rows that share a position (seeded or imported data).
     *
     * @return MorphMany<ContentBlock, covariant static>
     */
    public function contentBlocks(): MorphMany
    {
        $model = ContentBlocks::modelClass();
        /** @var ContentBlock $instance */
        $instance = new $model();

        return $this->morphMany($model, 'blockable')
            ->orderedBySort()
            ->orderBy($instance->getQualifiedKeyName());
    }
}
