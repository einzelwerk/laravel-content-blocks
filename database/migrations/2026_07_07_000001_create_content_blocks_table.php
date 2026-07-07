<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_blocks', static function (Blueprint $table): void {
            $table->id();
            $table->morphs('blockable');
            $table->string('type');
            $table->string('name');
            $table->json('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index(['blockable_type', 'blockable_id', 'is_active', 'sort_order'], 'content_blocks_render_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
