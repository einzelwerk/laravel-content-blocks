<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks;

use Ewk\ContentBlocks\Exceptions\InvalidBlockClassException;
use Ewk\ContentBlocks\Models\ContentBlock;

/**
 * Static holder for the configured block model class. Set once on boot by
 * the service provider (from `content-blocks.model`) so that traits and
 * resources can reference the model without touching the config repository.
 */
final class ContentBlocks
{
    /** @var class-string<ContentBlock> */
    private static string $model = ContentBlock::class;

    /**
     * @param class-string $model
     *
     * @throws InvalidBlockClassException
     */
    public static function useModel(string $model): void
    {
        if ($model !== ContentBlock::class && ! is_subclass_of($model, ContentBlock::class)) {
            throw InvalidBlockClassException::forModel($model);
        }

        self::$model = $model;
    }

    /**
     * @return class-string<ContentBlock>
     */
    public static function modelClass(): string
    {
        return self::$model;
    }

    public static function newModel(): ContentBlock
    {
        $model = self::$model;

        return new $model();
    }
}
