<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine;

use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

final readonly class BlockFieldsBuilder implements BlockFieldsBuilderInterface
{
    private const string CONTENT_PREFIX = 'content.';

    public function __construct(
        private BlockRegistryInterface $registry,
        private Container $container,
    ) {}

    public function build(?ContentBlock $current = null, ?array $codes = null): array
    {
        $fields = [];

        foreach (array_keys($this->registry->all()) as $code) {
            if ($codes !== null && ! \in_array($code, $codes, true)) {
                continue;
            }

            $block = $this->registry->make($code);

            if ($block === null) {
                continue;
            }

            $content = $current !== null && $current->type === $code
                ? $current->contentData()
                : null;

            foreach ($block->fields() as $field) {
                $column = $field->getColumn();

                $field->setColumn(self::CONTENT_PREFIX . $column);
                $field->showWhen('type', $code);

                // MoonShine toggles fields by their input name; two types
                // sharing a column (`title`) would collide, so each field
                // gets a type-scoped identity for the show-when logic.
                $field->customAttributes([
                    'data-show-when-field' => self::CONTENT_PREFIX . $code . '.' . $column,
                ]);

                // The form submits the fields of every registered type at
                // once; only the fields of the selected type may write into
                // the content (or touch uploaded files) on save.
                $field->canApply(fn(): bool => $this->submittedType() === $code);

                if ($content !== null && Arr::has($content, $column)) {
                    $field->setValue(Arr::get($content, $column));
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function rules(string $code): array
    {
        $block = $this->registry->make($code);

        if ($block === null) {
            return [];
        }

        $rules = [];

        foreach ($block->rules() as $key => $rule) {
            $rules[self::CONTENT_PREFIX . $key] = $rule;
        }

        return $rules;
    }

    /**
     * Resolved lazily per call: the builder is a singleton, so it must not
     * hold on to a request instance (long-running workers such as Octane).
     */
    private function submittedType(): ?string
    {
        $type = $this->container->make(Request::class)->input('type');

        return \is_string($type) ? $type : null;
    }
}
