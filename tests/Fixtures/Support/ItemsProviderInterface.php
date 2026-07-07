<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Support;

interface ItemsProviderInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function items(int $limit): array;
}
