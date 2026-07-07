<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Blocks;

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Http\Request;

/**
 * Declares the same code as {@see HeroBlock} — used to test duplicate
 * detection and deliberate override.
 */
final class ConflictingHeroBlock extends AbstractBlock
{
    public static function code(): string
    {
        return 'hero';
    }

    public function title(): string
    {
        return 'Custom hero';
    }

    public function fields(): array
    {
        return [];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        return [];
    }
}
