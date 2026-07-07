<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Models;

use Ewk\MoonShineResourceKit\Concerns\Activatable;
use Ewk\MoonShineResourceKit\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Not final on purpose: applications may extend it to rename the table, add
 * media collections, or attach behaviour — point `content-blocks.model` at
 * the subclass.
 *
 * Sorting and activity come from ewk/moonshine-resource-kit: the position
 * sequence is dense and partitioned per blockable owner, so drag & drop
 * reordering in the admin never leaks across owners.
 *
 * @property int                   $id
 * @property string                $blockable_type
 * @property int                   $blockable_id
 * @property string                $type
 * @property string                $name
 * @property ?array<string, mixed> $content
 * @property int                   $sort_order
 * @property bool                  $is_active
 * @property ?Carbon               $created_at
 * @property ?Carbon               $updated_at
 */
class ContentBlock extends Model
{
    use Activatable;
    use Sortable;

    protected $table = 'content_blocks';

    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'type',
        'name',
        'content',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Blocks are ordered independently within each owning model.
     *
     * @return list<string>
     */
    public function getSortGroupColumns(): array
    {
        return ['blockable_type', 'blockable_id'];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Block content as a plain array, regardless of the cast state.
     *
     * @return array<string, mixed>
     */
    public function contentData(): array
    {
        return $this->content ?? [];
    }

}
