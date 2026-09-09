<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Blocks;

use Ewk\ContentBlocks\Contracts\BlockContract;
use Ewk\ContentBlocks\Models\ContentBlock;

abstract class AbstractBlock implements BlockContract
{
    public function category(): ?string
    {
        return null;
    }

    public function scopes(): array
    {
        return [];
    }

    public function rules(): array
    {
        return [];
    }

    /**
     * Block content as a plain array.
     *
     * @return array<string, mixed>
     */
    protected function content(ContentBlock $block): array
    {
        return $block->contentData();
    }

    /**
     * Fully-qualified form column of an own content field — use for
     * intra-block `showWhen()` conditions, which must reference the column
     * as it appears in the built form (`content.media_type`), not the
     * relative name the block declares.
     */
    protected function contentColumn(string $column): string
    {
        return 'content.' . $column;
    }
}
