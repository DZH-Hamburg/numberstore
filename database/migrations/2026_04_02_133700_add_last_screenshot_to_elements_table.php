<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->string('last_screenshot_disk', 32)->nullable()->after('encrypted_credentials');
            $table->string('last_screenshot_path', 2048)->nullable()->after('last_screenshot_disk');
            $table->timestamp('last_screenshot_at')->nullable()->after('last_screenshot_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn(['last_screenshot_disk', 'last_screenshot_path', 'last_screenshot_at']);
        });
    }
};
