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
        $keys = [];

        foreach ($block->fields() as $field) {
            // A dotted column (`body.ru`) lives under its root key of the
            // content array — that root is what the block owns.
            $keys[explode('.', $field->getColumn(), 2)[0]] = true;
        }

        return array_intersect_key($content, $keys);
    }
}
