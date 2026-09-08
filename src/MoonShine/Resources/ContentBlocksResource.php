<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine\Resources;

use Ewk\ContentBlocks\ContentBlocks;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Ewk\ContentBlocks\MoonShine\Pages\ContentBlockFormPage;
use Ewk\ContentBlocks\MoonShine\Pages\ContentBlockIndexPage;
use Ewk\ContentBlocks\Support\BlockContentFilter;
use Ewk\MoonShineResourceKit\MoonShine\Concerns\WithActivatable;
use Ewk\MoonShineResourceKit\MoonShine\Concerns\WithReorderable;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Contracts\Translation\Translator;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\SkipMenu;

/**
 * CRUD resource for content blocks. Skipped in the menu on purpose: blocks
 * are managed from the owning model's form (e.g. a HasMany field on the
 * page resource), not as a standalone section.
 *
 * @extends ModelResource<ContentBlock>
 */
#[SkipMenu]
final class ContentBlocksResource extends ModelResource
{
    use WithActivatable;
    use WithReorderable;

    protected string $column = 'name';

    public function __construct(
        CoreContract $core,
        private readonly BlockRegistryInterface $registry,
        private readonly BlockContentFilter $contentFilter,
        private readonly Translator $translator,
    ) {
        $this->model = ContentBlocks::modelClass();

        parent::__construct($core);
    }

    public function getTitle(): string
    {
        $title = $this->translator->get('content-blocks::ui.blocks');

        return \is_string($title) ? $title : 'Blocks';
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ContentBlockIndexPage::class,
            ContentBlockFormPage::class,
        ];
    }

    protected function beforeCreating(DataWrapperContract $item): DataWrapperContract
    {
        return $this->applyFilteredContent($item);
    }

    protected function beforeUpdating(DataWrapperContract $item): DataWrapperContract
    {
        return $this->applyFilteredContent($item);
    }

    /**
     * The form submits `content.*` inputs of every registered block type
     * (they are toggled client-side by `showWhen`), so the raw payload can
     * carry keys of other types. Persist only the keys owned by the fields
     * of the selected type.
     */
    private function applyFilteredContent(DataWrapperContract $item): DataWrapperContract
    {
        $model = $item->getOriginal();

        if (! $model instanceof ContentBlock) {
            return $item;
        }

        $request = $this->getCore()->getRequest();
        $type = $request->getScalar('type');
        $content = $request->get('content');

        if (! \is_string($type) || ! \is_array($content)) {
            return $item;
        }

        $block = $this->registry->make($type);

        if ($block === null) {
            return $item;
        }

        /** @var array<string, mixed> $content */
        $model->content = new ArrayObject($this->contentFilter->filter($block, $content));

        return $item;
    }
}
