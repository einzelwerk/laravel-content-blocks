<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Feature;

use Ewk\ContentBlocks\Tests\Support\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class MakeBlockCommandTest extends TestCase
{
    private Filesystem $files;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem();
        $this->directory = $this->app->basePath('app/Blocks');
        $this->files->deleteDirectory($this->directory);
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function generatesAStaticBlock(): void
    {
        $this->artisan('make:content-block', ['name' => 'PromoBanner'])->assertSuccessful();

        $path = $this->directory . '/PromoBannerBlock.php';
        self::assertFileExists($path);

        $contents = $this->files->get($path);
        self::assertStringContainsString('final class PromoBannerBlock extends AbstractBlock', $contents);
        self::assertStringContainsString("return 'promo_banner';", $contents);
        self::assertStringContainsString('declare(strict_types=1);', $contents);
    }

    #[Test]
    public function generatesADynamicBlockWithCustomCode(): void
    {
        $this->artisan('make:content-block', [
            'name' => 'ProductList',
            '--dynamic' => true,
            '--code' => 'products',
        ])->assertSuccessful();

        $contents = $this->files->get($this->directory . '/ProductListBlock.php');
        self::assertStringContainsString("return 'products';", $contents);
        self::assertStringContainsString('public function __construct(', $contents);
    }

    #[Test]
    public function refusesToOverwriteWithoutForce(): void
    {
        $this->artisan('make:content-block', ['name' => 'Hero'])->assertSuccessful();
        $this->artisan('make:content-block', ['name' => 'Hero'])->assertFailed();
        $this->artisan('make:content-block', ['name' => 'Hero', '--force' => true])->assertSuccessful();
    }
}
