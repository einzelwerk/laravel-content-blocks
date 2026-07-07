<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Contracts;

use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasContentBlocksContract
{
    /**
     * @return MorphMany<ContentBlock, covariant Model>
     */
    public function contentBlocks(): MorphMany;
}
