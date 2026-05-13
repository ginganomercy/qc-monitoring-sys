<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use App\Helpers\CacheHelper;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true; // ✅ Lazy load for faster initial page render

    protected function getStats(): array
    {
        return CacheHelper::getWidgetStat('dashboard_stats', function () {
            $totalToday = Inspection::whereDate('inspection_date', today())->count();
            $passedToday = Inspection::whereDate('inspection_date', today())
                ->where('status', 'pass')
                ->count();
            $rejectedToday = $totalToday - $passedToday; // Calculate instead of querying
            $passRate = $totalToday > 0 ? round(($passedToday / $totalToday) * 100, 1) : 0;

            // Get critical defects today
            $criticalToday = Inspection::whereDate('inspection_date', today())
                ->where('status', 'reject')
                ->whereHas('defectType', function ($query) {
                    $query->where('severity', 'critical');
                })
                ->count();

            // Get total inspections this month
            $thisMonth = Inspection::whereMonth('inspection_date', now()->month)
                ->whereYear('inspection_date', now()->year)
                ->count();

            // 7 Days actual data for sparklines (no more fake data)
            $last7Days = Inspection::selectRaw("inspection_date, COUNT(*) as count")
                ->whereDate('inspection_date', '>=', now()->subDays(6))
                ->groupBy('inspection_date')
                ->orderBy('inspection_date')
                ->get()
                ->keyBy(fn($row) => Carbon::parse($row->inspection_date)->format('Y-m-d'));

            $last7DaysPass = Inspection::selectRaw("inspection_date, COUNT(*) as count")
                ->where('status', 'pass')
                ->whereDate('inspection_date', '>=', now()->subDays(6))
                ->groupBy('inspection_date')
                ->orderBy('inspection_date')
                ->get()
                ->keyBy(fn($row) => Carbon::parse($row->inspection_date)->format('Y-m-d'));

            $last7DaysCritical = Inspection::selectRaw("inspection_date, COUNT(*) as count")
                ->where('status', 'reject')
                ->whereHas('defectType', function ($query) {
                    $query->where('severity', 'critical');
                })
                ->whereDate('inspection_date', '>=', now()->subDays(6))
                ->groupBy('inspection_date')
                ->orderBy('inspection_date')
                ->get()
                ->keyBy(fn($row) => Carbon::parse($row->inspection_date)->format('Y-m-d'));
            
            $chartTotal = [];
            $chartPassRate = [];
            $chartCritical = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $dateString = Carbon::today()->subDays($i)->format('Y-m-d');
                $dailyTotal = isset($last7Days[$dateString]) ? $last7Days[$dateString]->count : 0;
                $dailyPass = isset($last7DaysPass[$dateString]) ? $last7DaysPass[$dateString]->count : 0;
                $dailyCritical = isset($last7DaysCritical[$dateString]) ? $last7DaysCritical[$dateString]->count : 0;
                
                $dailyPassRate = $dailyTotal > 0 ? round(($dailyPass / $dailyTotal) * 100, 1) : 0;

                $chartTotal[] = $dailyTotal;
                $chartPassRate[] = $dailyPassRate;
                $chartCritical[] = $dailyCritical;
            }

            return [
                Stat::make('Inspeksi Hari Ini', $totalToday)
                    ->description('Total inspeksi hari ini')
                    ->descriptionIcon('heroicon-m-clipboard-document-check')
                    ->color('primary')
                    ->chart($chartTotal),

                Stat::make('Tingkat Kelulusan', $passRate . '%')
                    ->description($passedToday . ' lolos, ' . $rejectedToday . ' ditolak')
                    ->descriptionIcon($passRate >= 85 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($passRate >= 85 ? 'success' : ($passRate >= 70 ? 'warning' : 'danger'))
                    ->chart($chartPassRate),

                Stat::make('Defect Kritis', $criticalToday)
                    ->description('Masalah kritis hari ini')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color($criticalToday === 0 ? 'success' : 'danger')
                    ->chart($chartCritical),

                Stat::make('Bulan Ini', $thisMonth)
                    ->description('Total inspeksi bulan ini')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('info')
                    // Not rendering chart for this month as it's not daily basis, keep it simple
            ];
        });
    }
}
