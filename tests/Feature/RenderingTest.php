<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Events\UnknownBlockTypeEncountered;
use Ewk\ContentBlocks\Rendering\BlockRenderer;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\FeaturedItemsBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Models\Page;
use Ewk\ContentBlocks\Tests\Fixtures\Support\FakeItemsProvider;
use Ewk\ContentBlocks\Tests\Fixtures\Support\ItemsProviderInterface;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;

final class RenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(ItemsProviderInterface::class, FakeItemsProvider::class);

        $registry = $this->app->make(BlockRegistryInterface::class);
        $registry->register(HeroBlock::class);
        $registry->register(FeaturedItemsBlock::class);
    }

    #[Test]
    public function rendersActiveBlocksInSortOrderWithEnvelope(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $page->contentBlocks()->create([
            'type' => 'featured_items',
            'name' => 'Featured',
            'content' => ['limit' => 2],
            'sort_order' => 2,
        ]);
        $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Main hero',
            'content' => ['heading' => 'Welcome', 'media_type' => 'photo'],
            'sort_order' => 1,
        ]);
        $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Disabled hero',
            'content' => ['heading' => 'Hidden'],
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $rendered = $this->app->make(BlockRenderer::class)->render($page, Request::create('/'));

        self::assertCount(2, $rendered);

        self::assertSame('hero', $rendered[0]['code']);
        self::assertSame('Main hero', $rendered[0]['name']);
        self::assertSame(
            ['heading' => 'Welcome', 'media_type' => 'photo', 'video_url' => null],
            $rendered[0]['data'],
        );

        self::assertSame('featured_items', $rendered[1]['code']);
        self::assertSame(
            ['limit' => 2, 'items' => [['id' => 1, 'title' => 'Item 1'], ['id' => 2, 'title' => 'Item 2']]],
            $rendered[1]['data'],
        );
    }

    #[Test]
    public function skipsUnknownTypesAndDispatchesEvent(): void
    {
        Event::fake([UnknownBlockTypeEncountered::class]);

        $page = Page::query()->create(['title' => 'Home']);

        $page->contentBlocks()->create([
            'type' => 'removed_block',
            'name' => 'Orphan',
        ]);
        $page->contentBlocks()->create([
            'type' => 'hero',
            'name' => 'Main hero',
            'content' => ['heading' => 'Welcome'],
        ]);

        $rendered = $this->app->make(BlockRenderer::class)->render($page, Request::create('/'));

        self::assertCount(1, $rendered);
        self::assertSame('hero', $rendered[0]['code']);

        Event::assertDispatched(
            UnknownBlockTypeEncountered::class,
            static fn(UnknownBlockTypeEncountered $event): bool => $event->block->type === 'removed_block',
        );
    }
}
