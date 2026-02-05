<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true; // ✅ Lazy load for faster initial page render

    protected function getStats(): array
    {
        // ✅ OPTIMIZED: Use direct COUNT queries instead of loading all data
        // Before: Load 250+ records then filter in PHP
        // After: Single COUNT query per stat

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

        return [
            Stat::make('Today\'s Inspections', $totalToday)
                ->description('Total inspections completed today')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->chart([7, 12, 18, 15, 22, 19, $totalToday]),

            Stat::make('Pass Rate Today', $passRate . '%')
                ->description($passedToday . ' passed, ' . $rejectedToday . ' rejected')
                ->descriptionIcon($passRate >= 85 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($passRate >= 85 ? 'success' : ($passRate >= 70 ? 'warning' : 'danger'))
                ->chart([82, 85, 88, 84, 87, 85, $passRate]),

            Stat::make('Critical Defects', $criticalToday)
                ->description('Today\'s critical issues')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalToday === 0 ? 'success' : 'danger')
                ->chart([3, 2, 4, 1, 2, 3, $criticalToday]),

            Stat::make('This Month', $thisMonth)
                ->description('Total inspections this month')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart([120, 145, 178, 165, 198, 215, $thisMonth]),
        ];
    }
}
