<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature\MoonShine;

use Ewk\ContentBlocks\MoonShine\Resources\ContentBlocksResource;
use Ewk\ContentBlocks\Tests\Fixtures\Models\Page;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ContentBlocksResourceTest extends TestCase
{
    /**
     * The HasMany table of the owner's form lists blocks through the
     * resource query, so the resource itself has to order by position; the
     * MoonShine default (`id desc`) would hide every drag & drop reorder.
     */
    #[Test]
    public function listsBlocksByPositionThenKey(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Last', 'sort_order' => 3]);
        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Older tie', 'sort_order' => 1]);
        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Newer tie', 'sort_order' => 1]);

        $resource = $this->app->make(ContentBlocksResource::class);

        self::assertSame('sort_order', $resource->getSortColumn());
        self::assertSame('asc', $resource->getSortDirection());
        self::assertSame(
            ['Older tie', 'Newer tie', 'Last'],
            $resource->getQuery()->pluck('name')->all(),
        );
    }
}
