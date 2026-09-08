<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Blocks;

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Http\Request;
use MoonShine\UI\Fields\Textarea;

/**
 * Block whose fields address nested content keys through dotted columns
 * (`body.en` → `content.body.en`), the way per-locale editors do.
 */
final class LocalizedTextBlock extends AbstractBlock
{
    public static function code(): string
    {
        return 'localized_text';
    }

    public function title(): string
    {
        return 'Localized text';
    }

    public function fields(): array
    {
        return [
            Textarea::make('Body (EN)', 'body.en'),
            Textarea::make('Body (DE)', 'body.de'),
        ];
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'array'],
            'body.en' => ['nullable', 'string'],
            'body.de' => ['nullable', 'string'],
        ];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        $body = $this->content($block)['body'] ?? [];

        return ['body' => \is_array($body) ? $body : []];
    }
}
