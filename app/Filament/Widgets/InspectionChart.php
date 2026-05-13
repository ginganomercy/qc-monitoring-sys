<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use App\Helpers\CacheHelper;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class InspectionChart extends ChartWidget
{
    protected static ?string $heading = 'Inspeksi Harian (7 Hari Terakhir)';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true; // ✅ Lazy load for progressive rendering

    protected function getData(): array
    {
        return CacheHelper::getWidgetStat('chart_daily_7d', function () {
            // Optimized: Single SQL aggregation query instead of 21 queries in a loop
            $startDate = Carbon::today()->subDays(6);
            
            $inspections = Inspection::selectRaw("
                    inspection_date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) as passed,
                    SUM(CASE WHEN status = 'reject' THEN 1 ELSE 0 END) as rejected
                ")
                ->whereDate('inspection_date', '>=', $startDate)
                ->groupBy('inspection_date')
                ->orderBy('inspection_date')
                ->get()
                ->keyBy(fn($row) => Carbon::parse($row->inspection_date)->format('Y-m-d'));

            $data = ['total' => [], 'passed' => [], 'rejected' => []];
            $labels = [];

            // Fill missing dates with 0
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateString = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                $row = $inspections->get($dateString);

                $data['total'][] = $row ? $row->total : 0;
                $data['passed'][] = $row ? $row->passed : 0;
                $data['rejected'][] = $row ? $row->rejected : 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Total Inspeksi',
                        'data' => $data['total'],
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    ],
                    [
                        'label' => 'Lolos',
                        'data' => $data['passed'],
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    ],
                    [
                        'label' => 'Ditolak',
                        'data' => $data['rejected'],
                        'borderColor' => 'rgb(239, 68, 68)',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
