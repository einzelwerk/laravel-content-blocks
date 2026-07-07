<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class MakeBlockCommand extends Command
{
    protected $signature = 'make:content-block
        {name : Block class name (e.g. Hero or HeroBlock)}
        {--code= : Block code stored in the type column (defaults to the snake_cased name)}
        {--dynamic : Generate a dynamic block with an injected data dependency}
        {--force : Overwrite the file if it already exists}';

    protected $description = 'Generate a content block class in app/Blocks';

    public function handle(Filesystem $files, Application $app): int
    {
        $class = $this->qualifyClass();
        $code = $this->blockCode($class);

        $directory = $app->basePath('app/Blocks');
        $path = $directory . '/' . $class . '.php';

        if ($files->exists($path) && $this->option('force') !== true) {
            $this->components->error(\sprintf('Block [%s] already exists. Use --force to overwrite.', $path));

            return self::FAILURE;
        }

        $stub = $this->option('dynamic') === true ? 'block.dynamic.stub' : 'block.stub';

        $contents = str_replace(
            ['{{ class }}', '{{ code }}', '{{ title }}'],
            [$class, $code, Str::headline($code)],
            $files->get(__DIR__ . '/../../stubs/' . $stub),
        );

        $files->ensureDirectoryExists($directory);
        $files->put($path, $contents);

        $this->components->info(\sprintf('Block [%s] created. Register it in config/content-blocks.php.', $path));

        return self::SUCCESS;
    }

    private function qualifyClass(): string
    {
        $name = $this->argument('name');
        \assert(\is_string($name));

        $class = Str::studly($name);

        return Str::endsWith($class, 'Block') ? $class : $class . 'Block';
    }

    private function blockCode(string $class): string
    {
        $code = $this->option('code');

        if (\is_string($code) && $code !== '') {
            return $code;
        }

        return Str::snake(Str::beforeLast($class, 'Block'));
    }
}
