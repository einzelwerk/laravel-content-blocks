<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature\MoonShine;

use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\FeaturedItemsBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Support\FakeItemsProvider;
use Ewk\ContentBlocks\Tests\Fixtures\Support\ItemsProviderInterface;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use MoonShine\Contracts\UI\FieldContract;
use PHPUnit\Framework\Attributes\Test;

final class BlockFieldsBuilderTest extends TestCase
{
    private BlockFieldsBuilderInterface $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(ItemsProviderInterface::class, FakeItemsProvider::class);

        $registry = $this->app->make(BlockRegistryInterface::class);
        $registry->register(HeroBlock::class);
        $registry->register(FeaturedItemsBlock::class);

        $this->builder = $this->app->make(BlockFieldsBuilderInterface::class);
    }

    #[Test]
    public function prefixesColumnsWithContentAndTogglesByType(): void
    {
        $fields = $this->builder->build();

        $columns = array_map(
            static fn(FieldContract $field): string => $field->getColumn(),
            $fields,
        );

        self::assertSame(
            ['content.heading', 'content.media_type', 'content.video_url', 'content.limit'],
            $columns,
        );

        foreach ($fields as $field) {
            self::assertTrue($field->hasShowWhen(), $field->getColumn() . ' must be toggled by type');
        }
    }

    #[Test]
    public function appliesCurrentValuesOnlyToTheEditedType(): void
    {
        $block = new ContentBlock([
            'type' => 'hero',
            'name' => 'Main hero',
            'content' => ['heading' => 'Welcome', 'limit' => 99],
        ]);

        $fields = $this->builder->build($block);

        $byColumn = [];

        foreach ($fields as $field) {
            $byColumn[$field->getColumn()] = $field;
        }

        self::assertSame('Welcome', $byColumn['content.heading']->toValue());
        // `limit` belongs to featured_items — the hero content must not leak into it.
        self::assertNotSame(99, $byColumn['content.limit']->toValue());
    }

    #[Test]
    public function prefixesRulesOfTheRequestedType(): void
    {
        self::assertSame(
            [
                'content.heading' => ['required', 'string', 'max:255'],
                'content.media_type' => ['nullable', 'string', 'in:video,photo'],
                'content.video_url' => ['nullable', 'string'],
            ],
            $this->builder->rules('hero'),
        );

        self::assertSame([], $this->builder->rules('missing'));
    }
}
