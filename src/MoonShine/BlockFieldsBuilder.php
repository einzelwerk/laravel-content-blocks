<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\MoonShine;

use Ewk\ContentBlocks\Contracts\BlockFieldsBuilderInterface;
use Ewk\ContentBlocks\Contracts\BlockRegistryInterface;
use Ewk\ContentBlocks\Models\ContentBlock;

final readonly class BlockFieldsBuilder implements BlockFieldsBuilderInterface
{
    private const string CONTENT_PREFIX = 'content.';

    public function __construct(
        private BlockRegistryInterface $registry,
    ) {}

    public function build(?ContentBlock $current = null): array
    {
        $fields = [];

        foreach (array_keys($this->registry->all()) as $code) {
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

                if ($content !== null && \array_key_exists($column, $content)) {
                    $field->setValue($content[$column]);
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
}
