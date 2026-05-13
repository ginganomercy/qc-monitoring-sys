<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inspection_date',
        'product_id',
        'line_id',
        'status',
        'defect_type_id',
        'component_id',
        'notes',
        'inspector_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inspection_date' => 'date',
    ];

    /**
     * Boot the model with business rule enforcement.
     *
     * Rules enforced at model level (defense-in-depth):
     * 1. status = 'reject' → defect_type_id MUST be filled
     * 2. status = 'pass'   → auto-clear defect fields (nullify)
     * 3. inspection_date   → cannot be in the future
     */
    protected static function booted(): void
    {
        static::saving(function (Inspection $inspection) {
            // Rule 1: Reject MUST have a defect type
            if ($inspection->status === 'reject' && empty($inspection->defect_type_id)) {
                throw new \InvalidArgumentException(
                    'Jenis defect wajib diisi untuk inspeksi dengan status reject.'
                );
            }

            // Rule 2: Pass → auto-clear defect data for consistency
            if ($inspection->status === 'pass') {
                $inspection->defect_type_id = null;
                $inspection->component_id = null;
                $inspection->notes = null;
            }

            // Rule 3: Cannot inspect in the future
            if ($inspection->inspection_date && $inspection->inspection_date->isFuture()) {
                throw new \InvalidArgumentException(
                    'Tanggal inspeksi tidak boleh di masa depan.'
                );
            }
        });
    }

    /**
     * Get the product for this inspection.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the line for this inspection.
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Get the defect type for this inspection.
     */
    public function defectType(): BelongsTo
    {
        return $this->belongsTo(DefectType::class);
    }

    /**
     * Get the component for this inspection.
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * Get the inspector (user) for this inspection.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Scope inspections with status 'pass'.
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'pass');
    }

    /**
     * Scope inspections with status 'reject'.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'reject');
    }

    /**
     * Scope inspections for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('inspection_date', today());
    }

    /**
     * Scope inspections for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('inspection_date', $date);
    }

    /**
     * Scope inspections for a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('inspection_date', [$startDate, $endDate]);
    }
}
