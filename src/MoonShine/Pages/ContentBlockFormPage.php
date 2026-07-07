<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine\Pages;

use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use Stringable;

/**
 * Form of a single content block: common columns, the type selector, and the
 * fields of every registered block toggled client-side by `showWhen`.
 *
 * The blockable owner is edited through a MorphTo field when
 * `content-blocks.moonshine.blockable_types` is configured; otherwise the
 * morph keys are carried by hidden inputs prefilled from the query string
 * (`blockable_id` / `blockable_type`) or the edited item.
 */
final class ContentBlockFormPage extends FormPage
{
    public function __construct(
        CoreContract $core,
        private readonly BlockRegistryInterface $registry,
        private readonly BlockFieldsBuilderInterface $fieldsBuilder,
        private readonly Repository $config,
        private readonly Translator $translator,
    ) {
        parent::__construct($core);
    }

    /**
     * @return list<ComponentContract>
     */
    protected function fields(): iterable
    {
        $item = $this->currentItem();

        return [
            ...$this->blockableFields($item),

            Select::make($this->label('type'), 'type')
                ->options($this->registry->options())
                ->required()
                ->native(),

            Text::make($this->label('name'), 'name')->required(),

            Divider::make(),

            ...$this->fieldsBuilder->build($item),

            Divider::make(),

            Switcher::make($this->label('is_active'), 'is_active')
                ->default(true),
        ];
    }

    /**
     * @return array<string, array<ValidationRule|string|Stringable>|string>
     */
    protected function rules(DataWrapperContract $item): array
    {
        $rules = [
            'type' => ['required', 'string', Rule::in(array_keys($this->registry->all()))],
            'name' => ['required', 'string', 'max:255'],
        ];

        $type = $this->getCore()->getRequest()->getScalar('type');

        if (\is_string($type)) {
            $rules = [...$rules, ...$this->fieldsBuilder->rules($type)];
        }

        return $rules;
    }

    /**
     * @return list<ComponentContract>
     */
    private function blockableFields(?ContentBlock $item): array
    {
        /** @var array<class-string<Model>, string|array<int, string>> $types */
        $types = $this->config->get('content-blocks.moonshine.blockable_types', []);

        if ($types !== []) {
            return [
                MorphTo::make($this->label('blockable'), 'blockable')
                    ->types($types)
                    ->required(),
            ];
        }

        $request = $this->getCore()->getRequest();

        return [
            Hidden::make('blockable_id')
                ->setValue($request->getScalar('blockable_id') ?? $item?->blockable_id),
            Hidden::make('blockable_type')
                ->setValue($request->getScalar('blockable_type') ?? $item?->blockable_type),
        ];
    }

    private function currentItem(): ?ContentBlock
    {
        $item = $this->getResource()?->getItem();

        return $item instanceof ContentBlock ? $item : null;
    }

    private function label(string $key): string
    {
        $label = $this->translator->get('content-blocks::ui.' . $key);

        return \is_string($label) ? $label : $key;
    }
}
