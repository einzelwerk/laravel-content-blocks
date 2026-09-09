<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Contracts;

use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Contracts\Validation\ValidationRule;
use MoonShine\Contracts\UI\FieldContract;
use Stringable;

/**
 * Builds the per-type MoonShine form fields for every registered block.
 * Replace the container binding to customise column mapping (e.g. to keep
 * relation-backed fields such as media libraries out of the JSON content).
 */
interface BlockFieldsBuilderInterface
{
    /**
     * Fields of the registered blocks: columns prefixed with `content.`,
     * visibility toggled by the `type` selector, current values applied
     * from the edited block. `$codes` limits the built types (e.g. to the
     * blocks the owner accepts); null builds every registered block.
     *
     * @param list<string>|null $codes
     *
     * @return list<FieldContract>
     */
    public function build(?ContentBlock $current = null, ?array $codes = null): array;

    /**
     * Validation rules of the block registered under `$code`, prefixed
     * with `content.`. Empty for unknown codes.
     *
     * @return array<string, array<ValidationRule|Stringable|string>|string>
     */
    public function rules(string $code): array;
}
