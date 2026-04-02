<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('element_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('element_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->boolean('consumer_can_read_via_api')->default(false);
            $table->timestamps();

            $table->unique(['element_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('element_group');
    }
};
