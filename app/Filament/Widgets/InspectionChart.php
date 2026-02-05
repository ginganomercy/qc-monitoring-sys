<?php

namespace App\Filament\Widgets;

use App\Models\Inspection;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class InspectionChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Inspections (Last 7 Days)';

    protected static ?int $sort = 2;

    protected static bool $isLazy = true; // ✅ Lazy load for progressive rendering

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');

            $total = Inspection::whereDate('inspection_date', $date)->count();
            $passed = Inspection::whereDate('inspection_date', $date)
                ->where('status', 'pass')
                ->count();
            $rejected = Inspection::whereDate('inspection_date', $date)
                ->where('status', 'reject')
                ->count();

            $data['total'][] = $total;
            $data['passed'][] = $passed;
            $data['rejected'][] = $rejected;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Inspections',
                    'data' => $data['total'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Passed',
                    'data' => $data['passed'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Rejected',
                    'data' => $data['rejected'],
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
