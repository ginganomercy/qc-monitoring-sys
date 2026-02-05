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
