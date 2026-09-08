<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Support\BlockContentFilter;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\LocalizedTextBlock;
use PHPUnit\Framework\Attributes\Test;
use Ewk\ContentBlocks\Tests\Support\TestCase;

final class BlockContentFilterTest extends TestCase
{
    #[Test]
    public function keepsOnlyKeysOwnedByTheBlockFields(): void
    {
        $filter = new BlockContentFilter();

        $filtered = $filter->filter(new HeroBlock(), [
            'heading' => 'Welcome',
            'media_type' => 'photo',
            'video_url' => null,
            // keys of other block types submitted by the shared form
            'tabs' => [['title' => 'Foreign']],
            'limit' => 10,
        ]);

        self::assertSame(
            ['heading' => 'Welcome', 'media_type' => 'photo', 'video_url' => null],
            $filtered,
        );
    }

    #[Test]
    public function keepsTheRootKeyOfDottedColumns(): void
    {
        $filter = new BlockContentFilter();

        $filtered = $filter->filter(new LocalizedTextBlock(), [
            'body' => ['en' => 'Hello', 'de' => 'Hallo'],
            'heading' => 'Foreign',
        ]);

        self::assertSame(['body' => ['en' => 'Hello', 'de' => 'Hallo']], $filtered);
    }

    #[Test]
    public function returnsEmptyArrayWhenBlockHasNoFields(): void
    {
        $filter = new BlockContentFilter();

        $block = new \Ewk\ContentBlocks\Tests\Fixtures\Blocks\ConflictingHeroBlock();

        self::assertSame([], $filter->filter($block, ['anything' => 1]));
    }
}
