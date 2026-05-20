<?php

namespace App\Filament\Widgets;

use App\Services\QueryOptimizerService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    /**
     * Initialize QueryOptimizerService
     */
    protected QueryOptimizerService $queryOptimizer;

    public function boot(): void
    {
        $this->queryOptimizer = new QueryOptimizerService();
    }

    protected function getStats(): array
    {
        $this->queryOptimizer = new QueryOptimizerService();

        // Use QueryOptimizerService for optimized queries (7 queries → 2 queries)
        $dailyStats = $this->queryOptimizer->getDailyStats();
        $criticalToday = $this->queryOptimizer->getCriticalDefectsToday();
        $chartData = $this->queryOptimizer->get7DayChartData();

        return [
            Stat::make('Inspeksi Hari Ini', $dailyStats['total_today'])
                ->description('Total inspeksi hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->chart($chartData['total']),

            Stat::make('Tingkat Kelulusan', $dailyStats['pass_rate'].'%')
                ->description($dailyStats['passed_today'].' lolos, '.$dailyStats['rejected_today'].' ditolak')
                ->descriptionIcon($dailyStats['pass_rate'] >= 85 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($dailyStats['pass_rate'] >= 85 ? 'success' : ($dailyStats['pass_rate'] >= 70 ? 'warning' : 'danger'))
                ->chart($this->calculatePassRateTrend($chartData)),

            Stat::make('Defect Kritis', $criticalToday)
                ->description('Masalah kritis hari ini')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($criticalToday === 0 ? 'success' : 'danger')
                ->chart($chartData['rejected']),

            Stat::make('Bulan Ini', $dailyStats['total_month'])
                ->description('Total inspeksi bulan ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }

    /**
     * Calculate pass rate trend from chart data.
     */
    protected function calculatePassRateTrend(array $chartData): array
    {
        $trend = [];
        $total = $chartData['total'];
        $passed = $chartData['passed'];

        for ($i = 0; $i < count($total); $i++) {
            if (isset($total[$i]) && $total[$i] > 0) {
                $trend[] = round(($passed[$i] / $total[$i]) * 100, 1);
            } else {
                $trend[] = 0;
            }
        }

        return $trend;
    }
}
