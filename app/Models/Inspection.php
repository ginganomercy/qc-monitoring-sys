<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
        'user_id',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inspection_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model with business rule enforcement.
     *
     * Rules enforced at model level (defense-in-depth):
     * 1. status = 'reject' → defect_type_id MUST be filled
     * 2. status = 'pass'   → auto-clear defect fields (nullify)
     * 3. inspection_date   → cannot be in the future
     * 4. user_id           → automatically set to current user (admin-only)
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
            $inspectionDate = $inspection->inspection_date;
            if ($inspectionDate && Carbon::parse($inspectionDate)->startOfDay()->greaterThan(Carbon::today())) {
                throw new \InvalidArgumentException(
                    'Tanggal inspeksi tidak boleh di masa depan.'
                );
            }

            // Rule 4: Auto-set user_id to current admin (admin-only system)
            if (empty($inspection->user_id) && auth()->check()) {
                $inspection->user_id = auth()->id();
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
     * Get the user (admin) who created this inspection.
     * Note: Renamed from 'inspector' to 'user' for admin-only system.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who approved this inspection.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    /**
     * Scope for approved inspections.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }

    /**
     * Scope for pending (not yet approved) inspections.
     */
    public function scopePending($query)
    {
        return $query->whereNull('approved_by');
    }
}
