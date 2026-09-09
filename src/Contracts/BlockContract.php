<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Contracts;

use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use MoonShine\Contracts\UI\FieldContract;
use Stringable;

/**
 * A single block type. Implementations are resolved through the service
 * container, so constructor dependency injection is fully supported —
 * dynamic blocks inject queries/use-cases and compute their payload at
 * render time.
 */
interface BlockContract
{
    /**
     * Unique block identifier stored in the `type` column.
     * Static on purpose: the registry maps codes to classes without
     * instantiating every block on registration.
     */
    public static function code(): string;

    /**
     * Human-readable name shown in the admin type selector.
     */
    public function title(): string;

    /**
     * Group the block is listed under in the admin type selector
     * (rendered as an `<optgroup>`); null lists it ungrouped.
     */
    public function category(): ?string;

    /**
     * Owner scopes the block may be attached to — matched against
     * {@see ScopedBlockOwnerContract::blockScope()} of the owning model.
     * An empty list makes the block available to every owner.
     *
     * @return list<string>
     */
    public function scopes(): array;

    /**
     * MoonShine fields editing the block content. Field columns are relative
     * to the block content root (`heading`, not `content.heading`) — the
     * package prefixes them when the admin form is built. For intra-block
     * `showWhen()` conditions reference the prefixed column via
     * {@see \Ewk\ContentBlocks\Blocks\AbstractBlock::contentColumn()}.
     *
     * @return list<FieldContract>
     */
    public function fields(): array;

    /**
     * Validation rules with keys relative to the block content root
     * (e.g. `tabs.*.title`); the package prefixes them with `content.`.
     *
     * @return array<string, array<ValidationRule|Stringable|string>|string>
     */
    public function rules(): array;

    /**
     * Public payload rendered under `data`. The id/code/name envelope is
     * added by the renderer — return only block-specific data.
     *
     * @return array<string, mixed>
     */
    public function data(ContentBlock $block, Request $request): array;
}
