<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Tests\Fixtures\Blocks\HeroBlock;
use Ewk\ContentBlocks\Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConfigRegistrationTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('content-blocks.blocks', [HeroBlock::class]);
    }

    #[Test]
    public function registersBlocksListedInConfig(): void
    {
        $registry = $this->app->make(BlockRegistryInterface::class);

        self::assertTrue($registry->has('hero'));
        self::assertSame(HeroBlock::class, $registry->classFor('hero'));
    }

    #[Test]
    public function registryIsASingleton(): void
    {
        self::assertSame(
            $this->app->make(BlockRegistryInterface::class),
            $this->app->make(BlockRegistryInterface::class),
        );
    }
}
