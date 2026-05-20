<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes:
     * 1. Rename inspector_id → user_id (admin-only, simpler)
     * 2. Add approved_by and approved_at for audit trail
     * 3. Add index on user_id for faster queries
     */
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // 1. Rename inspector_id to user_id
            $table->renameColumn('inspector_id', 'user_id');
        });

        Schema::table('inspections', function (Blueprint $table) {
            // 2. Add audit fields using the new column name
            $table->unsignedBigInteger('approved_by')->nullable()->after('user_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // 3. Add indexes
            $table->index('user_id', 'inspections_user_id_idx');
            $table->index('approved_by', 'inspections_approved_by_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Remove audit fields
            $table->dropIndex('inspections_approved_by_idx');
            $table->dropColumn('approved_at');
            $table->dropColumn('approved_by');

            // Rename back to inspector_id
            $table->renameColumn('user_id', 'inspector_id');
            $table->dropIndex('inspections_user_id_idx');
        });
    }
};