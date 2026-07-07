<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Rendering;

use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Contracts\HasContentBlocksContract;
use Ewk\ContentBlocks\Events\UnknownBlockTypeEncountered;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

final readonly class BlockRenderer
{
    public function __construct(
        private BlockRegistryInterface $registry,
        private Dispatcher $events,
    ) {}

    /**
     * Render the active blocks of a blockable model in sort order. Blocks
     * whose type is missing from the registry are skipped (an
     * {@see UnknownBlockTypeEncountered} event is dispatched for each).
     *
     * @return list<array<string, mixed>>
     */
    public function render(HasContentBlocksContract $blockable, Request $request): array
    {
        $blocks = $blockable->contentBlocks()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rendered = [];

        foreach ($blocks as $block) {
            $payload = $this->renderBlock($block, $request);

            if ($payload !== null) {
                $rendered[] = $payload;
            }
        }

        return $rendered;
    }

    /**
     * Render a single block with the id/code/name envelope, or null when
     * its type is not registered.
     *
     * @return array<string, mixed>|null
     */
    public function renderBlock(ContentBlock $block, Request $request): ?array
    {
        $instance = $this->registry->make($block->type);

        if ($instance === null) {
            $this->events->dispatch(new UnknownBlockTypeEncountered($block));

            return null;
        }

        return [
            'id' => $block->id,
            'code' => $block->type,
            'name' => $block->name,
            'data' => $instance->data($block, $request),
        ];
    }
}
