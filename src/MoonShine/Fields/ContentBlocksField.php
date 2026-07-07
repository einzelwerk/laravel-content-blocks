<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine\Fields;

use Closure;
use Ewk\ContentBlocks\MoonShine\Resources\ContentBlocksResource;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\UI\Components\Table\TableBuilder;

/**
 * Embeddable block manager for any resource form whose model uses
 * HasContentBlocks: renders the owner's blocks as a HasMany table with
 * drag & drop reordering, inline activity toggling, and creation through
 * the packaged {@see ContentBlocksResource}.
 *
 *     ContentBlocksField::make('Blocks')
 *
 * Returns a plain HasMany field, so every HasMany option (searchable,
 * modifyBuilder, ...) stays available for per-resource tuning.
 */
final class ContentBlocksField
{
    /**
     * @return HasMany<MorphMany<\Ewk\ContentBlocks\Models\ContentBlock, \Illuminate\Database\Eloquent\Model>>
     */
    public static function make(
        Closure|string|null $label = null,
        string $relationName = 'contentBlocks',
    ): HasMany {
        /** @var HasMany<MorphMany<\Ewk\ContentBlocks\Models\ContentBlock, \Illuminate\Database\Eloquent\Model>> $field */
        $field = HasMany::make($label ?? 'Blocks', $relationName, resource: ContentBlocksResource::class);

        return $field
            ->creatable()
            ->modifyTable(static function (TableBuilderContract $table, bool $preview = false) use ($field): TableBuilderContract {
                $resource = $field->getResource();

                if (! $table instanceof TableBuilder || ! $resource instanceof ContentBlocksResource) {
                    return $table;
                }

                return $resource->reorderableTableModifier(handle: true)($table, $preview);
            });
    }

    private function __construct() {}
}
