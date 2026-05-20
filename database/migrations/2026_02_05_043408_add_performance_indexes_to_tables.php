<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Performance optimization: Add indexes ONLY if they don't exist
     * Expected improvement: 30-50% faster queries on widgets and searches
     */
    public function up(): void
    {
        // Helper to check if index exists
        $indexExists = function (string $table, string $index): bool {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return count($indexes) > 0;
        };

        // ========================================
        // INSPECTIONS TABLE - Most Critical
        // ========================================
        Schema::table('inspections', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('inspections', 'idx_inspection_date')) {
                $table->index('inspection_date', 'idx_inspection_date');
            }

            if (! $indexExists('inspections', 'idx_status_date')) {
                $table->index(['status', 'inspection_date'], 'idx_status_date');
            }

            if (! $indexExists('inspections', 'idx_created_at')) {
                $table->index('created_at', 'idx_created_at');
            }
        });

        // ========================================
        // DEFECT_TYPES TABLE
        // ========================================
        Schema::table('defect_types', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('defect_types', 'idx_severity')) {
                $table->index('severity', 'idx_severity');
            }

            if (! $indexExists('defect_types', 'idx_defect_name')) {
                $table->index('name', 'idx_defect_name');
            }
        });

        // Note: Other tables (products, lines, daily_targets) already have necessary indexes
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex('idx_inspection_date');
            $table->dropIndex('idx_status_date');
            $table->dropIndex('idx_created_at');
        });

        Schema::table('defect_types', function (Blueprint $table) {
            $table->dropIndex('idx_severity');
            $table->dropIndex('idx_defect_name');
        });
    }
};
