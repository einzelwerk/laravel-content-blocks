<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Blocks;

use Ewk\ContentBlocks\Blocks\AbstractBlock;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Http\Request;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * Static block: the payload comes straight from the stored JSON content.
 */
final class HeroBlock extends AbstractBlock
{
    public static function code(): string
    {
        return 'hero';
    }

    public function title(): string
    {
        return 'Hero';
    }

    public function fields(): array
    {
        return [
            Text::make('Heading', 'heading')->required(),
            Select::make('Media type', 'media_type')
                ->options(['video' => 'Video', 'photo' => 'Photo'])
                ->default('video')
                ->native(),
            Text::make('Video URL', 'video_url')
                ->showWhen($this->contentColumn('media_type'), 'video'),
        ];
    }

    public function rules(): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'media_type' => ['nullable', 'string', 'in:video,photo'],
            'video_url' => ['nullable', 'string'],
        ];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        $content = $this->content($block);

        return [
            'heading' => $content['heading'] ?? null,
            'media_type' => $content['media_type'] ?? 'video',
            'video_url' => $content['video_url'] ?? null,
        ];
    }
}
