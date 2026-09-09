# ewk/laravel-content-blocks

Polymorphic content blocks for Laravel 12/13 with a MoonShine 4 admin: attach ordered, toggleable, JSON-backed blocks to any Eloquent model and render them as a structured API payload.

- **Any owner** — blocks attach through a `blockable` morph relation: pages, products, categories, anything.
- **Two kinds of blocks** — *static* (payload comes from the stored JSON content) and *dynamic* (payload is computed at render time through constructor-injected dependencies: product lists, collections, feeds).
- **Admin included** — a MoonShine resource with per-type form fields, drag & drop reordering and inline activity toggling (via [ewk/moonshine-resource-kit](https://github.com/einzelwerk/moonshine-resource-kit)), plus an embeddable `ContentBlocksField` for any resource form.
- **Container-first** — blocks are resolved through the service container; no facades, no global state in the package internals.

## Installation

```bash
composer require ewk/laravel-content-blocks
php artisan migrate
```

Publish the config when you need to customise anything:

```bash
php artisan vendor:publish --tag=content-blocks-config
```

## Quick start

**1. Create a block:**

```bash
php artisan make:content-block Hero            # static block
php artisan make:content-block ProductList --dynamic
```

```php
final class HeroBlock extends AbstractBlock
{
    public static function code(): string
    {
        return 'hero';
    }

    public function title(): string
    {
        return __('blocks.hero');
    }

    public function fields(): array
    {
        return [
            Text::make('Heading', 'heading')->required(),
            Select::make('Media type', 'media_type')
                ->options(['video' => 'Video', 'photo' => 'Photo'])
                ->native(),
            File::make('Video', 'video')
                // intra-block conditions reference the built form column:
                ->showWhen($this->contentColumn('media_type'), 'video'),
        ];
    }

    public function rules(): array
    {
        // keys are relative to the block content — the package prefixes them
        return ['heading' => ['required', 'string', 'max:255']];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        $content = $this->content($block);

        return ['heading' => $content['heading'] ?? null];
    }
}
```

**2. Register it** in `config/content-blocks.php`:

```php
'blocks' => [
    App\Blocks\HeroBlock::class,
],
```

**3. Attach blocks to a model:**

```php
use Ewk\ContentBlocks\Contracts\HasContentBlocksContract;
use Ewk\ContentBlocks\Models\Concerns\HasContentBlocks;

class Page extends Model implements HasContentBlocksContract
{
    use HasContentBlocks;
}
```

**4. Manage blocks in MoonShine** — drop the field into the owning resource's form:

```php
use Ewk\ContentBlocks\MoonShine\Fields\ContentBlocksField;

ContentBlocksField::make('Blocks'),
```

The field renders the owner's blocks as a table with a drag handle for reordering and an inline activity switcher, and creates blocks through the packaged resource. It returns a regular `HasMany`, so all its options remain available.

**5. Render the blocks:**

```php
use Ewk\ContentBlocks\Rendering\BlockRenderer;

final class GetPageBlocksController
{
    public function __construct(private readonly BlockRenderer $renderer) {}

    public function __invoke(Request $request, Page $page): JsonResponse
    {
        return new JsonResponse(['data' => $this->renderer->render($page, $request)]);
    }
}
```

Each rendered block is an envelope the frontend can dispatch on:

```json
{ "id": 1, "code": "hero", "name": "Main hero", "data": { "heading": "Welcome" } }
```

Inactive blocks are skipped, order follows the drag & drop position, and rows whose type is no longer registered are skipped with an `UnknownBlockTypeEncountered` event.

## Nested content keys

Field columns may address nested keys of the content through dot notation — handy for per-locale editors:

```php
public function fields(): array
{
    return [
        TinyMce::make('Body (EN)', 'body.en'),
        TinyMce::make('Body (DE)', 'body.de'),
    ];
}

public function rules(): array
{
    return ['body' => ['nullable', 'array'], 'body.*' => ['nullable', 'string']];
}
```

The stored content is `{"body": {"en": "...", "de": "..."}}`; the root key (`body`) is what the block owns when the submitted payload is filtered.

## Dynamic blocks

A dynamic block computes its payload at render time — the stored content holds only settings:

```php
final class ProductListBlock extends AbstractBlock
{
    public function __construct(private readonly ListProductsQuery $products) {}

    public function fields(): array
    {
        return [Number::make('Limit', 'limit')->default(10)];
    }

    public function data(ContentBlock $block, Request $request): array
    {
        $limit = (int) ($this->content($block)['limit'] ?? 10);

        return ['items' => $this->products->list(limit: $limit)];
    }
}
```

This is the building brick for block-driven APIs: the endpoint stays `renderer->render($model, $request)` while editors compose what the page returns.

## Block categories and scopes

Two optional hooks on a block shape the admin type selector:

```php
final class TourScheduleBlock extends AbstractBlock
{
    public function category(): ?string
    {
        return 'Tour';          // <optgroup> label in the type selector; null = ungrouped
    }

    public function scopes(): array
    {
        return ['tour'];        // owners the block is offered to; [] = every owner
    }
}
```

An owner narrows the blocks it accepts by implementing `ScopedBlockOwnerContract` — a page model whose `type` column separates plain pages from entity templates, for example:

```php
class Page extends Model implements ScopedBlockOwnerContract
{
    use HasContentBlocks;

    public function blockScope(): ?string
    {
        return $this->type;     // 'page' | 'tour' | ...
    }
}
```

The block form lists only the blocks available to the resolved owner (unscoped blocks plus the ones listing the owner's scope), validates `type` against the same set, and builds just their fields. Owners without the contract get the unscoped blocks; when the owner cannot be determined every registered block is offered. `BlockRegistryInterface::availableFor()`, `options()` and `optionsFor()` expose the same logic for custom resources.

## Extension points

| What | How |
| --- | --- |
| Custom model (table name, media, behaviour) | Extend `ContentBlock`, point `content-blocks.model` at the subclass |
| Replace a packaged/app block | `BlockRegistryInterface::override()` (accidental code collisions throw) |
| Restrict blocks per owner | `ScopedBlockOwnerContract` on the owner + `BlockContract::scopes()` on the block |
| Custom admin form mapping | Rebind `BlockFieldsBuilderInterface` |
| Your own admin resource | Set `content-blocks.moonshine.resource => false`, build on `BlockFieldsBuilderInterface` |
| Blockable owner selector on the block form | `content-blocks.moonshine.blockable_types` (rendered as a `MorphTo` field) |
| React to orphaned block rows | Listen to `UnknownBlockTypeEncountered` |

## Roadmap

- Rich block editor UI (custom blade/JS component) on top of the current table-based manager.
- JSON Schema export per block for typed frontends / OpenAPI generation.

## Development

```bash
composer test          # arch + unit + feature
composer phpstan       # level max, src/ only
composer format        # PER 3.0 via PHP-CS-Fixer
```

## License

MIT
