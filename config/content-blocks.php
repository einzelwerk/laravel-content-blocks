<?php

declare(strict_types=1);

use Ewk\ContentBlocks\Models\ContentBlock;

return [
    /*
    |--------------------------------------------------------------------------
    | Block model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used to store blocks. Point this at your own subclass
    | of ContentBlock to customise the table name, add media collections, or
    | extend behaviour. The class MUST extend
    | Ewk\ContentBlocks\Models\ContentBlock.
    |
    */
    'model' => ContentBlock::class,

    /*
    |--------------------------------------------------------------------------
    | Registered blocks
    |--------------------------------------------------------------------------
    |
    | Block classes registered on boot. Each class must implement
    | Ewk\ContentBlocks\Contracts\BlockContract. Blocks may also be registered
    | imperatively from any service provider via BlockRegistryInterface.
    |
    */
    'blocks' => [
        // App\Blocks\HeroBlock::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | MoonShine admin
    |--------------------------------------------------------------------------
    |
    | When `resource` is true the package registers its ContentBlocksResource
    | and CRUD pages on the MoonShine core. Disable it to ship your own
    | resource built on top of BlockFieldsBuilderInterface.
    |
    */
    'moonshine' => [
        'resource' => true,

        /*
        | Models that can own blocks, shown as a MorphTo selector on the
        | block form. Keys are model classes, values are the search column
        | (string) or [search column, label]. When empty, the form falls
        | back to hidden blockable_id / blockable_type inputs prefilled
        | from the query string.
        */
        'blockable_types' => [
            // App\Models\Page::class => ['title', 'Pages'],
        ],
    ],
];
