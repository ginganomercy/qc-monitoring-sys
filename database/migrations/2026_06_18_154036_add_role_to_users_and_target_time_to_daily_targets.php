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
            $table->string('role', 20)->default('admin_qc')->after('password');
        });

        Schema::table('daily_targets', function (Blueprint $table) {
            $table->time('target_time')->nullable()->after('target_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropColumn('target_time');
        });
    }
};
