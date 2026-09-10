<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\Support\BlockOwnerResolver;
use Ewk\ContentBlocks\Tests\Fixtures\Models\Page;
use Ewk\ContentBlocks\Tests\Fixtures\Models\ScopedPage;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use MoonShine\Contracts\Core\CrudResourceContract;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\Core\DependencyInjection\RequestContract;
use PHPUnit\Framework\Attributes\Test;

final class BlockOwnerResolverTest extends TestCase
{
    private BlockOwnerResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->app->make(BlockOwnerResolver::class);
    }

    #[Test]
    public function resolvesTheOwnerOfAnEditedBlock(): void
    {
        $page = ScopedPage::query()->create(['title' => 'Tour template', 'scope' => 'tour']);
        $block = $page->contentBlocks()->create(['type' => 'tour_schedule', 'name' => 'Schedule']);

        $owner = $this->resolver->resolve($block, $this->request());

        self::assertTrue($owner instanceof ScopedPage && $owner->is($page));
        self::assertSame('tour', $this->resolver->scope($owner));
    }

    #[Test]
    public function resolvesTheOwnerFromSubmittedMorphKeys(): void
    {
        $page = ScopedPage::query()->create(['title' => 'Tour template', 'scope' => 'tour']);

        $request = $this->request([
            'blockable_type' => ScopedPage::class,
            'blockable_id' => (string) $page->id,
        ]);

        $owner = $this->resolver->resolve(null, $request);

        self::assertTrue($owner instanceof ScopedPage && $owner->is($page));
    }

    #[Test]
    public function ignoresMorphKeysOfClassesThatDoNotOwnBlocks(): void
    {
        $request = $this->request([
            'blockable_type' => ContentBlock::class,
            'blockable_id' => '1',
        ]);

        self::assertNull($this->resolver->resolve(null, $request));
        self::assertNull($this->resolver->resolve(null, $this->request()));
    }

    #[Test]
    public function ownersWithoutAScopeContractHaveNoScope(): void
    {
        $page = Page::query()->create(['title' => 'Home']);
        $block = $page->contentBlocks()->create(['type' => 'hero', 'name' => 'Hero']);

        $owner = $this->resolver->resolve($block, $this->request());

        self::assertTrue($owner instanceof Page && $owner->is($page));
        self::assertNull($this->resolver->scope($owner));
    }

    /**
     * Under Octane the resolver is built once per worker while every request
     * runs in its own container clone, so the parent resource must be read
     * from the container that is current at call time, not from the one the
     * resolver was constructed with.
     */
    #[Test]
    public function readsTheParentResourceFromTheCurrentContainer(): void
    {
        $page = ScopedPage::query()->create(['title' => 'Tour template', 'scope' => 'tour']);

        $resource = $this->createStub(CrudResourceContract::class);
        $resource->method('getItem')->willReturn($page);

        $crudRequest = $this->createStub(CrudRequestContract::class);
        $crudRequest->method('hasResource')->willReturn(true);
        $crudRequest->method('getResource')->willReturn($resource);

        $current = clone $this->app;
        $current->instance(CrudRequestContract::class, $crudRequest);

        Container::setInstance($current);

        try {
            $owner = $this->resolver->resolve(null, $this->request());
        } finally {
            Container::setInstance($this->app);
        }

        self::assertTrue($owner instanceof ScopedPage && $owner->is($page));
    }

    /**
     * @param array<string, string> $input
     */
    private function request(array $input = []): RequestContract
    {
        $this->app->instance('request', Request::create('/', 'POST', $input));

        $request = $this->app->make(RequestContract::class);
        \assert($request instanceof RequestContract);

        return $request;
    }
}
