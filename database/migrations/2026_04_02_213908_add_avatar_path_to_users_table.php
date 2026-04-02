<?php

return new class extends \Illuminate\Database\Migrations\Migration { // pragma: allowlist secret
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->string('avatar_path')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->dropColumn('avatar_path');
        });
    }
};
