<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Cache resource count for navigation badges
     * TTL: 5 minutes (300 seconds)
     *
     * @param  string  $model Model class name
     * @param  string  $key Cache key
     * @return int Count of records
     */
    public static function getResourceCount(string $model, string $key): int
    {
        return Cache::remember($key, 300, function () use ($model) {
            return $model::count();
        });
    }

    /**
     * Cache widget statistics
     * TTL: 5 minutes (300 seconds)
     *
     * @param  string  $key Cache key
     * @param  callable  $callback Callback function to execute
     * @return mixed Result from callback
     */
    public static function getWidgetStat(string $key, callable $callback): mixed
    {
        return Cache::remember($key, 300, $callback);
    }

    /**
     * Clear all QC monitoring caches
     * Useful after data updates
     */
    public static function clearQcCaches(): void
    {
        $keys = [
            'products_count',
            'lines_count',
            'defect_types_count',
            'components_count',
            'daily_targets_count',
            'inspections_count',
            'widget_inspections_today',
            'widget_inspections_passed',
            'widget_inspections_rejected',
            'widget_pass_rate',
            // Added widget keys from Sprint 2
            'dashboard_stats',
            'chart_daily_7d',
            'chart_top_defects_7d',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cache key for date range
     *
     * @param  string  $prefix Key prefix
     * @param  string  $startDate Start date
     * @param  string  $endDate End date
     * @return string Cache key
     */
    public static function getDateRangeKey(string $prefix, string $startDate, string $endDate): string
    {
        return "{$prefix}_{$startDate}_{$endDate}";
    }
}
