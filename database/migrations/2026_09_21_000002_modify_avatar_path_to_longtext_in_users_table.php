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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_path')) {
                // SQLite & MySQL compatible column modification/fallback
                try {
                    $table->longText('avatar_path')->nullable()->change();
                } catch (\Throwable $e) {
                    // Ignore if change method without doctrine/dbal, SQLite stores any length string regardless
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
