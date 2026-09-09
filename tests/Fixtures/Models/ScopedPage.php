<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Models;

use Ewk\ContentBlocks\Contracts\ScopedBlockOwnerContract;
use Ewk\ContentBlocks\Models\Concerns\HasContentBlocks;
use Illuminate\Database\Eloquent\Model;

/**
 * Owner whose `scope` column narrows the blocks it accepts (a page type
 * distinguishing plain pages from entity templates).
 */
final class ScopedPage extends Model implements ScopedBlockOwnerContract
{
    use HasContentBlocks;

    protected $table = 'pages';

    protected $fillable = ['title', 'scope'];

    public function blockScope(): ?string
    {
        $scope = $this->getAttribute('scope');

        return \is_string($scope) ? $scope : null;
    }
}
