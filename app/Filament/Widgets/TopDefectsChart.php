<?php

namespace App\Filament\Widgets;

use App\Services\QueryOptimizerService;
use Filament\Widgets\ChartWidget;

class TopDefectsChart extends ChartWidget
{
    protected static ?string $heading = '5 Defect Teratas (7 Hari Terakhir)';

    protected static ?int $sort = 3;

    protected static bool $isLazy = true;

    protected QueryOptimizerService $queryOptimizer;

    public function boot(): void
    {
        $this->queryOptimizer = new QueryOptimizerService();
    }

    protected function getData(): array
    {
        $this->queryOptimizer = new QueryOptimizerService();

        // Single call with caching (1 query instead of 1)
        $defects = $this->queryOptimizer->getTopDefects(7, 5);

        $labels = $defects->pluck('name')->toArray();
        $data = $defects->pluck('count')->toArray();
        $colors = $defects->pluck('color')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Defect',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}