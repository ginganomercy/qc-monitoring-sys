<?php

namespace App\Filament\Widgets;

use App\Models\DefectType;
use App\Models\Inspection;
use Filament\Widgets\ChartWidget;

class TopDefectsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Defects (Last 7 Days)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get top 5 defect types from last 7 days
        $defects = Inspection::where('status', 'reject')
            ->whereDate('inspection_date', '>=', now()->subDays(7))
            ->with('defectType')
            ->get()
            ->groupBy('defect_type_id')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5);

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($defects as $defectTypeId => $count) {
            if ($defectTypeId) {
                $defectType = DefectType::find($defectTypeId);
                $labels[] = $defectType->name ?? 'Unknown';
                $data[] = $count;

                // Color based on severity
                $colors[] = match ($defectType->severity ?? 'medium') {
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
                    'label' => 'Defect Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
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
