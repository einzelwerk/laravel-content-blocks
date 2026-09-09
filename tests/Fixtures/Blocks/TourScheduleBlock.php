<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Blocks;

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Http\Request;
use MoonShine\UI\Fields\Number;

/**
 * Scoped block: offered only to owners whose scope is `tour`, listed under
 * the "Tour" category of the type selector.
 */
final class TourScheduleBlock extends AbstractBlock
{
    public static function code(): string
    {
        return 'tour_schedule';
    }

    public function title(): string
    {
        return 'Tour schedule';
    }

    public function category(): ?string
    {
        return 'Tour';
    }

    public function scopes(): array
    {
        return ['tour'];
    }

    public function fields(): array
    {
        return [
            Number::make('Per page', 'per_page')->default(5),
        ];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        return ['per_page' => (int) ($this->content($block)['per_page'] ?? 5)];
    }
}
