<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine\Pages;

use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\MoonShineResourceKit\MoonShine\Concerns\ReorderableIndex;
use Ewk\MoonShineResourceKit\MoonShine\Fields\ActiveSwitcher;
use Ewk\MoonShineResourceKit\MoonShine\Fields\SortHandle;
use Illuminate\Contracts\Translation\Translator;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * Blocks are reordered by dragging the handle column — the position column
 * itself is never edited by hand (see the Sortable concern from
 * ewk/moonshine-resource-kit on the model).
 */
final class ContentBlockIndexPage extends IndexPage
{
    use ReorderableIndex;

    public function __construct(
        CoreContract $core,
        private readonly BlockRegistryInterface $registry,
        private readonly Translator $translator,
    ) {
        parent::__construct($core);
    }

    /**
     * @return list<ComponentContract>
     */
    protected function fields(): iterable
    {
        $options = $this->registry->options();

        return [
            SortHandle::make(),

            ID::make(),

            Text::make($this->label('name'), 'name'),

            Text::make($this->label('type'), 'type')
                ->changePreview(static fn(mixed $value): string => \is_string($value)
                    ? ($options[$value] ?? $value)
                    : '')
                ->badge(),

            ActiveSwitcher::make($this->label('is_active')),
        ];
    }

    protected function reorderableWithHandle(): bool
    {
        return true;
    }

    private function label(string $key): string
    {
        $label = $this->translator->get('content-blocks::ui.' . $key);

        return \is_string($label) ? $label : $key;
    }
}
