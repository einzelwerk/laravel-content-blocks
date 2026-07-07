<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Exceptions;

use Ewk\ContentBlocks\Contracts\BlockContract;

final class InvalidBlockClassException extends ContentBlocksException
{
    public static function forClass(string $blockClass): self
    {
        return new self(\sprintf(
            'Block class [%s] must implement [%s].',
            $blockClass,
            BlockContract::class,
        ));
    }

    public static function forModel(string $modelClass): self
    {
        return new self(\sprintf(
            'Content block model [%s] must extend [%s].',
            $modelClass,
            \Ewk\ContentBlocks\Models\ContentBlock::class,
        ));
    }
}
