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
