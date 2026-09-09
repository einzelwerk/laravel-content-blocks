<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Support;

use Ewk\ContentBlocks\Providers\ContentBlocksServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ewk\MoonShineResourceKit\Providers\MoonShineResourceKitServiceProvider;
use Illuminate\Support\Facades\Schema;
use MoonShine\Laravel\Providers\MoonShineServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            MoonShineServiceProvider::class,
            MoonShineResourceKitServiceProvider::class,
            ContentBlocksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('pages', static function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('scope')->nullable();
            $table->timestamps();
        });
    }
}
