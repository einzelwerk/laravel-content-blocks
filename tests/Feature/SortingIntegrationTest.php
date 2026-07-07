<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Tests\Fixtures\Models\Page;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class SortingIntegrationTest extends TestCase
{
    #[Test]
    public function assignsDensePositionsPerOwner(): void
    {
        $home = Page::query()->create(['title' => 'Home']);
        $about = Page::query()->create(['title' => 'About']);

        $first = $home->contentBlocks()->create(['type' => 'hero', 'name' => 'First']);
        $second = $home->contentBlocks()->create(['type' => 'hero', 'name' => 'Second']);
        $other = $about->contentBlocks()->create(['type' => 'hero', 'name' => 'Other page']);

        self::assertSame(1, $first->sort_order);
        self::assertSame(2, $second->sort_order);
        // The sequence is partitioned by the blockable owner, not global.
        self::assertSame(1, $other->sort_order);
    }

    #[Test]
    public function movesBlocksWithinTheirOwnerSequence(): void
    {
        $page = Page::query()->create(['title' => 'Home']);

        $first = $page->contentBlocks()->create(['type' => 'hero', 'name' => 'First']);
        $second = $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Second']);

        $second->moveUp();

        self::assertSame(1, $second->refresh()->sort_order);
        self::assertSame(2, $first->refresh()->sort_order);
    }

    #[Test]
    public function togglesActivity(): void
    {
        $page = Page::query()->create(['title' => 'Home']);
        $block = $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Hero']);

        self::assertTrue($block->refresh()->isActive());

        $block->toggleActive();

        self::assertFalse($block->refresh()->isActive());
    }
}
