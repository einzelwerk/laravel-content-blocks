<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Support;

final class FakeItemsProvider implements ItemsProviderInterface
{
    public function items(int $limit): array
    {
        $items = [];

        for ($i = 1; $i <= $limit; $i++) {
            $items[] = ['id' => $i, 'title' => 'Item ' . $i];
        }

        return $items;
    }
}
