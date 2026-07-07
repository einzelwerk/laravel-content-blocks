<?php

declare(strict_types=1);

namespace Ewk\ContentBlocks\Tests\Fixtures\Models;

use Ewk\ContentBlocks\Contracts\HasContentBlocksContract;
use Ewk\ContentBlocks\Models\Concerns\HasContentBlocks;
use Illuminate\Database\Eloquent\Model;

final class Page extends Model implements HasContentBlocksContract
{
    use HasContentBlocks;

    protected $table = 'pages';

    protected $fillable = ['title'];
}
