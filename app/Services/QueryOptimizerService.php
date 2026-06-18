<?php

namespace App\Services;

use App\Models\Inspection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Query Optimizer Service
 *
 * Centralized location for all optimized dashboard queries.
 * Reduces database load by using SQL aggregation and caching.
 */
class QueryOptimizerService
{
    /**
     * Cache TTL for dashboard queries (5 minutes)
     */
    protected const CACHE_TTL = 300;

    /**
     * Get optimized daily stats (7 queries → 2 queries)
     */
    public function getDailyStats(): array
    {
        return Cache::remember('dashboard_daily_stats', self::CACHE_TTL, function () {
            // Query 1: Today's stats
            $todayStats = Inspection::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as rejected
            ")
                ->whereDate('inspection_date', today())
                ->first();

            // Query 2: Monthly stats
            $monthlyStats = Inspection::selectRaw('COUNT(*) as total')
                ->whereMonth('inspection_date', now()->month)
                ->whereYear('inspection_date', now()->year)
                ->first();

            // Calculate pass rate
            $passRate = $todayStats->total > 0
                ? round(($todayStats->passed / $todayStats->total) * 100, 1)
                : 0;

            return [
                'total_today' => $todayStats->total ?? 0,
                'passed_today' => $todayStats->passed ?? 0,
                'rejected_today' => $todayStats->rejected ?? 0,
                'pass_rate' => $passRate,
                'total_month' => $monthlyStats->total ?? 0,
            ];
        });
    }

    /**
     * Get critical defects count for today
     */
    public function getCriticalDefectsToday(): int
    {
        return Cache::remember('dashboard_critical_today', self::CACHE_TTL, function () {
            // Single query with subquery join for critical defects
            return Inspection::where('status', 'reject')
                ->whereDate('inspection_date', today())
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('defect_types')
                        ->whereColumn('defect_types.id', 'inspections.defect_type_id')
                        ->where('severity', 'critical');
                })
                ->count();
        });
    }

    /**
     * Get 7-day chart data (3 queries → 1 query with UNION or single aggregation)
     */
    public function get7DayChartData(): array
    {
        return Cache::remember('dashboard_7day_chart', self::CACHE_TTL, function () {
            $startDate = Carbon::today()->subDays(6);

            // Single optimized query with conditional aggregation
            $data = Inspection::selectRaw("
                DATE(inspection_date) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as rejected
            ")
                ->whereDate('inspection_date', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(fn ($row) => Carbon::parse($row->date)->format('Y-m-d'));

            // Fill missing dates with zeros
            $chartData = ['labels' => [], 'total' => [], 'passed' => [], 'rejected' => []];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateString = $date->format('Y-m-d');

                $chartData['labels'][] = $date->translatedFormat('l, d');

                if (isset($data[$dateString])) {
                    $chartData['total'][] = $data[$dateString]->total;
                    $chartData['passed'][] = $data[$dateString]->passed;
                    $chartData['rejected'][] = $data[$dateString]->rejected;
                } else {
                    $chartData['total'][] = 0;
                    $chartData['passed'][] = 0;
                    $chartData['rejected'][] = 0;
                }
            }

            return $chartData;
        });
    }

    /**
     * Get top defects for chart (1 query)
     */
    public function getTopDefects(int $days = 7, int $limit = 5): array
    {
        return Cache::remember("dashboard_top_defects_{$days}d", self::CACHE_TTL, function () use ($days, $limit) {
            return Inspection::selectRaw('
                defect_type_id,
                COUNT(*) as count
            ')
                ->where('status', 'reject')
                ->whereDate('inspection_date', '>=', now()->subDays($days))
                ->whereNotNull('defect_type_id')
                ->with(['defectType:id,name,severity'])
                ->groupBy('defect_type_id')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->defectType?->name ?? 'Unknown',
                        'severity' => $item->defectType?->severity ?? 'unknown',
                        'count' => $item->count,
                        'color' => $this->getSeverityColor($item->defectType?->severity),
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    /**
     * Get pass rate trend for sparkline (1 query)
     */
    public function getPassRateTrend(int $days = 7): array
    {
        return Cache::remember("dashboard_pass_rate_trend_{$days}d", self::CACHE_TTL, function () use ($days) {
            $startDate = Carbon::today()->subDays($days - 1);

            $data = Inspection::selectRaw("
                DATE(inspection_date) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) as passed
            ")
                ->whereDate('inspection_date', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(fn ($row) => Carbon::parse($row->date)->format('Y-m-d'));

            $trend = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateString = $date->format('Y-m-d');

                if (isset($data[$dateString]) && $data[$dateString]->total > 0) {
                    $trend[] = round(($data[$dateString]->passed / $data[$dateString]->total) * 100, 1);
                } else {
                    $trend[] = 0;
                }
            }

            return $trend;
        });
    }

    /**
     * Get severity color for chart.
     */
    protected function getSeverityColor(?string $severity): string
    {
        return match ($severity) {
            'critical' => 'rgb(220, 38, 38)',
            'high' => 'rgb(239, 68, 68)',
            'medium' => 'rgb(251, 146, 60)',
            'low' => 'rgb(34, 197, 94)',
            default => 'rgb(156, 163, 175)',
        };
    }

    /**
     * Clear all dashboard caches.
     */
    public function clearCaches(): void
    {
        $keys = [
            'dashboard_daily_stats',
            'dashboard_critical_today',
            'dashboard_7day_chart',
            'dashboard_pass_rate_trend_7d',
        ];

        for ($i = 1; $i <= 30; $i++) {
            $keys[] = "dashboard_top_defects_{$i}d";
            $keys[] = "dashboard_pass_rate_trend_{$i}d";
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
