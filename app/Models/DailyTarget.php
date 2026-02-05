<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTarget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'line_id',
        'target_date',
        'target_quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_date' => 'date',
        'target_quantity' => 'integer',
    ];

    /**
     * Get the line for this daily target.
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Scope targets for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('target_date', today());
    }

    /**
     * Scope targets for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('target_date', $date);
    }
}
