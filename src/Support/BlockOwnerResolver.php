<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Support;

use Ewk\ContentBlocks\Contracts\HasContentBlocksContract;
use Ewk\ContentBlocks\Contracts\ScopedBlockOwnerContract;
use Ewk\ContentBlocks\Models\ContentBlock;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use MoonShine\Contracts\Core\DependencyInjection\CrudRequestContract;
use MoonShine\Contracts\Core\DependencyInjection\RequestContract;

/**
 * Finds the model a block form belongs to, so the type selector and the
 * `type` validation can be narrowed to the blocks the owner accepts.
 *
 * The owner comes, in order, from the edited block, from the submitted
 * morph keys (`blockable_type` / `blockable_id`, present on the standalone
 * form and on every save), or from the parent resource item of an embedded
 * HasMany form. Null means the owner is unknown, in which case callers fall
 * back to every registered block.
 */
final readonly class BlockOwnerResolver
{
    public function resolve(?ContentBlock $item, RequestContract $request): ?HasContentBlocksContract
    {
        $owner = $item?->blockable;

        if ($owner instanceof HasContentBlocksContract) {
            return $owner;
        }

        return $this->fromMorphKeys($request) ?? $this->fromParentResource();
    }

    public function scope(?HasContentBlocksContract $owner): ?string
    {
        return $owner instanceof ScopedBlockOwnerContract ? $owner->blockScope() : null;
    }

    private function fromMorphKeys(RequestContract $request): ?HasContentBlocksContract
    {
        $type = $request->getScalar('blockable_type');
        $id = $request->getScalar('blockable_id');

        if (! \is_string($type) || $type === '' || ! \is_scalar($id) || $id === '') {
            return null;
        }

        $class = Relation::getMorphedModel($type) ?? $type;

        if (! is_subclass_of($class, Model::class) || ! is_subclass_of($class, HasContentBlocksContract::class)) {
            return null;
        }

        $owner = $class::query()->find($id);

        return $owner instanceof HasContentBlocksContract ? $owner : null;
    }

    /**
     * Embedded HasMany forms are served under the owning resource's route,
     * so the CRUD request resolves the parent resource and its item.
     *
     * Both the container and the request are taken per call: the resolver is
     * a singleton, and under Octane an injected container would be the
     * worker's base application rather than the sandbox of the current
     * request. Resolving the CRUD request through it would store the request
     * of one HTTP call in the base application, where every later request of
     * that worker would find it.
     */
    private function fromParentResource(): ?HasContentBlocksContract
    {
        $container = Container::getInstance();

        if (! $container->bound(CrudRequestContract::class)) {
            return null;
        }

        $request = $container->make(CrudRequestContract::class);

        if (! $request->hasResource()) {
            return null;
        }

        $item = $request->getResource()?->getItem();

        return $item instanceof HasContentBlocksContract ? $item : null;
    }
}
