<?php

declare(strict_types=1);

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;

arch('package classes use strict types')
    ->expect('Ewk\ContentBlocks')
    ->toUseStrictTypes();

arch('no Laravel facades in src')
    ->expect('Ewk\ContentBlocks')
    ->not->toUse('Illuminate\Support\Facades');

arch('no debug calls in src')
    ->expect('Ewk\ContentBlocks')
    ->not->toUse(['dd', 'dump', 'var_dump', 'print_r', 'exit', 'die', 'ray']);

arch('contracts are interfaces')
    ->expect('Ewk\ContentBlocks\Contracts')
    ->toBeInterfaces();

arch('services are final')
    ->expect([
        'Ewk\ContentBlocks\Registry',
        'Ewk\ContentBlocks\Rendering',
        'Ewk\ContentBlocks\Support',
        'Ewk\ContentBlocks\Events',
    ])
    ->classes()
    ->toBeFinal();

arch('the block base and the model stay extensible on purpose')
    ->expect([AbstractBlock::class])
    ->toBeAbstract()
    ->and([ContentBlock::class])
    ->not->toBeFinal();

test('php files under src, tests and stubs declare strict types', function (): void {
    $directories = array_filter([
        realpath(__DIR__ . '/../../src'),
        realpath(__DIR__ . '/../../tests'),
        realpath(__DIR__ . '/../../config'),
        realpath(__DIR__ . '/../../database'),
    ]);

    foreach ($directories as $directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            expect($contents)->toContain('declare(strict_types=1)');
        }
    }
});
