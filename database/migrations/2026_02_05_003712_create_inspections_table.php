<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->date('inspection_date')->index();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('line_id')->constrained('lines')->onDelete('restrict');
            $table->enum('status', ['pass', 'reject'])->index();
            $table->foreignId('defect_type_id')->nullable()->constrained('defect_types')->onDelete('set null');
            $table->foreignId('component_id')->nullable()->constrained('components')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('inspector_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Composite indexes for performance
            $table->index(['inspection_date', 'status'], 'inspections_date_status_idx');
            $table->index(['line_id', 'inspection_date'], 'inspections_line_date_idx');
            $table->index('created_at', 'inspections_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
