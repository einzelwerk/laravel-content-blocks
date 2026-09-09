<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature\MoonShine;

use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\FeaturedItemsBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\LocalizedTextBlock;
use Ewk\ContentBlocks\Tests\Fixtures\Support\FakeItemsProvider;
use Ewk\ContentBlocks\Tests\Fixtures\Support\ItemsProviderInterface;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use Illuminate\Http\Request;
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
        $registry->register(LocalizedTextBlock::class);

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
            [
                'content.heading',
                'content.media_type',
                'content.video_url',
                'content.limit',
                'content.body.en',
                'content.body.de',
            ],
            $columns,
        );

        foreach ($fields as $field) {
            self::assertTrue($field->hasShowWhen(), $field->getColumn() . ' must be toggled by type');
        }
    }

    #[Test]
    public function scopesTheShowWhenIdentityByType(): void
    {
        $identities = [];

        foreach ($this->builder->build() as $field) {
            $identities[] = $field->getAttribute('data-show-when-field');
        }

        self::assertSame(
            [
                'content.hero.heading',
                'content.hero.media_type',
                'content.hero.video_url',
                'content.featured_items.limit',
                'content.localized_text.body.en',
                'content.localized_text.body.de',
            ],
            $identities,
        );
    }

    #[Test]
    public function buildsOnlyTheRequestedTypes(): void
    {
        $columns = array_map(
            static fn(FieldContract $field): string => $field->getColumn(),
            $this->builder->build(null, ['featured_items']),
        );

        self::assertSame(['content.limit'], $columns);
        self::assertSame([], $this->builder->build(null, []));
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
    public function appliesNestedValuesThroughDottedColumns(): void
    {
        $block = new ContentBlock([
            'type' => 'localized_text',
            'name' => 'Intro',
            'content' => ['body' => ['en' => 'Hello', 'de' => 'Hallo']],
        ]);

        $byColumn = [];

        foreach ($this->builder->build($block) as $field) {
            $byColumn[$field->getColumn()] = $field;
        }

        self::assertSame('Hello', $byColumn['content.body.en']->toValue());
        self::assertSame('Hallo', $byColumn['content.body.de']->toValue());
    }

    #[Test]
    public function onlyFieldsOfTheSubmittedTypeCanApply(): void
    {
        $this->app->instance('request', Request::create('/', 'POST', ['type' => 'hero']));

        $byColumn = [];

        foreach ($this->builder->build() as $field) {
            $byColumn[$field->getColumn()] = $field;
        }

        self::assertTrue($byColumn['content.heading']->isCanApply());
        self::assertFalse($byColumn['content.limit']->isCanApply());
        self::assertFalse($byColumn['content.body.en']->isCanApply());
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
