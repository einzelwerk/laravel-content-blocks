<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\ContentBlocks;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Models\Page;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class HasContentBlocksTest extends TestCase
{
    #[Test]
    public function attachesBlocksPolymorphically(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $block = $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Main hero',
            'content' => ['heading' => 'Welcome'],
        ]);

        self::assertInstanceOf(ContentBlock::class, $block);
        self::assertSame(Page::class, $block->blockable_type);
        self::assertSame($page->id, $block->blockable_id);
        self::assertTrue($block->blockable()->first()?->is($page) ?? false);
        self::assertSame(['heading' => 'Welcome'], $block->contentData());
    }

    #[Test]
    public function listsBlocksByPositionRegardlessOfInsertionOrder(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Third', 'sort_order' => 3]);
        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'First', 'sort_order' => 1]);
        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Second', 'sort_order' => 2]);

        self::assertSame(['First', 'Second', 'Third'], $page->contentBlocks()->pluck('name')->all());
        self::assertSame(['First', 'Second', 'Third'], $page->contentBlocks->pluck('name')->all());

        // SQLite may walk the render index and return position order by
        // accident; the relation has to ask for it explicitly.
        /** @var list<array{column: string, direction: string}> $orders */
        $orders = $page->contentBlocks()->getQuery()->getQuery()->orders ?? [];

        self::assertSame(
            ['content_blocks.sort_order asc', 'content_blocks.id asc'],
            array_map(static fn(array $order): string => $order['column'] . ' ' . $order['direction'], $orders),
        );
    }

    #[Test]
    public function breaksPositionTiesByKey(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Older', 'sort_order' => 0]);
        $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Newer', 'sort_order' => 0]);

        self::assertSame(['Older', 'Newer'], $page->contentBlocks()->pluck('name')->all());
    }

    #[Test]
    public function nestedContentKeysCanBeSetThroughDataSet(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $block = $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Main hero',
            'content' => ['heading' => 'Welcome'],
        ]);

        // MoonShine applies form fields through data_set() on the model.
        data_set($block, 'content.heading', 'Changed');
        data_set($block, 'content.body.en', 'Hello');
        $block->save();

        self::assertSame(
            ['heading' => 'Changed', 'body' => ['en' => 'Hello']],
            $block->fresh()?->contentData(),
        );
    }

    #[Test]
    public function contentDataFallsBackToEmptyArray(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $block = $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Empty hero',
        ]);

        self::assertSame([], $block->contentData());
    }

    #[Test]
    public function defaultModelClassIsTheBundledOne(): void
    {
        self::assertSame(ContentBlock::class, ContentBlocks::modelClass());
        self::assertInstanceOf(ContentBlock::class, ContentBlocks::newModel());
    }
}
