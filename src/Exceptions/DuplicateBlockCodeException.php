<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Exceptions;

final class DuplicateBlockCodeException extends ContentBlocksException
{
    public static function forCode(string $code, string $existingClass, string $incomingClass): self
    {
        return new self(\sprintf(
            'Block code [%s] is already registered by [%s]; refusing to register [%s]. Use override() to replace it deliberately.',
            $code,
            $existingClass,
            $incomingClass,
        ));
    }
}
