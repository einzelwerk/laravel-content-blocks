<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Unit\Registry;

use Ewk\ContentBlocks\Exceptions\DuplicateBlockCodeException;
use Ewk\ContentBlocks\Exceptions\InvalidBlockClassException;
use Ewk\ContentBlocks\Registry\BlockRegistry;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\ConflictingHeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\FeaturedItemsBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\TourScheduleBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Support\FakeItemsProvider;
use Ewk\ContentBlocks\Tests\Fixtures\Support\ItemsProviderInterface;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class BlockRegistryTest extends TestCase
{
    private Container $container;

    private BlockRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(ItemsProviderInterface::class, FakeItemsProvider::class);
        $this->registry = new BlockRegistry($this->container);
    }

    #[Test]
    public function registersAndResolvesBlocks(): void
    {
        $this->registry->register(HeroBlock::class);

        self::assertTrue($this->registry->has('hero'));
        self::assertSame(HeroBlock::class, $this->registry->classFor('hero'));
        self::assertInstanceOf(HeroBlock::class, $this->registry->make('hero'));
    }

    #[Test]
    public function resolvesBlocksThroughTheContainerWithDependencies(): void
    {
        $this->registry->register(FeaturedItemsBlock::class);

        $block = $this->registry->make('featured_items');

        self::assertInstanceOf(FeaturedItemsBlock::class, $block);
    }

    #[Test]
    public function returnsNullForUnknownCode(): void
    {
        self::assertNull($this->registry->make('missing'));
        self::assertNull($this->registry->classFor('missing'));
        self::assertFalse($this->registry->has('missing'));
    }

    #[Test]
    public function rejectsClassesNotImplementingTheContract(): void
    {
        $this->expectException(InvalidBlockClassException::class);

        $this->registry->register(stdClass::class);
    }

    #[Test]
    public function throwsOnDuplicateCodeFromDifferentClass(): void
    {
        $this->registry->register(HeroBlock::class);
        $this->registry->register(HeroBlock::class); // same class again is a no-op

        $this->expectException(DuplicateBlockCodeException::class);

        $this->registry->register(ConflictingHeroBlock::class);
    }

    #[Test]
    public function overrideReplacesTheRegisteredClass(): void
    {
        $this->registry->register(HeroBlock::class);

        $this->registry->override(ConflictingHeroBlock::class);

        self::assertSame(ConflictingHeroBlock::class, $this->registry->classFor('hero'));
        self::assertSame(['hero' => 'Custom hero'], $this->registry->options());
    }

    #[Test]
    public function optionsMapCodesToTitles(): void
    {
        $this->registry->register(HeroBlock::class);
        $this->registry->register(FeaturedItemsBlock::class);

        self::assertSame(
            ['hero' => 'Hero', 'featured_items' => 'Featured items'],
            $this->registry->options(),
        );
    }

    #[Test]
    public function optionsNestCategorizedBlocksUnderTheirCategory(): void
    {
        $this->registry->register(HeroBlock::class);
        $this->registry->register(TourScheduleBlock::class);
        $this->registry->register(FeaturedItemsBlock::class);

        self::assertSame(
            [
                'hero' => 'Hero',
                'Tour' => ['tour_schedule' => 'Tour schedule'],
                'featured_items' => 'Featured items',
            ],
            $this->registry->options(),
        );
    }

    #[Test]
    public function availableForMatchesBlockScopesAgainstTheOwnerScope(): void
    {
        $this->registry->register(HeroBlock::class);
        $this->registry->register(TourScheduleBlock::class);

        self::assertSame(
            ['hero' => HeroBlock::class, 'tour_schedule' => TourScheduleBlock::class],
            $this->registry->availableFor('tour'),
        );
        // unscoped blocks are offered to every owner, scoped ones only to a matching scope
        self::assertSame(['hero' => HeroBlock::class], $this->registry->availableFor('page'));
        self::assertSame(['hero' => HeroBlock::class], $this->registry->availableFor(null));

        self::assertSame(
            ['hero' => 'Hero', 'Tour' => ['tour_schedule' => 'Tour schedule']],
            $this->registry->optionsFor('tour'),
        );
        self::assertSame(['hero' => 'Hero'], $this->registry->optionsFor(null));
    }

    #[Test]
    public function allReturnsTheCodeToClassMap(): void
    {
        $this->registry->register(HeroBlock::class);

        self::assertSame(['hero' => HeroBlock::class], $this->registry->all());
    }
}
