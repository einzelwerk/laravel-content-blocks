<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Blocks;

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Support\ItemsProviderInterface;
use Illuminate\Http\Request;
use MoonShine\UI\Fields\Number;

/**
 * Dynamic block: the payload is computed at render time through a
 * constructor-injected dependency; the stored content holds only settings.
 */
final class FeaturedItemsBlock extends AbstractBlock
{
    public function __construct(
        private readonly ItemsProviderInterface $items,
    ) {}

    public static function code(): string
    {
        return 'featured_items';
    }

    public function title(): string
    {
        return 'Featured items';
    }

    public function fields(): array
    {
        return [
            Number::make('Limit', 'limit')->min(1)->default(3),
        ];
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        $limit = (int) ($this->content($block)['limit'] ?? 3);

        return [
            'limit' => $limit,
            'items' => $this->items->items($limit),
        ];
    }
}
