<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Support;

use Ewk\ContentBlocks\Contracts\BlockContract;

/**
 * The admin form renders the fields of every registered block and toggles
 * them client-side, so a submitted `content` payload can carry keys that
 * belong to other block types. This filter keeps only the keys managed by
 * the selected block's own fields before the content is persisted.
 */
final readonly class BlockContentFilter
{
    /**
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public function filter(BlockContract $block, array $content): array
    {
        $columns = [];

        foreach ($block->fields() as $field) {
            $columns[$field->getColumn()] = true;
        }

        return array_intersect_key($content, $columns);
    }
}
