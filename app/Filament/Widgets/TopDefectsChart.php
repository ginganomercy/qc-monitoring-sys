<?php

namespace App\Filament\Widgets;

use App\Models\DefectType;
use App\Models\Inspection;
use App\Helpers\CacheHelper;
use Filament\Widgets\ChartWidget;

class TopDefectsChart extends ChartWidget
{
    protected static ?string $heading = '5 Defect Teratas (7 Hari Terakhir)';

    protected static ?int $sort = 3;

    protected static bool $isLazy = true; // ✅ Lazy load to avoid blocking page load

    protected function getData(): array
    {
        return CacheHelper::getWidgetStat('chart_top_defects_7d', function () {
            // ✅ OPTIMIZED: Use SQL GROUP BY instead of loading all data and grouping in PHP
            // Before: Load 100+ reject inspections, group in collection
            // After: Single query with GROUP BY

            $defects = Inspection::selectRaw('defect_type_id, COUNT(*) as count')
                ->where('status', 'reject')
                ->whereDate('inspection_date', '>=', now()->subDays(7))
                ->whereNotNull('defect_type_id')
                ->groupBy('defect_type_id')
                ->orderByDesc('count')
                ->limit(5)
                ->with('defectType')  // Eager load defect type info
                ->get();

            $labels = [];
            $data = [];
            $colors = [];

            foreach ($defects as $defect) {
                $defectType = $defect->defectType;
                if ($defectType) {
                    $labels[] = $defectType->name;
                    $data[] = $defect->count;

                    // Color based on severity
                    $colors[] = match ($defectType->severity) {
                        'critical' => 'rgb(220, 38, 38)',
                        'high' => 'rgb(239, 68, 68)',
                        'medium' => 'rgb(251, 146, 60)',
                        'low' => 'rgb(34, 197, 94)',
                        default => 'rgb(156, 163, 175)',
                    };
                }
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Defect',
                        'data' => $data,
                        'backgroundColor' => $colors,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
