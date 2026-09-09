<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Providers;

use Ewk\ContentBlocks\Console\MakeBlockCommand;
use Ewk\ContentBlocks\ContentBlocks;
use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\MoonShine\BlockFieldsBuilder;
use Ewk\ContentBlocks\MoonShine\Pages\ContentBlockFormPage;
use Ewk\ContentBlocks\MoonShine\Pages\ContentBlockIndexPage;
use Ewk\ContentBlocks\MoonShine\Resources\ContentBlocksResource;
use Ewk\ContentBlocks\Registry\BlockRegistry;
use Ewk\ContentBlocks\Rendering\BlockRenderer;
use Ewk\ContentBlocks\Support\BlockContentFilter;
use Ewk\ContentBlocks\Support\BlockOwnerResolver;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;

final class ContentBlocksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'content-blocks');

        $this->app->singleton(BlockRegistryInterface::class, static function (Container $app): BlockRegistryInterface {
            $registry = new BlockRegistry($app);

            $config = $app->make(Repository::class);
            $blocks = $config->get('content-blocks.blocks', []);

            if (\is_array($blocks)) {
                foreach ($blocks as $blockClass) {
                    if (\is_string($blockClass) && class_exists($blockClass)) {
                        $registry->register($blockClass);
                    }
                }
            }

            return $registry;
        });

        $this->app->singleton(BlockFieldsBuilderInterface::class, BlockFieldsBuilder::class);
        $this->app->singleton(BlockRenderer::class);
        $this->app->singleton(BlockContentFilter::class);
        $this->app->singleton(BlockOwnerResolver::class);
    }

    public function boot(): void
    {
        $this->configureModel();

        $this->loadMigrationsFrom($this->migrationsPath());
        $this->loadTranslationsFrom($this->langPath(), 'content-blocks');

        $this->publishes([
            $this->configPath() => $this->app->configPath('content-blocks.php'),
        ], 'content-blocks-config');

        $this->publishes([
            $this->migrationsPath() => $this->app->databasePath('migrations'),
        ], 'content-blocks-migrations');

        $this->publishes([
            $this->langPath() => $this->app->langPath('vendor/content-blocks'),
        ], 'content-blocks-lang');

        if ($this->app->runningInConsole()) {
            $this->commands([MakeBlockCommand::class]);
        }

        $this->bootMoonShineResource();
    }

    private function configureModel(): void
    {
        $model = $this->app->make(Repository::class)->get('content-blocks.model', ContentBlock::class);

        if (\is_string($model) && class_exists($model)) {
            ContentBlocks::useModel($model);
        }
    }

    /**
     * Register the packaged admin resource and its pages on the MoonShine
     * core. Gated by `content-blocks.moonshine.resource` so applications can
     * ship their own resource built on {@see BlockFieldsBuilderInterface}.
     */
    private function bootMoonShineResource(): void
    {
        $this->callAfterResolving(
            CoreContract::class,
            function (CoreContract $core): void {
                $enabled = $this->app->make(Repository::class)
                    ->get('content-blocks.moonshine.resource', true);

                if ($enabled !== true) {
                    return;
                }

                $core
                    ->resources([ContentBlocksResource::class])
                    ->pages([
                        ContentBlockIndexPage::class,
                        ContentBlockFormPage::class,
                    ]);
            },
        );
    }

    private function configPath(): string
    {
        return __DIR__ . '/../../config/content-blocks.php';
    }

    private function migrationsPath(): string
    {
        return __DIR__ . '/../../database/migrations';
    }

    private function langPath(): string
    {
        return __DIR__ . '/../../lang';
    }
}
