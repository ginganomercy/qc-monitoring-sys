<?php

namespace App\Filament\Widgets;

use App\Services\QueryOptimizerService;
use Filament\Widgets\ChartWidget;

class InspectionChart extends ChartWidget
{
    protected static ?string $heading = 'Inspeksi Harian (7 Hari Terakhir)';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected QueryOptimizerService $queryOptimizer;

    public function boot(): void
    {
        $this->queryOptimizer = new QueryOptimizerService();
    }

    protected function getData(): array
    {
        $this->queryOptimizer = new QueryOptimizerService();

        // Single call to get all chart data (1 query instead of 1)
        $chartData = $this->queryOptimizer->get7DayChartData();

        return [
            'datasets' => [
                [
                    'label' => 'Total Inspeksi',
                    'data' => $chartData['total'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Lolos',
                    'data' => $chartData['passed'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Ditolak',
                    'data' => $chartData['rejected'],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $chartData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
